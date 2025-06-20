<?php namespace Tohur\Bookings\Mailers;

use App;
use Mail;
use Request;
use Tohur\Bookings\Models\Booking;
use Tohur\Bookings\Models\Settings;

class BookingAdminMailer extends BaseMailer
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
        $enabled = Settings::get('admin_confirmation_enable');
        $templateLocale = Settings::get('admin_confirmation_locale');
        $recipients = $this->initRecipients();
        $recipients['email'] = Settings::get('admin_confirmation_email');
        $recipients['name'] = Settings::get('admin_confirmation_name');

        // skip if disabled or empty email
        if (!$enabled || empty($recipients['email'])) {
            return;
        }

        $template = $this->getTemplateIdent('booking-admin', $templateLocale);

        $templateParameters = [
            'site' => $appUrl,
            'booking' => $booking,
            'locale' => $locale,
            'bookingsCount' => $bookingsCount,
        ];

        if (App::environment() === 'testing') {
            return;
        }

        Mail::send($template, $templateParameters, function($message) use ($recipients)
        {
            $message->to($recipients['email'], $recipients['name']);

            if (!empty($recipients['bcc_email']) && !empty($recipients['bcc_name'])) {
                $message->bcc($recipients['bcc_email'], $recipients['bcc_name']);
            }
        });
    }
}
