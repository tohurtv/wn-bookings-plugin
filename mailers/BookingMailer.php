<?php

namespace Tohur\Bookings\Mailers;

use App;
use Mail;
use Request;
use Tohur\Bookings\Models\Booking;

class BookingMailer extends BaseMailer
{
    /**
     * Send booking confirmation mail.
     *
     * @param Booking $booking
     * @param int $bookingsCount
     */
    public function send(Booking $booking, $bookingsCount = 0)
    {
        // init
        $locale = App::getLocale();
        $appUrl = App::make('url')->to('/');
        $recipients = $this->initRecipients();
        $recipients['email'] = $booking->email;
        $recipients['name'] = trim($booking->name . ' ' . $booking->lastname);

        $template = $this->getTemplateIdent('booking');

        $templateParameters = [
            'site' => $appUrl,
            'booking' => $booking,
            'locale' => $locale,
            'bookingsCount' => $bookingsCount,
        ];

        if (App::environment() === 'testing') {
            return;
        }

        Mail::send($template, $templateParameters, function ($message) use ($recipients) {
            $message->to($recipients['email'], $recipients['name']);

            if (!empty($recipients['bcc_email']) && !empty($recipients['bcc_name'])) {
                $message->bcc($recipients['bcc_email'], $recipients['bcc_name']);
            }
        });
    }
}
