<?php namespace Tohur\Bookings\Controllers;

use BackendMenu;
use Backend\Classes\Controller;
use Tohur\Bookings\Models\Booking;

class BookingCalendar extends Controller
{
        public $implement = [
        'Backend\Behaviors\ListController',
        'Backend\Behaviors\FormController',
    ];
    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';
    public $requiredPermissions = ['tohur.bookings.access_bookings'];

    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('Tohur.Bookings', 'bookings', 'calendar');
    }

    public function index()
    {
/*         $this->addCss('/plugins/tohur/bookings/assets/calendar.css');
        $this->addJs('/plugins/tohur/bookings/assets/calendar.js'); */

        $this->pageTitle = 'Booking Calendar';
    }

    public function getApprovedBookings()
    {
        $bookings = Booking::where('status', 2)->get();

        return $bookings->map(function ($booking) {
            return [
                'title' => $booking->name,
                'start' => $booking->date->toDateString(),
                'url'   => Backend::url('tohur/bookings/bookings/update/' . $booking->id),
            ];
        });
    }
}
