<?php

namespace Tohur\Bookings\Models;

use Backend\Facades\BackendAuth;
use Backend\Models\ExportModel;
use Config;

/**
 * Booking's export.
 *
 * @package Tohur\Bookings\Models
 */
class BookingExport extends ExportModel
{
    public $table = 'tohur_bookings_bookings';

    public $dates = ['created_at', 'updated_at', 'deleted_at'];

    public $belongsTo = [
        'status' => 'Tohur\Bookings\Models\Status',
    ];

    public $fillable = [
        'status_enabled',
        'status',
    ];

    /**
     * Prepare data for export.
     *
     * @param $columns
     * @param $sessionKey
     *
     * @return array
     */
    public function exportData($columns, $sessionKey = null)
    {
        $query = Booking::query();

        // filter by status
        if ($this->status_enabled) {
            $query->where('status_id', $this->status_id);
        }

        // prepare columns
        $bookings = $query->get();
        $bookings->each(function ($item) use ($columns) {
            $item->addVisible($columns);
            $item->status_id = $item->status->name;
        });

        return $bookings->toArray();
    }

    /**
     * Get all available statuses.
     *
     * @return mixed
     */
    public static function getStatusIdOptions()
    {
        return Status::orderBy('sort_order')->lists('name', 'id');
    }
}
