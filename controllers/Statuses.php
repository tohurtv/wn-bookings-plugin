<?php

namespace Tohur\Bookings\Controllers;

use Backend\Classes\Controller;
use BackendMenu;
use Flash;
use Tohur\Bookings\Models\Status;

class Statuses extends Controller
{
    public $implement = [
        'Backend\Behaviors\ListController',
        'Backend\Behaviors\FormController',
        'Backend\Behaviors\ReorderController',
    ];

    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';
    public $reorderConfig = 'config_reorder.yaml';

    public $requiredPermissions = [
        'tohur.bookings.statuses',
    ];

    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('Tohur.Bookings', 'bookings', 'statuses');
    }

    /**
     * Override displaying status listing.
     *
     * @param Status $record
     * @param string $columnName
     *
     * @return string
     */
    public function listOverrideColumnValue($record, $columnName)
    {
        if ($columnName == 'color') {
            return '<div style="width:18px;height:18px;background-color:' . $record->color . '"></div>';
        }
    }
}
