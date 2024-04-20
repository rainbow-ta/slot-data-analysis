<?php

namespace App\Services;

use App\Models\HallData;
use Illuminate\Support\Facades\DB;

class EventHallDataService
{
    public function getDataWithEventDate($hallId)
    {
        return HallData::where('hall_id', $hallId)
            ->whereIn('date', function ($query) use ($hallId) {
                $query->select('date')
                    ->from('hall_data')
                    ->where('hall_id', $hallId)
                    ->whereIsHighSetting(1)
                    ->groupBy('date');
            })
            ->with('slotMachine')
            ->orderByDesc('date')
            ->get();
    }
}
