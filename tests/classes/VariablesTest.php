<?php namespace Tohur\Bookings\Tests\Variables;

use PluginTestCase;
use Tohur\Bookings\Classes\Variables;
use Tohur\Bookings\Models\Settings;

class VariablesTest extends PluginTestCase
{
    public function testGetDateTime()
    {
        $result = Variables::getDateTimeFormat();
        $this->assertSame('d/m/Y H:i', $result);
    }

    public function testGetBookingLength()
    {
        $result = Variables::getBookingLength();
        $this->assertSame('2 hours', $result);
    }

    public function testGetBookingLengthAfterSet()
    {
        Settings::set('booking_length', 90);
        Settings::set('booking_length_unit', 'minutes');
        $result = Variables::getBookingLength();
        $this->assertSame('90 minutes', $result);
    }
}
