<?php namespace Tohur\Bookings\Tests\Facades;

use App;
use Carbon\Carbon;
use Config;
use Illuminate\Support\Facades\Validator;
use PluginTestCase;
use Tohur\Bookings\Facades\BookingsFacade;
use Tohur\Bookings\Models\Settings;
use Tohur\Bookings\Validators\BookingsValidators;

class BookingsFacadeTest extends PluginTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->app->bind('tohur.bookings.facade', BookingsFacade::class);

        // registrate bookings validators
        Validator::resolver(function($translator, $data, $rules, $messages, $customAttributes) {
            return new BookingsValidators($translator, $data, $rules, $messages, $customAttributes);
        });
    }

    /**
     * Returns tested class.
     *
     * @return BookingsFacade
     */
    public function getModel()
    {
        return App::make(BookingsFacade::class);
    }

    public function testStoreEmptyBooking()
    {
        $model = $this->getModel();

        $this->setExpectedException('Winter\Rain\Exception\ApplicationException');
        $model->storeBooking([]);
    }

    public function testStoreBookingWithoutTime()
    {
        $model = $this->getModel();

        $this->setExpectedException('Winter\Rain\Exception\ApplicationException');
        $nextMonday = Carbon::parse('next monday')->format('d/m/Y');
        $model->storeBooking([
            'date' => $nextMonday,
        ]);
    }

    public function testStoreBookingDaysOff()
    {
        $model = $this->getModel();
        $default = Settings::get('work_days', ['monday', 'tuesday', 'wednesday', 'thursday', 'friday']);
        Settings::set('work_days', []);

        $data = $this->getTestingBookingData();
        foreach (['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $dayOfWeek) {
            $exceptionTest = null;
            try {
                $data['date'] = Carbon::parse('next '.$dayOfWeek)->format('d/m/Y');
                $model->storeBooking($data);
            } catch (\Exception $exception) {
                $exceptionTest = $exception;
            }
            $this->assertEquals('Winter\Rain\Exception\ApplicationException', get_class($exceptionTest));
            $this->assertEquals('tohur.bookings::lang.errors.days_off', $exceptionTest->getMessage());
        }

        Settings::set('work_days', $default);
    }

    public function testStoreBookingWorkingDays()
    {
        $default = Config::get('tohur.bookings::config.protection_time', '-30 seconds');
        Config::set('tohur.bookings::config.protection_time', '0 seconds');
        $model = $this->getModel();
        Settings::set('work_days', ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday']);

        $data = $this->getTestingBookingData();
        foreach (['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $dayOfWeek) {
            $data['date'] = Carbon::parse('next '.$dayOfWeek)->format('d/m/Y');
            $model->storeBooking($data);
        }

        Config::set('tohur.bookings::config.protection_time', $default);
    }

    public function testStoreBookingOutOfHours()
    {
        $model = $this->getModel();

        $data = $this->getTestingBookingData();
        $data['time'] = '19:00';

        $this->setExpectedException('Winter\Rain\Exception\ApplicationException');
        $model->storeBooking($data);
    }

    public function testStoreBookingInThePast()
    {
        $model = $this->getModel();

        $data = $this->getTestingBookingData();
        $data['date'] = Carbon::parse("last monday - 7 days")->format('d/m/Y');

        $this->setExpectedException('Winter\Rain\Exception\ApplicationException');
        $model->storeBooking($data);
    }

    public function testStoreBooking()
    {
        $model = $this->getModel();
        $booking = $model->storeBooking($this->getTestingBookingData());

        // check status
        $defaultStatusIdent = Config::get('tohur.bookings::config.statuses.received', 'received');
        $this->assertEquals($defaultStatusIdent, $booking->status->ident);

        // check locale
        $locale = App::getLocale();
        $this->assertEquals($locale, $booking->locale);

        // check date and time
        $testingData = $this->getTestingBookingData();
        $inputDate = $testingData['date'] . ' ' . $testingData['time'];
        $dateTime = Carbon::createFromFormat('d/m/Y H:i', $inputDate);
        $this->assertEquals($dateTime, $booking->date);
    }

    public function testDoubleStoreBookingUnder30Seconds()
    {
        $model = $this->getModel();
        $testingData = $this->getTestingBookingData();
        $model->storeBooking($testingData);

        $this->setExpectedException('Winter\Rain\Exception\ApplicationException');
        $model->storeBooking($testingData);
    }

    public function testTransformDateTime()
    {
        $model = $this->getModel();

        $nextMonday = Carbon::parse('next monday');
        $data = [
            'date' => $nextMonday->format('d/m/Y'),
            'time' => '15:45',
        ];
        $date = $model->transformDateTime($data);

        $this->assertInstanceOf('Carbon\Carbon', $date);
        $this->assertEquals($nextMonday->format('Y-m-d').' 15:45:00', $date->format('Y-m-d H:i:s'));
    }

    public function testGetBookingsCountByMail()
    {
        $model = $this->getModel();

        // create one booking with test@test.cz email
        $model->storeBooking($this->getTestingBookingData());

        $count = $model->getBookingsCountByMail('tohur.cz@gmail.com');
        $this->assertEquals(0, $count);

        $count = $model->getBookingsCountByMail('test@test.cz');
        $this->assertEquals(1, $count);
    }

    public function testIsUserReturning()
    {
        $model = $this->getModel();

        // enable Returning Customers function
        Settings::set('returning_mark', 1);

        // is returning without any booking?
        $isReturning = $model->isUserReturning('test@test.cz');
        $this->assertEquals(false, $isReturning, 'There is no booking, so customer cant be returning.');

        // create one booking with test@test.cz email
        $model->storeBooking($this->getTestingBookingData());

        // is returning without any booking?
        $isReturning = $model->isUserReturning('tohur.cz@gmail.com');
        $this->assertEquals(false, $isReturning, 'Email tohur.cz@gmail.com does not has any booking, so it should not be marked as returning customer.');

        // is returning with one booking?
        $isReturning = $model->isUserReturning('test@test.cz');
        $this->assertEquals(true, $isReturning, 'Email test@test.cz has one booking, so it should be marked as returning customer.');
    }

    public function testIsCreatedWhileAgo()
    {
        $model = $this->getModel();
        $exists = $model->isCreatedWhileAgo();

        $this->assertFalse($exists);

        // create fake booking
        $model->storeBooking($this->getTestingBookingData());
        $exists = $model->isCreatedWhileAgo();

        $this->assertTrue($exists);
    }

    private function getTestingBookingData()
    {
        $nextMonday = Carbon::parse('next monday')->format('d/m/Y');

        return [
            'date' => $nextMonday,
            'time' => '11:00',
            'email' => 'test@test.cz',
            'phone' => '777111222',
            'street' => 'ABCDE',
            'name' => 'Vojta Svoboda',
            'message' => 'Hello.',
        ];
    }
}
