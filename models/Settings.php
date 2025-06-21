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
    public $jsonable = [
    'working_schedule',
    'working_schedule.*.time_blocks'
];

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
public function beforeSave()
{
    $schedule = $this->working_schedule;  // get a copy

    if (is_array($schedule)) {
        foreach ($schedule as &$day) {
            if (!empty($day['time_blocks']) && is_array($day['time_blocks'])) {
                foreach ($day['time_blocks'] as &$block) {
                    // For example, normalize or clean values here
                    if (isset($block['from_raw'])) {
                        $block['from'] = $block['from_raw'];
                        unset($block['from_raw']);
                    }
                    if (isset($block['to_raw'])) {
                        $block['to'] = $block['to_raw'];
                        unset($block['to_raw']);
                    }
                }
                unset($block);
            }
        }
        unset($day);

        $this->working_schedule = $schedule; // re-assign modified array
    }
}
}
