<?php namespace Tohur\Bookings\Tests\Mailers;

use App;
use PluginTestCase;
use Tohur\Bookings\Mailers\BaseMailer;
use Tohur\Bookings\Mailers\BookingMailer;

class BaseMailerTest extends PluginTestCase
{
    /**
     * Get model.
     *
     * @return BaseMailer
     */
    public function getModel()
    {
        return App::make(BaseMailer::class);
    }

    public function testGetTemplateIdent()
    {
        $model = $this->getModel();

        $ident = $model->getTemplateIdent('booking');
        $locale = App::getLocale();

        $this->assertEquals('tohur.bookings::mail.booking-' . $locale, $ident);
    }

    public function testGetTemplateIdentWithLocale()
    {
        $model = $this->getModel();

        $ident = $model->getTemplateIdent('booking-admin', 'cs');

        $this->assertEquals('tohur.bookings::mail.booking-admin-cs', $ident);
    }
}
