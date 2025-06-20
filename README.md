# Bookings plugin for WinterCMS

[![Build Status](https://travis-ci.org/tohurtv/wn-bookings-plugin.svg?branch=master)](https://travis-ci.org/tohurtv/wn-bookings-plugin)
[![Codacy](https://img.shields.io/codacy/d46420185c9046db8208ab16d358a0d3.svg)](https://www.codacy.com/app/tohurtv/wn-bookings-plugin)
[![Code Coverage](https://scrutinizer-ci.com/g/tohurtv/wn-bookings-plugin/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/tohurtv/wn-bookings-plugin/?branch=master)
[![License](https://img.shields.io/badge/license-MIT-blue.svg)](https://github.com/tohurtv/wn-bookings-plugin/blob/master/LICENSE)

Provide booking form with bookings management. You should also check related plugin: [backend calendar](http://wintercms.com/tohurtv/tohur-bookingscalendar).

Key features:

- bookings have **coloured statuses**, **bulk actions** and **fulltext search** to save your time
- nice and clean **dashboard widget**
- bookings **export** with status filtering
- booking can be created directly from the backend
- returning customers function

Technical features:

- shipped with **translations** and covered by **unit tests**
- booking form has **CSRF protection** and **multiple bots submissions protection**
- booking form has **AJAX sending** and also non-JS fallback
- overloadable **data seeding** for statuses

No other plugin dependencies. Tested with the latest stable WinterCMS build 420 (with Laravel 5.5).

## Installation


## Returning Customers

Plugin allow you to mark returning customers:

- set amount of previous bookings at **Backend > Settings > Bookings > Bookings** 
- at bookings listing, click at the list settings (the hamburger at the right corner) and check Returning
- it shows star at customers with more then <your-threshold> non-canceled bookings

## Admin confirmation

By default, plugin sends confirmation email to customer. But you can also turn on sending confirmation to different user 
(your customer, system administrator, etc). Follow these steps to turn this feature on:

- set admin email and name at **Backend > Settings > Bookings > Bookings** at **Admin confirmation** tab
- turn the admin confirmation by switch
- system will send special template 'booking-admin', so feel free edit content of template at **Backend > Settings > Mail > Mail templates**


## Public facade

You can use plugin's facade **tohur.bookings.facade** with some public methods as follows:

```
$facade = App::make('tohur.bookings.facade');
$facade->storeBooking(array $data);
$facade->getBookings();
$facede->getActiveBookings();
$facade->getReservedDates();
$facade->getBookingsByInterval(\Carbon\Carbon $from, \Carbon\Carbon $to);
$facade->isDateAvailable(\Carbon\Carbon $date);
```

## Configuration

You can find some plugin configuration at the CMS backend (datetime format, booking length, time slot length, etc). 
But you can also set some values at plugin's config file. Config values are used when Settings value can not be found 
(and also because of backward compatibility with users using older version of plugin).

When you want to override default plugin's *config.php*, which is placed at plugin's folder */config*, just create file:

`/config/tohur/bookings/config.php`

And override values you want to change. Example of this file:

```
<?php return [
    'formats' => [
        'date' => 'd.m.Y H:i:s',
    ],
];
```

## Override seeding

For override seeding just copy seed files from plugin's folder */updates/sources* and copy them to:
 
`/resources/tohur/bookings/updates/sources/`

For example:

`/resources/tohur/bookings/updates/sources/statuses.yaml`

This file will be load with first migration, or you can force refreshing migrations with this command:

`php artisan plugin:refresh Tohur.Bookings`

## Unit tests

Just run `phpunit` in the plugin directory. For running plugin's unit tests with project tests,
add this to your project *phpunit.xml* file:

```
<testsuites>
    <testsuite name="Booking Tests">
        <directory>./plugins/tohur/bookings/tests</directory>
    </testsuite>
</testsuites>
```

Receiving "Class 'PluginTestCase' not found" error? Just type `composer dumpautoload` at your project root.

## TODO

- [ ] Checkbox for disabling injecting assets to the components.
- [ ] Move date validation from facade to the model (it should works also when creating booking from backend)
- [ ] Automatically load statuses for bookings listing/filtration.
- [ ] Assets concatenation.
- [ ] Log history of booking changes.
- [ ] Make bulk booking status change in one SQL query.
- [ ] Order by returning flag without SQL exception.
- [ ] Translate statuses at backend.
- [ ] Translations with Translate trait.
- [ ] Can send iCal link in the e-mail.
- [ ] Show only future dates in datepicker.
- [ ] Load only future bookings to the datepicker to show reserved slots.
- [ ] Bookings reminder by email/SMS, before booking
- [ ] Own function (callback) for generating next booking number.
- [ ] Sends confirmation email when admin [confirms the booking](https://github.com/tohurtv/wn-bookings-plugin/issues/2).

**Feel free to send pull request!**

## Contributing

Please send Pull Request to the master branch. Please add also unit tests and make sure all unit tests are green.

## License

Bookings plugin is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT) same as WinterCMS platform.
