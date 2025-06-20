<?php

return [

    /**
     * Set up bookings.
     */
    'booking' => [

        /**
         * Bookings interval slot. Used for booking form time picker.
         */
        'interval' => 15,

        /**
         * Length of one booking. How much time one booking takes.
         * Note that when you have booking at 09:00 with 2 hours lenght,
         * next possible booking is at 11:00 and previous possible
         * booking is at 07:00 to not cover booking from 09:00!
         */
        'length' => '2 hours',
    ],

    /**
     * Booking statuses config.
     */
    'statuses' => [

        /**
         * Booking status ident assigned after create.
         */
        'received' => 'received',

        /**
         * Booking status idents that doesn't blocks terms for booking.
         */
        'cancelled' => ['cancelled'],
    ],

    /**
     * Datetime formats.
     */
    'formats' => [

        'date' => 'd/m/Y',

        'time' => 'H:i',
    ],

    /**
     * Send booking confirmation to you as blind carbon copy.
     */
    'mail' => [
        'bcc_email' => '',
        'bcc_name' => '',
    ],

    /**
     * Booking random number. For disable, just set min to 0.
     */
    'number' => [
        'min' => 123456,
        'max' => 999999,
    ],

    /**
     * Booking random hash. For disable set to 0.
     */
    'hash' => 32,

    /**
     * Minimum amount of time between two form submissions.
     *
     * @see http://carbon.nesbot.com/docs/ for syntax
     */
    'protection_time' => '-30 seconds',
];
