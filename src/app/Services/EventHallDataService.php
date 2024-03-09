<?php

namespace App\Services;

use App\Models\HallData;
use Illuminate\Support\Facades\DB;

class EventHallDataService
{
    public function getDataWithEventDate($hallId)
    {
        return HallData::where('hall_id', $hallId)
            ->with('slotMachine')
            ->orderBy('date', 'desc')
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('hall_events')
                    ->whereRaw('hall_events.hall_id = hall_data.hall_id')
                    ->whereRaw('hall_events.date = hall_data.date');
            })
            ->get();
    }
}
