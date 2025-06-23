<?php namespace Tohur\Bookings\Controllers;

use BackendMenu;
use Backend\Classes\Controller;
use Tohur\Bookings\Models\Booking;

class BookingCalendar extends Controller
{
    public $requiredPermissions = ['tohur.bookings.access_calendar'];
    public $bodyClass = 'compact-container';
    public $implement = [];

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('Tohur.Bookings', 'bookings', 'calendar');
    }

    public function index()
    {
        $this->pageTitle = 'Booking Calendar';
        $this->vars['events'] = \Tohur\Bookings\Models\Booking::where('status', 2)->get()->map(function ($booking) {
        return [
            'title' => $booking->name ?? 'Booking',
            'start' => optional($booking->date)->format('Y-m-d'),
            'allDay' => true,
        ];
    });
    }

}