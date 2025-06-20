<?php namespace Tohur\Bookings\Validators;

use App;
use Carbon\Carbon;
use Illuminate\Validation\Validator;
use Tohur\Bookings\Facades\BookingsFacade;
use Tohur\Bookings\Models\Booking;
use Tohur\Bookings\Models\Status;

/**
 * Custom Bookings validator.
 *
 * @package Tohur\Bookings\Validators
 */
class BookingsValidators extends Validator
{
    /**
     * Validate booking. Called on date attribute. Validate date availability.
     *
     * Other available variables: $this->translator, $this->data, $this->rules a $this->messages.
     *
     * @param string $attribute Name of the validated field.
     * @param mixed $value Field value.
     *
     * @return bool
     */
	public function validateBooking($attribute, $value)
	{
	    if ($attribute === 'date') {
            return $this->validateDateAttribute($value);
        }

		return false;
	}

    /**
     * Validate date attribute.
     *
     * @param string $value
     *
     * @return bool
     */
	protected function validateDateAttribute($value)
    {
        $date = $this->getDateAsCarbon($value);
        $bookingId = isset($this->data['id']) ? $this->data['id'] : null;

        // disable validation for cancelled bookings
        if ($bookingId !== null) {
            $booking = Booking::findOrFail($bookingId);
            if ($this->isBookingCancelled($booking)) {
                return true;
            }
        }

        return $this->getFacade()->isDateAvailable($date, $bookingId);
    }

    /**
     * Replace placeholder :booking with custom text.
     *
     * @param string $message
     *
     * @return string
     */
    protected function replaceBooking($message)
    {
        $date = $this->getDateAsCarbon($this->data['date']);

        return str_replace(':booking', $date->format('d.m.Y H:i'), $message);
    }

    /**
     * Returns if booking is cancelled or going to be cancelled.
     *
     * @param Booking $booking
     *
     * @return bool
     */
    private function isBookingCancelled($booking)
    {
        $futureStatus = Status::findOrFail($this->data['status_id']);

        return $booking->isCancelled($futureStatus->ident);
    }

    /**
     * Get date as Carbon instance.
     *
     * @param string $date
     *
     * @return Carbon
     */
    private function getDateAsCarbon($date)
    {
        return Carbon::createFromFormat('Y-m-d H:i:s', $date);
    }

    /**
     * Get Bookings facade.
     *
     * @return BookingsFacade
     */
    private function getFacade()
    {
        return App::make('tohur.bookings.facade');
    }
}
