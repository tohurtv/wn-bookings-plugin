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
    try {
        Log::info('Loading events...');

        $bookings = \Tohur\Bookings\Models\Booking::where('status', 2)->get();

        Log::info('Fetched bookings: ' . $bookings->count());

        $events = $bookings->map(function ($booking) {
            return [
                'title' => $booking->name ?? 'Booking',
                'start' => $booking->date ? $booking->date->format('Y-m-d') : null,
                'allDay' => true,
            ];
        });

        return ['result' => $events];
    } catch (\Throwable $e) {
        Log::error('Error loading events: ' . $e->getMessage());
        throw $e;
    }
}
}