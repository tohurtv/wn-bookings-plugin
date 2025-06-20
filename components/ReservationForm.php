<?php namespace Tohur\Bookings\Components;

use App;
use Cms\Classes\ComponentBase;
use Exception;
use Flash;
use Illuminate\Support\Facades\Log;
use Input;
use Lang;
use Winter\Storm\Exception\ApplicationException;
use Winter\Storm\Exception\ValidationException;
use Redirect;
use Session;
use Tohur\Bookings\Classes\Variables;
use Tohur\Bookings\Facades\BookingsFacade;

/**
 * Booking Form component.
 *
 * @package Tohur\Bookings\Components
 */
class BookingForm extends ComponentBase
{
    const PATH_PICKADATE_COMPRESSED = '/plugins/tohur/bookings/assets/vendor/pickadate/lib/compressed/';

    protected $pickerLang = [
        'cs' => 'cs_CZ',
        'es' => 'es_ES',
        'ru' => 'ru_RU',
        'fr' => 'fr_FR',
    ];

    public function componentDetails()
	{
		return [
			'name' => 'tohur.bookings::lang.bookingform.name',
			'description' => 'tohur.bookings::lang.bookingform.description',
		];
	}

    /**
     * AJAX form submit handler.
     */
    public function onSubmit()
    {
        // check CSRF token
        if (Session::token() !== Input::get('_token')) {
            throw new ApplicationException(Lang::get('tohur.bookings::lang.errors.session_expired'));
        }

        $data = Input::all();
        $this->getFacade()->storeBooking($data);
    }

    /**
     * Fallback for non-ajax POST request.
     */
	public function onRun()
	{
        $facade = $this->getFacade();

		$error = false;
		if (Input::get($this->alias . '-submit')) {

            // check CSRF token
            if (Session::token() !== Input::get('_token')) {
                $error = Lang::get('tohur.bookings::lang.errors.session_expired');

            } else {
                try {
                    $data = Input::all();
                    $facade->storeBooking($data);
                    $msg = Lang::get('tohur.bookings::lang.bookingform.success');
                    Flash::success($msg);

                    return Redirect::to($this->page->url . '#' . $this->alias, 303);

                } catch(ValidationException $e) {
                    $error = $e->getMessage();

                } catch(ApplicationException $e) {
                    $error = $e->getMessage();

                } catch(Exception $e) {
                    Log::error($e->getMessage());
                    $error = Lang::get('tohur.bookings::lang.errors.exception');
                }
            }
		}

		// inject assets
        $this->injectAssets();

		// load booked dates and their time slots
        $dates = $this->getReservedDates();

        // template data
		$this->page['sent'] = Flash::check();
		$this->page['post'] = post();
		$this->page['error'] = $error;
        $this->page['dates'] = json_encode($dates);
        $this->page['settings'] = $this->getCalendarSetting();
	}

    /**
     * Get reserved dates.
     *
     * @return array
     */
    protected function getReservedDates()
    {
        return $this->getFacade()->getReservedDates();
    }

    /**
     * @return array
     */
    protected function getCalendarSetting()
    {
        return [
            'formats_date' => Variables::getDateFormat(),
            'formats_time' => Variables::getTimeFormat(),
            'booking_interval' => Variables::getBookingInterval(),
            'first_weekday' => Variables::getFirstWeekday(),
            'work_time_from' => Variables::getWorkTimeFrom(),
            'work_time_to' => Variables::getWorkTimeTo(),
            'work_days' => Variables::getWorkingDays(),
        ];
    }

    /**
     * Inject components assets.
     */
    protected function injectAssets()
    {
        $this->addCss(self::PATH_PICKADATE_COMPRESSED.'themes/classic.css');
        $this->addCss(self::PATH_PICKADATE_COMPRESSED.'themes/classic.date.css');
        $this->addCss(self::PATH_PICKADATE_COMPRESSED.'themes/classic.time.css');
        $this->addJs(self::PATH_PICKADATE_COMPRESSED.'picker.js');
        $this->addJs(self::PATH_PICKADATE_COMPRESSED.'picker.date.js');
        $this->addJs(self::PATH_PICKADATE_COMPRESSED.'picker.time.js');

        $locale = Lang::getLocale();
        $translation = isset($this->pickerLang[$locale]) ? $this->pickerLang[$locale] : null;
        if ($translation !== null) {
            $this->addJs(self::PATH_PICKADATE_COMPRESSED.'translations/'.$translation.'.js');
        }

        $this->addJs('/plugins/tohur/bookings/assets/js/convert.js');
        $this->addJs('/plugins/tohur/bookings/assets/js/bookingform.js');
    }

    /**
     * @return BookingsFacade
     */
    protected function getFacade()
    {
        return App::make(BookingsFacade::class);
    }
}
