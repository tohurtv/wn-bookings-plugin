<?php

namespace Tohur\Bookings\Classes;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class DatesResolver transform bookings to booked time slots, grouped by date.
 *
 * @package Tohur\Bookings\Classes
 */
class DatesResolver
{
    /**
     * Returns reserved time slots from non cancelled bookings.
     *
     * If you have bookings at 13.10.2016 at 11:00 and also at 13:00, both with 2 hours lenght, this method return
     * all booked slots - from 09:15 to 14:45 (when you have 15 minutes slot lenght).
     *
     * ------------ 11:00 ------------- 13:00 --------------
     *
     * Because nearest booking can be done at 09:00 with 2 hours lenghts and next booking at 15:00.
     *
     * @param Collection $bookings
     *
     * @return array
     */
    public function getDatesFromBookings(Collection $bookings)
    {
        // init
        $interval = Variables::getBookingInterval();
        $length = Variables::getBookingLength();
        $dateFormat = 'd/m/Y';
        $timeFormat = 'H:i';

        // sort bookings by date and count time slots before booking and during the booking
        $dates = [];
        foreach ($bookings as $booking) {
            // init dates
            $startDate = $this->getStartDate($booking, $length, $interval);
            $endDate = $this->getEndDate($booking, $length);

            // save each booked interval
            while ($startDate < $endDate) {
                $time = $startDate->format($timeFormat);
                $date = $startDate->format($dateFormat);
                $dates[$date][$time] = $time;
                $startDate->modify('+' . $interval . ' minutes');
            }
        }

        return $dates;
    }

    /**
     * Get booked interval around the given date.
     *
     * @param Carbon $date
     *
     * @return array
     */
    public function getBoundaryDates(Carbon $date)
    {
        // booking length
        $length = Variables::getBookingLength();

        // boundary dates before and after
        $startDatetime = $this->getBoundaryBefore($date, $length);
        $endDatetime = $this->getBoundaryAfter($date, $length);

        return [$startDatetime, $endDatetime];
    }

    /**
     * Get boundary date before booking date.
     *
     * @param Carbon $date
     * @param string $length
     *
     * @return mixed
     */
    private function getBoundaryBefore(Carbon $date, $length)
    {
        $startDatetime = clone $date;
        $startDatetime->modify('-' . $length);
        $startDatetime->modify('+1 second');

        return $startDatetime;
    }

    /**
     * Get boundary date after booking date.
     *
     * @param Carbon $date
     * @param string $length
     *
     * @return mixed
     */
    private function getBoundaryAfter(Carbon $date, $length)
    {
        $endDatetime = clone $date;
        $endDatetime->modify('+' . $length);
        $endDatetime->modify('-1 second');

        return $endDatetime;
    }

    /**
     * Get booking imaginary start date.
     *
     * @param $booking
     * @param $length
     * @param $interval
     *
     * @return mixed
     */
    protected function getStartDate($booking, $length, $interval)
    {
        $startDate = $booking->date;
        $startDate->modify('-' . $length);
        $startDate->modify('+' . $interval . ' minutes');

        return $startDate;
    }

    /**
     * Get booking imaginary end date.
     *
     * @param $booking
     * @param $length
     *
     * @return mixed
     */
    protected function getEndDate($booking, $length)
    {
        $endDate = clone $booking->date;
        $endDate->modify('+' . $length);

        return $endDate;
    }

    /**
     * Get date format.
     *
     * @return string
     *
     * @deprecated Use Variables::getDateFormat() instead.
     */
    protected function getDateFormat()
    {
        return Variables::getDateFormat();
    }

    /**
     * Get time format.
     *
     * @return string
     *
     * @deprecated Use Variables::getTimeFormat() instead.
     */
    protected function getTimeFormat()
    {
        return Variables::getTimeFormat();
    }

    /**
     * Get booking interval length.
     *
     * @return string
     *
     * @deprecated Use Variables::getBookingInterval() instead.
     */
    protected function getInterval()
    {
        return Variables::getBookingInterval();
    }

    /**
     * Get booking length.
     *
     * @return string
     *
     * @deprecated Use Variables::getBookingLength() instead.
     */
    protected function getLength()
    {
        return Variables::getBookingLength();
    }
}
