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
            'productId' => [
                'title' => 'Product ID',
                'type' => 'string',
                'required' => true
            ]
        ];
    }

    public function onRun()
    {
        $productId = $this->property('productId');
        $this->product = Product::find($productId);

        $this->settings = Settings::instance();

        if (!$this->product || !$this->product->isbookable) {
            return;
        }

        // Prepare available dates & times based on working_schedule and booking_interval
        $this->prepareAvailableSlots();
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
    $productId = $this->property('productId');

    // You'd want to validate $day and $time are valid, and then
    // create your booking record or whatever fits your logic

    // Just a dummy success flash message for now
    \Flash::success("Booked product #{$productId} for {$day} at {$time}!");

    // Optionally redirect or do other logic here
}

}
