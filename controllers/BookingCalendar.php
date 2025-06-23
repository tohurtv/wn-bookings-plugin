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
    }

public function onLoadEvents()
{
    $bookings = Booking::where('status_id', 2)->get();

    $events = $bookings->map(function ($booking) {
        return [
            'title' => $booking->name,
            'start' => $booking->date->format('Y-m-d'),
            'allDay' => true,
        ];
    });

    return [
        'result' => $events
    ];
}
}