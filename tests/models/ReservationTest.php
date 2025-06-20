<?php namespace Tohur\Bookings\Tests\Models;

use App;
use Carbon\Carbon;
use Config;
use Illuminate\Support\Facades\Validator;
use PluginTestCase;
use Winter\Storm\Database\ModelException;
use Tohur\Bookings\Facades\BookingsFacade;
use Tohur\Bookings\Models\Booking;
use Tohur\Bookings\Models\Status;
use Tohur\Bookings\Validators\BookingsValidators;

class BookingTest extends PluginTestCase
{
    private $defaultStatus;

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
     * Get tested model.
     *
     * @return Booking
     */
    public function getModel()
    {
        return App::make(Booking::class);
    }

    public function testBeforeCreate()
    {
        $model = $this->getModel();

        // create booking
        $booking = $model->create($this->getTestingBookingData());
        $this->assertNotEmpty($booking->hash, 'Booking hash is empty.');
        $this->assertNotEmpty($booking->number, 'Number hash is empty.');
        $this->assertSame(App::getLocale(), $booking->locale, 'Booking locale should be same as app locale.');
        $this->assertNotEmpty($booking->ip, 'IP address is empty.');
        $this->assertNotEmpty($booking->user_agent, 'User Agent is empty.');
        $this->assertSame($this->getDefaultStatus(), $booking->status, 'Booking status should be ' . $this->getDefaultStatus()->name . '.');
    }

    public function testIsDateAvailableFailing()
    {
        $model = $this->getModel();

        // create booking
        $model->create($this->getTestingBookingData());

        // try to do second booking with same date and time
        $this->setExpectedException(ModelException::class, 'tohur.bookings::lang.errors.already_booked');
        $model->create($this->getTestingBookingData());
    }

    public function testIsDateAvailablePassed()
    {
        $model = $this->getModel();

        // create booking
        $model->create($this->getTestingBookingData());

        // try to do second booking with same date and time after 2 hours
        $data = $this->getTestingBookingData();
        $nextMonday = Carbon::parse('next monday')->format('Y-m-d 13:00');
        $data['date'] = Carbon::createFromFormat('Y-m-d H:i', $nextMonday);
        $model->create($data);
    }

    public function testIsDateAvailableForCancelled()
    {
        $model = $this->getModel();

        // create booking
        $booking = $model->create($this->getTestingBookingData());

        // cancel status
        $cancelledStatuses = Config::get('tohur.bookings::config.statuses.cancelled', ['cancelled']);
        $statusIdent = empty($cancelledStatuses) ? 'cancelled' : $cancelledStatuses[0];

        // cancell booking
        $booking->status = Status::where('ident', $statusIdent)->first();
        $booking->save();

        // try to do second booking with same date and time
        $data = $this->getTestingBookingData();
        $model->create($data);
    }

    public function testIsCancelled()
    {
        $model = $this->getModel();

        $booking = $model->create($this->getTestingBookingData());
        $this->assertFalse($booking->isCancelled());
        $this->assertTrue($booking->isCancelled('cancelled'));
    }

    public function testGetHash()
    {
        $model = $this->getModel();

        $firstHash = $model->getUniqueHash();
        $secondHash = $model->getUniqueHash();

        $this->assertNotEquals($firstHash, $secondHash);
    }

    public function testGetEmptyHash()
    {
        $model = $this->getModel();
        Config::set('tohur.bookings::config.hash', 0);
        $this->assertNull($model->getUniqueHash());
    }

    public function testGetNumber()
    {
        $model = $this->getModel();

        $firstNumber = $model->getUniqueNumber();
        $secondNumber = $model->getUniqueNumber();

        $this->assertNotEquals($firstNumber, $secondNumber);
    }

    public function testGetEmptyNumber()
    {
        $model = $this->getModel();
        Config::set('tohur.bookings::config.number.min', 0);
        $this->assertNull($model->getUniqueNumber());
    }

    public function testScopeNotCancelled()
    {
        $model = $this->getModel();

        // create booking
        $booking = $model->create($this->getTestingBookingData());
        $bookings = $model->notCancelled()->get();
        $this->assertNotEmpty($bookings);

        // change booking to cancelled
        $booking->status = Status::where('ident', 'cancelled')->first();
        $booking->save();
        $bookings = $model->notCancelled()->get();
        $this->assertEmpty($bookings);
    }

    public function testScopeCurrentDate()
    {
        $model = $this->getModel();

        // create booking
        $booking = $model->create($this->getTestingBookingData());
        $bookings = $model->currentDate()->get();
        $this->assertNotEmpty($bookings);

        // change booking to the past
        $booking->date = Carbon::parse('-1 month');
        $booking->save();
        $bookings = $model->currentDate()->get();
        $this->assertEmpty($bookings);
    }

    /**
     * Get testing booking data.
     *
     * @return array
     */
    private function getTestingBookingData()
    {
        $nextMonday = Carbon::parse('next monday')->format('Y-m-d 11:00');

        return [
            'date' => Carbon::createFromFormat('Y-m-d H:i', $nextMonday),
            'email' => 'test@test.cz',
            'phone' => '777111222',
            'street' => 'ABCDE',
            'name' => 'Vojta Svoboda',
            'message' => 'Hello.',
            'status' => $this->getDefaultStatus(),
        ];
    }

    /**
     * Get default status object.
     *
     * @return mixed
     */
    private function getDefaultStatus()
    {
        if ($this->defaultStatus === null) {
            $statusIdent = 'received';
            $this->defaultStatus = Status::where('ident', $statusIdent)->first();
        }

        return $this->defaultStatus;
    }
}
