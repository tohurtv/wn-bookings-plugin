<?php namespace Tohur\Bookings;

use Backend;
use Illuminate\Support\Facades\Validator;
use System\Classes\PluginBase;
use Tohur\Bookings\Facades\BookingsFacade;
use Tohur\Bookings\Validators\BookingsValidators;
use Event;

class Plugin extends PluginBase
{
    public function boot()
    {
        $this->app->bind('tohur.bookings.facade', BookingsFacade::class);

        // registrate bookings validators
        Validator::resolver(function($translator, $data, $rules, $messages, $customAttributes) {
            return new BookingsValidators($translator, $data, $rules, $messages, $customAttributes);
        });

     if (!\System\Classes\PluginManager::instance()->exists('OFFLINE.Mall')) {
        return;
    }

    \OFFLINE\Mall\Models\Product::extend(function ($model) {
        $model->addFillable(['is_bookable']);
        $model->casts['is_bookable'] = 'boolean';
    });

    Event::listen('backend.form.extendFields', function ($widget) {
        if (
            !$widget->getController() instanceof \OFFLINE\Mall\Controllers\Products ||
            !$widget->model instanceof \OFFLINE\Mall\Models\Product
        ) {
            return;
        }

        $widget->addTabFields([
            'isbookable' => [
                'label'   => 'Is bookable',
                'comment' => 'Indicates whether this product can be booked.',
                'type'    => 'switch',
                'tab'     => 'offline.mall::lang.product.general',
            ],
        ]);
    });
    }

    public function registerNavigation()
    {
        return [
            'bookings' => [
                'label'       => 'tohur.bookings::lang.plugin.menu_label',
                'url'         => Backend::url('tohur/bookings/bookings'),
                'icon'        => 'icon-calendar-o',
                'permissions' => ['tohur.bookings.*'],
                'order'       => 500,
                'sideMenu' => [
                    'bookings' => [
                        'label'       => 'tohur.bookings::lang.bookings.menu_label',
                        'url'         => Backend::url('tohur/bookings/bookings'),
                        'icon'        => 'icon-calendar-o',
                        'permissions' => ['tohur.bookings.bookings'],
                        'order'       => 100,
                    ],
                    'statuses' => [
                        'label'       => 'tohur.bookings::lang.statuses.menu_label',
                        'icon'        => 'icon-star',
                        'url'         => Backend::url('tohur/bookings/statuses'),
                        'permissions' => ['tohur.bookings.statuses'],
                        'order'       => 200,
                    ],
                    'export' => [
                        'label'       => 'tohur.bookings::lang.export.menu_label',
                        'icon'        => 'icon-sign-out',
                        'url'         => Backend::url('tohur/bookings/bookings/export'),
                        'permissions' => ['tohur.bookings.export'],
                        'order'       => 300,
                    ],
                ],
            ],
        ];
    }

    public function registerComponents()
    {
        return [
            'Tohur\Bookings\Components\BookingForm' => 'bookingForm',
        ];
    }

    public function registerReportWidgets()
    {
        return [
            'Tohur\Bookings\ReportWidgets\Bookings' => [
                'label'   => 'tohur.bookings::lang.bookings.widget_label',
                'context' => 'dashboard',
            ],
        ];
    }

    public function registerMailTemplates()
    {
        return [
            'tohur.bookings::mail.booking-cs' => 'Booking confirmation CS',
            'tohur.bookings::mail.booking-en' => 'Booking confirmation EN',
            'tohur.bookings::mail.booking-es' => 'Booking confirmation ES',
            'tohur.bookings::mail.booking-fr' => 'Booking confirmation FR',
            'tohur.bookings::mail.booking-ru' => 'Booking confirmation RU',
            'tohur.bookings::mail.booking-admin-cs' => 'Booking confirmation for admin CS',
            'tohur.bookings::mail.booking-admin-en' => 'Booking confirmation for admin EN',
            'tohur.bookings::mail.booking-admin-es' => 'Booking confirmation for admin ES',
            'tohur.bookings::mail.booking-admin-fr' => 'Booking confirmation for admin FR',
            'tohur.bookings::mail.booking-admin-ru' => 'Booking confirmation for admin RU',
        ];
    }
public function registerFormWidgets()
{
    return [
        'Tohur\Bookings\FormWidgets\TimePicker' => [
            'label' => 'Custom Time Picker',
            'code' => 'tohur_bookings_timepicker',
        ],
    ];
}
    public function registerSettings()
    {
        return [
            'settings' => [
                'category' => 'tohur.bookings::lang.plugin.category',
                'label' => 'tohur.bookings::lang.plugin.name',
                'description' => 'tohur.bookings::lang.settings.description',
                'icon' => 'icon-calendar-o',
                'class' => 'Tohur\Bookings\Models\Settings',
                'order' => 100,
            ],
        ];
    }
}
