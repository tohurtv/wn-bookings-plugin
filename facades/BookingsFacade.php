<?php namespace Tohur\Bookings\Facades;

use Auth;
use Carbon\Carbon;
use Config;
use Event;
use Lang;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Winter\Storm\Exception\ApplicationException;
use Winter\Storm\Exception\ValidationException;
use Tohur\Bookings\Classes\DatesResolver;
use Tohur\Bookings\Classes\Variables;
use Tohur\Bookings\Mailers\BookingAdminMailer;
use Tohur\Bookings\Mailers\BookingMailer;
use Tohur\Bookings\Models\Booking;
use Tohur\Bookings\Models\Settings;
use Tohur\Bookings\Models\Status;

/**
 * Public bookings facade.
 *
 * Usage: App::make(BookingsFacade::class);
 *
 * @package Tohur\Bookings\Facades
 */
class BookingsFacade
{
    /** @var Booking $bookings */
    private $bookings;

    /** @var Status $statuses */
    private $statuses;

    /** @var DatesResolver $datesResolver */
    private $datesResolver;

    /** @var array $returningUsersCache */
    private $returningUsersCache;

    /** @var BookingMailer $mailer */
    private $mailer;

    /** @var BookingAdminMailer $adminMailer */
    private $adminMailer;

    /**
     * BookingsFacade constructor.
     *
     * @param Booking $bookings
     * @param Status $statuses
     * @param DatesResolver $resolver
     * @param BookingMailer $mailer
     * @param BookingAdminMailer $adminMailer
     */
    public function __construct(
        Booking $bookings, Status $statuses, DatesResolver $resolver,
        BookingMailer $mailer, BookingAdminMailer $adminMailer
    ) {
        $this->bookings = $bookings;
        $this->statuses = $statuses;
        $this->datesResolver = $resolver;
        $this->mailer = $mailer;
        $this->adminMailer = $adminMailer;
    }

    /**
     * Create and store booking.
     *
     * @param array $data
     *
     * @return Booking $booking
     *
     * @throws ApplicationException
     * @throws ValidationException
     */
    public function storeBooking($data)
    {
        // check number of sends
        $this->checkLimits();

        // transform date and time to Carbon
        $data['date'] = $this->transformDateTime($data);

        // place for extending
        Event::fire('tohur.bookings.processBooking', [&$data]);

        // create booking
        $booking = $this->bookings->create($data);

        // send mails to client and admin
        $this->sendMails($booking);

        return $booking;
    }

    /**
     * Send mail to client and admin.
     *
     * @param Booking $booking
     */
    public function sendMails($booking)
    {
        // calculate bookings by same email
        $sameCount = $this->getBookingsCountByMail($booking->email);

        // send booking confirmation to customer
        $this->mailer->send($booking, $sameCount);

        // send booking confirmation to admin
        $this->adminMailer->send($booking, $sameCount);
    }

    /**
     * Get all bookings.
     *
     * @return Collection
     */
    public function getBookings()
    {
        return $this->bookings->all();
    }

    /**
     * Get all active (not cancelled) bookings.
     *
     * @return Collection
     */
    public function getActiveBookings()
    {
        return $this->bookings->notCancelled()->currentDate()->get();
    }

    /**
     * Get all reserved time slots.
     *
     * @return array
     */
    public function getReservedDates()
    {
        $bookings = $this->getActiveBookings();

        return $this->datesResolver->getDatesFromBookings($bookings);
    }

    /**
     * Get all bookings by given date interval.
     *
     * @param Carbon $since Date from.
     * @param Carbon $till Date to.
     *
     * @return mixed
     */
    public function getBookingsByInterval(Carbon $since, Carbon $till)
    {
        return $this->bookings->whereBetween('date', [$since, $till])->get();
    }

    /**
     * Get bookings count by one email.
     *
     * @param $email
     *
     * @return int
     */
    public function getBookingsCountByMail($email)
    {
        return $this->bookings->where('email', $email)->notCancelled()->count();
    }

    /**
     * Is user returning or not? You have to set this parameter at Backend Bookings setting.
     *
     * @param $email
     *
     * @return bool
     */
    public function isUserReturning($email)
    {
        // when disabled, user is never returning
        $threshold = Settings::get('returning_mark', 0);
        if ($threshold < 1) {
            return false;
        }

        // load emails count
        if ($this->returningUsersCache === null) {
            $items = $this
                ->bookings
                ->select(DB::raw('email, count(*) as count'))
                ->groupBy('email')
                ->get();
            // refactor to mapWithKeys after upgrade to Laravel 5.3.
            foreach($items as $item) {
                $this->returningUsersCache[$item['email']] = $item['count'];
            }
        }

        $returning = $this->returningUsersCache;
        $actual = isset($returning[$email]) ? $returning[$email] : 0;

        return $threshold > 0 && $actual >= $threshold;
    }

    /**
     * Bulk booking state change.
     *
     * @param array $ids
     * @param string $ident
     */
    public function bulkStateChange($ids, $ident)
    {
        // get state
        $status = $this->statuses->where('ident', $ident)->first();
        if (!$status) {
            return;
        }

        // go through bookings
        foreach ($ids as $id)
        {
            $booking = $this->bookings->find($id);
            if (!$booking) {
                continue;
            }

            $booking->status = $status;
            $booking->save();
        }
    }

    /**
     * Bulk bookings delete.
     *
     * @param array $ids
     */
    public function bulkDelete($ids)
    {
        // go through bookings
        foreach ($ids as $id)
        {
            $booking = $this->bookings->find($id);
            if (!$booking) {
                continue;
            }

            $booking->delete();
        }
    }

    /**
     * Transform date and time to DateTime string.
     *
     * @param $data
     *
     * @return Carbon
     *
     * @throws ApplicationException
     */
    public function transformDateTime($data)
    {
        // validate date
        if (empty($data['date'])) {
            throw new ApplicationException(Lang::get('tohur.bookings::lang.errors.empty_date'));
        }

        // validate time
        if (empty($data['time'])) {
            throw new ApplicationException(Lang::get('tohur.bookings::lang.errors.empty_hour'));
        }

        // convert input to datetime format
        $format = Variables::getDateTimeFormat();
        $dateTime = Carbon::createFromFormat($format, trim($data['date'] . ' ' . $data['time']));

        // validate date + time > current
        if ($dateTime->timestamp < Carbon::now()->timestamp) {
            throw new ApplicationException(Lang::get('tohur.bookings::lang.errors.past_date'));
        }

        // validate days off
        if (!in_array($dateTime->dayOfWeek, $this->getWorkingDays())) {
            throw new ApplicationException(Lang::get('tohur.bookings::lang.errors.days_off'));
        }

        // validate out of hours
        $workTime = $this->getWorkingTime();

        // convert hour and minutes to minutes
        $timeToMinute = $dateTime->hour * 60 + $dateTime->minute;
        $workTimeFrom = $workTime['from']['hour'] * 60 + $workTime['from']['minute'];
        $workTimeTo   = $workTime['to']['hour'] * 60 + $workTime['to']['minute'];

        if ($timeToMinute < $workTimeFrom || $timeToMinute > $workTimeTo) {
            throw new ApplicationException(Lang::get('tohur.bookings::lang.errors.out_of_hours'));
        }

        return $dateTime;
    }

    /**
     * Get working days. We are starting with sunday, because Carbon dayOfWeek for Sunday is 0.
     *
     * @return array
     */
    public function getWorkingDays()
    {
        $daysWorkInput = Variables::getWorkingDays();
        $daysWork = [];
        $allDays = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];

        foreach ($allDays as $index => $day) {
            if (in_array($day, $daysWorkInput)) {
                $daysWork[] = $index;
            }
        }

        return $daysWork;
    }

    /**
     * Get working time.
     *
     * @return array
     */
    public function getWorkingTime()
    {
        $workTime = [];

        $work_time_from = explode(':', Variables::getWorkTimeFrom());
        $workTime['from']['hour'] = (int) $work_time_from[0];
        $workTime['from']['minute'] = isset($work_time_from[1]) ? (int) $work_time_from[1] : 0;

        $work_time_to = explode(':', Variables::getWorkTimeTo());
        $workTime['to']['hour'] = (int) $work_time_to[0];
        $workTime['to']['minute'] = isset($work_time_to[1]) ? (int) $work_time_to[1] : 0;

        return $workTime;
    }

    /**
     * Returns if given date is available.
     *
     * @param Carbon $date
     * @param int $exceptId Except booking ID.
     *
     * @return bool
     */
    public function isDateAvailable($date, $exceptId = null)
    {
        // get boundary dates for given booking date
        $boundaries = $this->datesResolver->getBoundaryDates($date);

        // get all bookings in this date
        $query = $this->bookings->notCancelled()->whereBetween('date', $boundaries);

        // if updating booking, we should skip existing booking
        if ($exceptId !== null) {
            $query->where('id', '!=', $exceptId);
        }

        return $query->count() === 0;
    }

    /**
     * Check bookings amount limit per time.
     *
     * @throws ApplicationException
     */
    private function checkLimits()
    {
        if ($this->isCreatedWhileAgo()) {
            throw new ApplicationException(Lang::get('tohur.bookings::lang.errors.please_wait'));
        }
    }

    /**
     * Try to find some booking in less then given limit (default 30 seconds).
     *
     * @return boolean
     */
    public function isCreatedWhileAgo()
    {
        // protection time
        $time = Config::get('tohur.bookings::config.protection_time', '-30 seconds');
        $timeLimit = Carbon::parse($time)->toDateTimeString();

        // try to find some message
        $item = $this->bookings->machine()->where('created_at', '>', $timeLimit)->first();

        return $item !== null;
    }
}
