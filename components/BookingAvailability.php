<?php namespace Tohur\Bookings\Components;

use Cms\Classes\ComponentBase;
use OFFLINE\Mall\Models\Product;

class BookingAvailability extends ComponentBase
{
    public $product;
    public $bookingSettings;

    public function componentDetails()
    {
        return [
            'name' => 'Booking Availability',
            'description' => 'Displays booking availability if product is bookable.'
        ];
    }

    public function defineProperties()
    {
        return [
            'productSlug' => [
                'title' => 'Product Slug',
                'description' => 'Slug of the product to show booking info for',
                'type' => 'string',
                'default' => '{{ :slug }}'
            ]
        ];
    }

    public function onRun()
    {
        $slug = $this->property('productSlug');

        $this->product = Product::where('slug', $slug)->first();

        if (!$this->product || !$this->product->isbookable) {
            return;
        }

        // Load your booking settings (or any other logic)
        $this->bookingSettings = \Tohur\Bookings\Models\Settings::instance();

        // Pass variables to the partial automatically
        $this->page['product'] = $this->product;
        $this->page['bookingSettings'] = $this->bookingSettings;
    }
}
