<?php namespace Tohur\Bookings;

use Backend;
use Illuminate\Support\Facades\Validator;
use System\Classes\PluginBase;
use Tohur\Bookings\Facades\BookingsFacade;
use Tohur\Bookings\Validators\BookingsValidators;
use OFFLINE\Mall\Models\Product;
use OFFLINE\Mall\Models\CartProduct;
use OFFLINE\Mall\Models\Cart;
use OFFLINE\Mall\Models\Order;
use OFFLINE\Mall\Models\OrderProduct;
use OFFLINE\Mall\Models\Address;
use Tohur\Bookings\Models\Booking;
use Schema;
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
        $model->addFillable(['isbookable']);
        $model->casts['isbookable'] = 'boolean';
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

    Event::listen('backend.form.extendFields', function ($widget) {
    // Only target the correct controller and model
    if (
        !$widget->getController() instanceof \OFFLINE\Mall\Controllers\Products ||
        !$widget->model instanceof Product
    ) {
        return;
    }

    // Check if 'isbookable' column exists
    if (!Schema::hasColumn('offline_mall_products', 'isbookable')) {
        return;
    }

    // Add session length only if the product is bookable
    if ($widget->model->isbookable) {
        $widget->addTabFields([
            'booking_session_length' => [
                'label'   => 'Booking Session Length',
                'comment' => 'Duration of each booking slot for this product.',
                'type'    => 'dropdown',
                'tab'     => 'offline.mall::lang.product.general',
                'options' => [
                    30  => '30 minutes',
                    60  => '1 hour',
                    90  => '1.5 hours',
                    120 => '2 hours',
                ],
            ],
        ]);
    }
});

    CartProduct::extend(function ($model) {
        // Ensure the property exists and is an array before modifying
        $model->addFillable('booking_data');
        $model->addJsonable('booking_data');
    });

    OrderProduct::extend(function ($model) {
    $model->belongsTo['cart_product'] = [
        CartProduct::class,
        'key' => 'cart_product_id', // this must match the column in `order_products` table
    ];
});

Event::listen('mall.cart.product.added', function (CartProduct $cartItem) {
    $bookingTime = post('booking_time');

    if ($bookingTime) {
        $cartItem->booking_data = [
            'booking_time' => $bookingTime,
        ];

        // Save booking data into the JSON column
        $cartItem->save();
    }
});

/* Event::listen('mall.order.beforeCreate', function (Cart $cart) {
    foreach ($cart->products as $cartProduct) {
        if (!empty($cartProduct->booking_data['booking_time'])) {
            $data = is_array($cartProduct->data) ? $cartProduct->data : [];

            $cartProduct->data = array_merge($data, [
                'booking_data' => $cartProduct->booking_data,
            ]);

            $cartProduct->save();
        }
    }
}); */

Event::listen('mall.orderProduct.beforeCreate', function ($orderProduct, $cartProduct) {
    $orderProduct->cart_product_id = $cartProduct->id;
});

Event::listen('mall.order.afterCreate', function (Order $order, $cart) {
    // No 'billing_address' relation, so don't load it here
    $order->load('products.product', 'customer.user');

    foreach ($order->products as $orderProduct) {
        // Find matching CartProduct by product_id and variant_id
        $matchedCartProduct = $cart->products->first(function ($cartProduct) use ($orderProduct) {
            return $cartProduct->product_id === $orderProduct->product_id &&
                   $cartProduct->variant_id === $orderProduct->variant_id;
        });

        if (!$matchedCartProduct) {
            logger()->warning('No matching CartProduct found for OrderProduct', [
                'order_product_id' => $orderProduct->id,
                'product_id' => $orderProduct->product_id,
                'variant_id' => $orderProduct->variant_id,
            ]);
            continue;
        }

        // Save cart_product_id on orderProduct if needed
        $orderProduct->cart_product_id = $matchedCartProduct->id;
        $orderProduct->save();

        $bookingData = $matchedCartProduct->booking_data ?? [];

        if (!empty($bookingData['booking_time'])) {
            $product = $orderProduct->product;

            if (!$product || !is_scalar($product->id)) {
                logger()->error('Invalid product or product ID', [
                    'product' => $product,
                    'order_product_id' => $orderProduct->id,
                ]);
                continue;
            }

            $booking = new \Tohur\Bookings\Models\Booking();
            $booking->product_id = $product->id;
            $booking->date = $bookingData['booking_time'];
            $booking->session_length = $product->session_length ?? 30;
            $booking->status_id = 1;
            $booking->order_number = $order->order_number;

            $booking->email = $order->customer->user->email ?? null;
            $booking->name = $order->customer->firstname ?? null;
            $booking->lastname = $order->customer->lastname ?? null;

            // Parse billing_address which is JSON string or array
            $address = $order->billing_address;
            if (is_string($address)) {
                $address = json_decode($address, true) ?: [];
            }

            $booking->street = $address['lines'] ?? null;
            $booking->town = $address['city'] ?? null;
            $booking->zip = $address['zip'] ?? null;

            $booking->save();
        }
    }
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
            'Tohur\Bookings\Components\BookableProduct' => 'bookableProduct',
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
