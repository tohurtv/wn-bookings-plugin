<?php namespace Tohur\Bookings\Models;

use Config;
use Winter\Storm\Database\Model;
use Winter\Storm\Database\Traits\Validation as ValidationTrait;

class Settings extends Model
{
    use ValidationTrait;

    public $implement = ['System.Behaviors.SettingsModel'];

    public $settingsCode = 'tohur_bookings_settings';

    public $settingsFields = 'fields.yaml';

    public $rules = [
        'returning_mark' => 'numeric'
    ];
    public $jsonable = ['working_schedule'];

public static function getWorkingHoursByDay(string $day): array
{
    $settings = self::instance();
    $schedule = $settings->working_schedule ?? [];

    $hours = [];

    foreach ($schedule as $entry) {
        if (strtolower($entry['day']) !== strtolower($day)) {
            continue;
        }

        foreach (($entry['time_blocks'] ?? []) as $block) {
            $hours[] = [
                'from' => $block['from'],
                'to'   => $block['to'],
            ];
        }
    }

    return $hours;
}
}
