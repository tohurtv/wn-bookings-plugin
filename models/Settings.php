<?php namespace Tohur\Bookings\Models;

use Config;
use Winter\Rain\Database\Model;
use Winter\Rain\Database\Traits\Validation as ValidationTrait;

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
