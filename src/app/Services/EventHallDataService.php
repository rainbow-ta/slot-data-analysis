<?php

namespace App\Services;
use App\Models\HallData;
use App\Models\SlotMachine;
use Illuminate\Support\Facades\DB;

class EventHallDataService
{
    public function getDifferenceCoinsBySlotMachines($hallData) {
        $differenceCoinsBySlotMachines = [];

        foreach ($hallData as $data) {
            $machineName = $data['slotMachine']['name'];

            if (!isset($differenceCoinsBySlotMachines[$machineName])) {
                $differenceCoinsBySlotMachines[$machineName] = 0;
            }

            $differenceCoinsBySlotMachines[$machineName] += $data['difference_coins'];
        }

        arsort($differenceCoinsBySlotMachines);

        return $differenceCoinsBySlotMachines;
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
