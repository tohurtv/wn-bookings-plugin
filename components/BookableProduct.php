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
        // Example: Build a simple list of available days from working_schedule
        $schedule = $this->settings->working_schedule ?: [];
        $interval = (int)$this->settings->booking_interval ?: 15;

        $this->availableDates = []; // e.g. ['monday', 'tuesday', ...]

        foreach ($schedule as $daySchedule) {
            if (!empty($daySchedule['day'])) {
                $this->availableDates[] = strtolower($daySchedule['day']);
            }
        }

        // For demo: just grab first day's time blocks, build time slots based on interval
        if (!empty($schedule)) {
            $timeBlocks = $schedule[0]['time_blocks'] ?? [];

            $times = [];
            foreach ($timeBlocks as $block) {
                $from = strtotime($block['from']);
                $to = strtotime($block['to']);

                for ($time = $from; $time + $interval*60 <= $to; $time += $interval*60) {
                    $times[] = date('H:i', $time);
                }
            }

            $this->availableTimes = $times;
        }
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
