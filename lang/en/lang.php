<?php

return [

    'plugin' => [
        'name' => 'Bookings',
        'category' => 'Bookings',
        'description' => 'Quick bookings plugin.',
        'menu_label' => 'Bookings',
    ],

    'permission' => [
        'tab_label' => 'Bookings',
        'bookings' => 'Bookings management',
        'statuses' => 'Statuses management',
        'export' => 'Bookings export',
    ],

    'bookings' => [
        'menu_label' => 'Bookings',
        'widget_label' => 'Bookings',
        'bulk_actions' => 'Bulk actions',
        'approved' => 'Approve',
        'approved_question' => 'Are you sure to switch bookings as Approved?',
        'closed' => 'Close',
        'closed_question' => 'Are you sure to switch bookings as Closed?',
        'received' => 'Received',
        'received_question' => 'Are you sure to switch bookings as Received?',
        'cancelled' => 'Cancell',
        'cancelled_question' => 'Are you sure to switch bookings as Cancelled?',
        'delete' => 'Delete',
        'delete_question' => 'Are you sure to delete selected bookings?',
        'change_status_success' => 'Booking states has been successfully changed.',
    ],

    'booking' => [
        'date' => 'Date',
        'time' => 'Time',
        'date_format' => 'd.m.Y H:i:s',
        'name' => 'Name',
        'email' => 'Email',
        'phone' => 'Phone',
        'status' => 'Status',
        'created_at' => 'Created at',
        'created_at_format' => 'd.m.Y H:i:s',
        'street' => 'Address / street',
        'message' => 'Message',
        'number' => 'Number',
        'returning' => 'Returning',
        'submit' => 'Submit',
    ],

    'statuses' => [
        'menu_label' => 'Statuses',
        'change_order' => 'Change order',
    ],

    'status' => [
        'name' => 'Status',
        'color' => 'Color',
        'ident' => 'Ident',
        'order' => 'Sort order',
        'enabled' => 'Enabled',
        'created' => 'Created',
        'created_format' => 'd.m.Y H:i:s',
        'updated' => 'Updated',
        'updated_format' => 'd.m.Y H:i:s',
    ],

    'export' => [
        'menu_label' => 'Export',
        'status_filter' => 'Filter by status',
        'status_filter_label' => 'Status',
        'status_filter_tab' => 'Status',
    ],

    'bookingform' => [
        'name' => 'Booking form',
        'description' => 'Form for taking bookings in specific date/time.',
        'success' => 'Booking has been successfully sent!',
    ],

    'mail' => [
        'cs_label' => 'Booking confirmation CS',
        'en_label' => 'Booking confirmation EN',
        'es_label' => 'Booking confirmation ES',
        'fr_label' => 'Booking confirmation FR',
        'ru_label' => 'Booking confirmation RU',
    ],

    'errors' => [
        'empty_date' => 'You have to select pickup date!',
        'empty_hour' => 'You have to select pickup hour!',
        'please_wait' => 'You can sent only one booking per 30 seconds, please wait a second.',
        'session_expired' => 'Form session expired! Please refresh the page.',
        'exception' => 'We\'re sorry, but something went wrong and the form cannot be sent.',
        'already_booked' => 'Date :booking is already booked.',
        'days_off' => 'Selected date is day off.',
        'out_of_hours' => 'Selected time is out of hours.',
        'past_date' => 'Selected date is passed.',
    ],

    'settings' => [
        'description' => 'Manage Bookings settings.',
        'tabs' => [
            'plugin'  => 'Bookings settings',
            'admin'   => 'Admin confirmation',
            'datetime' => 'Date, time settings',
            'returning' => 'Returning customers',
            'working_days' => 'Working days',
        ],

        'returning_mark' => [
            'label'   => 'Mark returning customers',
            'comment' => 'Mark customers with that number of bookings or more. Disable by value 0.',
        ],
        'admin_confirmation_enable' => [
            'label'   => 'Enable admin confirmation',
        ],
        'admin_confirmation_email' => [
            'label'   => 'Admin email',
            'comment' => 'Admin email for sending confirmation.',
        ],
        'admin_confirmation_name' => [
            'label'   => 'Admin name',
            'comment' => 'Admin name for confirmation email.',
        ],
        'admin_confirmation_locale' => [
            'label'   => 'Admin confirmation locale',
            'comment' => 'Locale of confirmation email.',
        ],
        'booking_interval' => [
            'label'   => 'Bookings interval slot (minute)',
            'comment' => 'Used for booking form time picker.',
        ],
        'booking_length' => [
            'label'   => 'Length of one booking',
            'comment' => 'How much time one booking takes.',
        ],
        'booking_length_unit' => [
            'options' => [
                'minutes' => 'minutes',
                'hours' => 'hours',
                'days' => 'days',
                'weeks' => 'weeks',
            ],
        ],
        'formats_date' => [
            'label'   => 'Date format',
            'comment' => 'You can use: d, dd, ddd, dddd, m, mm, mmm, mmmm, yy, yyyy, Y',
        ],
        'formats_time' => [
            'label'   => 'Time format',
            'comment' => 'You can use: h, hh, H, HH, i, a, A',
        ],
        'first_weekday' => [
            'label'   => 'The first day of the week is Monday?',
        ],
        'work_time_from' => [
            'label'   => 'Start working from',
            'comment' => 'Time to format HH:mm (24 hours format)',
        ],
        'work_time_to' => [
            'label'   => 'Finish working at',
            'comment' => 'Time to format HH:mm (24 hours format)',
        ],
        'work_days' => [
            'label'   => 'Work days',
            'monday'    => 'Monday',
            'tuesday'   => 'Tuesday',
            'wednesday' => 'Wednesday',
            'thursday'  => 'Thursday',
            'friday'    => 'Friday',
            'saturday'  => 'Saturday',
            'sunday'    => 'Sunday',
        ],
    ],
];
