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
    $this->prepareAvailableSlots($this->product);

    // Pass data to page
    $this->page['product'] = $this->product;
    $this->page['availableDates'] = $this->availableDates;
    $this->page['availableTimes'] = $this->availableTimes;
    $this->page['settings'] = $this->settings;
}

protected function prepareAvailableSlots(Product $product)
{
    $schedule = $this->settings->working_schedule ?: [];
    $sessionLength = (int) $product->booking_session_length ?: 30;

    $this->availableDates = [];
    $allTimes = [];

    // Get confirmed future bookings with session length
    $existingBookings = Booking::where('date', '>=', now())
        ->where('status_id', 2)
        ->get()
        ->map(function ($booking) {
            $start = Carbon::parse($booking->date);
            $end = $start->copy()->addMinutes($booking->session_length ?? 30);
            return [
                'start' => $start,
                'end'   => $end
            ];
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

            for ($time = $from->copy(); $time->lte($to->copy()->subMinutes($sessionLength)); $time->addMinutes($sessionLength)) {
                for ($i = 0; $i < 30; $i++) {
                    $dayDate = Carbon::now()->addDays($i);

                    if (strtolower($dayDate->format('l')) !== $day) {
                        continue;
                    }

                    $slotStart = $dayDate->copy()->setTimeFrom($time);
                    $slotEnd = $slotStart->copy()->addMinutes($sessionLength);

                    // Check for overlap with any existing approved booking
                    $overlaps = $existingBookings->contains(function ($booking) use ($slotStart, $slotEnd) {
                        return $slotStart->lt($booking['end']) && $slotEnd->gt($booking['start']);
                    });

                    if ($overlaps) {
                        continue;
                    }

                    $formatted = $slotStart->format('Y-m-d g:i A');
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
