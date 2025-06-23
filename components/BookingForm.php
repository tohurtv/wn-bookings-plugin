<?php namespace Tohur\Bookings\Components;

use Cms\Classes\ComponentBase;
use Tohur\Bookings\Models\Settings;
use Tohur\Bookings\Models\Booking;
use Carbon\Carbon;

class BookingForm extends ComponentBase
{
    public $settings;
    public $availableDates = [];
    public $availableTimes = [];

    public function componentDetails()
    {
        return [
            'name'        => 'Booking Form',
            'description' => 'Standalone booking request form without requiring a product.'
        ];
    }

    public function onRun()
    {
        $this->settings = Settings::instance();

        $sessionLength = (int) ($this->settings->default_session_length ?? 30);
        $buffer = (int) ($this->settings->booking_interval ?? 15);
        $slotSpacing = $sessionLength + $buffer;

        $this->prepareAvailableSlots($sessionLength, $slotSpacing);

        $this->page['availableDates'] = $this->availableDates;
        $this->page['availableTimes'] = $this->availableTimes;
        $this->page['settings'] = $this->settings;
        $this->page['workingSchedule'] = $this->settings->working_schedule;
        $this->page['interval'] = $slotSpacing;

        $this->page['existingBookings'] = Booking::where('status_id', 2)
            ->where('date', '>=', now())
            ->get()
            ->map(function ($booking) use ($buffer) {
                $start = Carbon::parse($booking->date);
                $length = $booking->session_length ?? 30;
                $end = $start->copy()->addMinutes($length + $buffer);

                return [
                    'start'  => $start->format('Y-m-d H:i:s'),
                    'end'    => $end->format('Y-m-d H:i:s'),
                    'length' => $length + $buffer,
                ];
            });
    }

    protected function prepareAvailableSlots(int $sessionLength, int $slotSpacing)
    {
        $schedule = $this->settings->working_schedule ?: [];

        $this->availableDates = [];
        $allTimes = [];

        $existingBookings = Booking::where('status_id', 2)->get()->map(function ($booking) use ($slotSpacing) {
            $start = Carbon::parse($booking->date);
            $end = $start->copy()->addMinutes(($booking->session_length ?? 30) + $slotSpacing);
            return [
                'start' => $start,
                'end'   => $end,
            ];
        });

        for ($i = 0; $i < 30; $i++) {
            $dayDate = Carbon::now()->addDays($i);
            $dayName = strtolower($dayDate->format('l'));

            $daySchedule = collect($schedule)->firstWhere('day', ucfirst($dayName));
            if (!$daySchedule) continue;

            $this->availableDates[] = $dayName;

            foreach ($daySchedule['time_blocks'] ?? [] as $block) {
                $from = Carbon::createFromFormat('H:i', $block['from']);
                $to = Carbon::createFromFormat('H:i', $block['to']);

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

                    if (!$overlaps) {
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

   public function onBookRequest()
{
    $data = post();

    $rules = [
        'email' => 'required|email',
        'name' => 'required|max:300',
        'street' => 'max:300',
        'town' => 'max:300',
        'zip' => 'nullable|numeric',
        'phone' => 'max:300',
        'message' => 'max:3000',
        'booking_date' => 'required|date',
        'booking_time' => 'required|date_format:Y-m-d H:i:s',
    ];

    $validator = Validator::make($data, $rules);

    if ($validator->fails()) {
        throw new \ValidationException($validator);
    }

    $booking = new Booking();
    $booking->email = $data['email'];
    $booking->name = $data['name'];
    $booking->street = $data['street'] ?? null;
    $booking->town = $data['town'] ?? null;
    $booking->zip = $data['zip'] ?? null;
    $booking->phone = $data['phone'] ?? null;
    $booking->message = $data['message'] ?? null;
    $booking->date = $data['booking_date'];
    $booking->start = $data['booking_time'];
    $booking->length = $this->settings->booking_interval;
    $booking->status_id = 1; // Received
    $booking->user_id = Auth::getUser()?->id;

    $booking->save();

    Flash::success('Your booking has been received. We will contact you shortly.');
}

}
