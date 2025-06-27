<?php

namespace Tohur\Bookings\Classes;

use Config;
use Tohur\Bookings\Models\Settings;

class Variables
{
    public static function getDateTimeFormat()
    {
        return self::getDateFormat() . ' ' . self::getTimeFormat();
    }

    public static function getDateFormat()
    {
        $default = Config::get('tohur.bookings::config.formats.date', 'd/m/Y');

        return Settings::get('formats_date', $default);
    }

    public static function getTimeFormat()
    {
        $default = Config::get('tohur.bookings::config.formats.time', 'H:i');

        return Settings::get('formats_time', $default);
    }

    public static function getBookingInterval()
    {
        $default = Config::get('tohur.bookings::config.booking.interval', 15);

        return (int) Settings::get('booking_interval', $default);
    }

    public static function getBookingLength()
    {
        $default = Config::get('tohur.bookings::config.booking.length', '2 hours');

        $length = Settings::get('booking_length');
        $unit = Settings::get('booking_length_unit');

        if (empty($length) || empty($unit)) {
            return $default;
        }

        return $length . ' ' . $unit;
    }

    public static function getWorkingDays()
    {
        $default = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];

        return Settings::get('work_days', $default);
    }

    public static function getFirstWeekday()
    {
        return (int) Settings::get('first_weekday', false);
    }

    public static function getWorkTimeFrom()
    {
        return Settings::get('work_time_from', '10:00');
    }

    public static function getWorkTimeTo()
    {
        return Settings::get('work_time_to', '18:00');
    }
}
