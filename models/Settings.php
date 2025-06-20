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
}
