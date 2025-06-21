<?php namespace Tohur\Bookings\Components;

use Cms\Classes\ComponentBase;
use Winter\Storm\Support\Collection;
use Tohur\Bookings\Models\Settings;
use Tohur\Bookings\Models\Booking;
use Carbon\Carbon;
use Carbon\CarbonInterval;
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

    // Get all future bookings
    $existingBookings = Booking::where('date', '>=', now())->pluck('date')->map(function ($dt) {
        return Carbon::parse($dt);
    });

    foreach ($schedule as $daySchedule) {
        if (empty($daySchedule['day'])) {
            continue;
        }

        $day = strtolower($daySchedule['day']);
        $this->availableDates[] = $day;

        $timeBlocks = $daySchedule['time_blocks'] ?? [];

        foreach ($timeBlocks as $block) {
            $from = Carbon::createFromFormat('H:i', $block['from']);
            $to = Carbon::createFromFormat('H:i', $block['to']);

            for ($time = $from->copy(); $time->lte($to->copy()->subMinutes($interval)); $time->addMinutes($interval)) {

                // Loop next 30 days to collect available slots by date + time
                for ($i = 0; $i < 30; $i++) {
                    $dayDate = Carbon::now()->addDays($i);

                    if (strtolower($dayDate->format('l')) !== $day) {
                        continue;
                    }

                    $slotDateTime = $dayDate->copy()->setTimeFrom($time);

                    // Check if slot is taken
                    if ($existingBookings->contains(function ($booking) use ($slotDateTime, $interval) {
                        return $booking->format('Y-m-d H:i') === $slotDateTime->format('Y-m-d H:i');
                    })) {
                        continue;
                    }

                    // Format for frontend (optional: use 12-hour time only)
                    $formatted = $slotDateTime->format('Y-m-d g:i A');
                    if (!in_array($formatted, $allTimes)) {
                        $allTimes[] = $formatted;
                    }
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
