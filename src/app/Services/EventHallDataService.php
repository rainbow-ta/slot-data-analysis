<?php

namespace App\Services;

use App\Models\HallData;
use App\Models\SlotMachine;
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

    public function getSumDifferenceCoins($hallData)
    {
        return $hallData->groupBy('slot_number')
            ->map(function ($group) {
                $slotMachineName = $group->first()->slotMachine->name;
                $sumDifference = $group->sum('difference_coins');
                return [
                    'slot_machine_name' => $slotMachineName,
                    'slot_number' => $group->first()->slot_number,
                    'sum_difference_coins' => $sumDifference,
                ];
            });
    }
}
