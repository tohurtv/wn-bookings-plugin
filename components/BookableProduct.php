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

        $buffer = $this->settings->booking_interval ?? 15;

    $this->page['existingBookings'] = Booking::where('status_id', 2)
        ->where('date', '>=', now()) // This is fine to keep for performance
        ->get()
        ->map(function ($booking) use ($buffer) {
            $start = Carbon::parse($booking->date);
            $length = $booking->session_length ?? 30;
            $end = $start->copy()->addMinutes($length + $buffer);

            return [
                'start' => $start->format('Y-m-d H:i:s'),
                'end'   => $end->format('Y-m-d H:i:s'),
            ];
        });
}

protected function prepareAvailableSlots(Product $product)
{
    $schedule = $this->settings->working_schedule ?: [];
    $sessionLength = (int) $product->booking_session_length ?: 30;
    $bookingInterval = (int) $this->settings->booking_interval ?: 15;
    $slotSpacing = $sessionLength + $bookingInterval;

    $this->availableDates = [];
    $allTimes = [];

    // Pull *all* approved future bookings
    $existingBookings = Booking::where('status_id', 2)->get()->map(function ($booking) use ($bookingInterval) {
        $start = Carbon::parse($booking->date);
        $end = $start->copy()->addMinutes(($booking->session_length ?? 30) + $bookingInterval);
        return [
            'start' => $start,
            'end'   => $end,
        ];
    });

    // Check next 30 days
    for ($i = 0; $i < 30; $i++) {
        $dayDate = Carbon::now()->addDays($i);
        $dayName = strtolower($dayDate->format('l'));

        $daySchedule = collect($schedule)->firstWhere('day', ucfirst($dayName));
        if (!$daySchedule) {
            continue;
        }

        $this->availableDates[] = $dayName;

        foreach ($daySchedule['time_blocks'] ?? [] as $block) {
            $from = Carbon::createFromFormat('H:i', $block['from']);
            $to = Carbon::createFromFormat('H:i', $block['to']);

            // Build slots across the block using spacing logic
            for (
                $time = $from->copy();
                $time->lte($to->copy()->subMinutes($sessionLength));
                $time->addMinutes($slotSpacing)
            ) {
                $slotStart = $dayDate->copy()->setTimeFrom($time);
                $slotEnd = $slotStart->copy()->addMinutes($sessionLength);

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
