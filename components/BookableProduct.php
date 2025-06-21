<?php namespace Tohur\Bookings\Components;

use Cms\Classes\ComponentBase;
use Winter\Storm\Support\Collection;
use Tohur\Bookings\Models\Settings;
use Offline\Mall\Models\Product;

class BookableProduct extends ComponentBase
{
    public $product;
    public $settings;
    public $availableDates = [];
    public $availableTimes = [];

    public function componentDetails()
    {
        return [
            'name' => 'Bookable Product',
            'description' => 'Shows booking options for bookable products'
        ];
    }

public function defineProperties()
{
    return [
        'slug' => [
            'title' => 'Product Slug',
            'type' => 'string',
            'default' => '{{ :slug }}',
        ]
    ];
}

    public function onRun()
{
    $slug = $this->property('slug');
    $this->product = Product::where('slug', $slug)->first();

    if (!$this->product || !$this->product->isbookable) {
        return;
    }

    $this->settings = Settings::instance();
    $this->prepareAvailableSlots();

    // Pass data to page
    $this->page['product'] = $this->product;
    $this->page['availableDates'] = $this->availableDates;
    $this->page['availableTimes'] = $this->availableTimes;
    $this->page['settings'] = $this->settings;
}

protected function prepareAvailableSlots()
{
    $schedule = $this->settings->working_schedule ?: [];
    $interval = (int) $this->settings->booking_interval ?: 15;

    $this->availableDates = []; // e.g. ['monday', 'tuesday', ...]
    $allTimes = [];

    foreach ($schedule as $daySchedule) {
        if (empty($daySchedule['day'])) {
            continue;
        }

        $day = strtolower($daySchedule['day']);
        $this->availableDates[] = $day;

        $timeBlocks = $daySchedule['time_blocks'] ?? [];

        foreach ($timeBlocks as $block) {
            $from = strtotime($block['from']);
            $to = strtotime($block['to']);

            for ($time = $from; $time + $interval * 60 <= $to; $time += $interval * 60) {
                $formatted = date('g:i A', $time); // 12-hour format with am/pm
                if (!in_array($formatted, $allTimes)) {
                    $allTimes[] = $formatted;
                }
            }
        }
    }

    $this->availableTimes = $allTimes;
}
public function onBookProduct()
{
    $day = post('booking_date');
    $time = post('booking_time');

    if (!$this->product) {
        \Flash::error("No bookable product found.");
        return;
    }

    // You'd validate day/time here

    \Flash::success("Booked {$this->product->name} for {$day} at {$time}!");
}

}
