<?php

namespace App\Services;
use App\Models\HallData;

class HallDataService
{
    public function fetchHallData($hallId) {
        return HallData::whereHallId($hallId)
            ->with('slotMachine')
            ->orderBy('date', 'desc')
            ->get();
    }

    public function matsubiCount($hallData)
    {
        $matsubiArray = [];

        foreach ($hallData as $data) {
            if ($data['is_high_setting'] === 1) {
                $date = $data['date'];
                $matsubi = substr($data['slot_number'], -1);

                if (!isset($matsubiArray[$date][$matsubi])) {
                    $matsubiArray[$date][$matsubi] = 0;
                }

                $matsubiArray[$date][$matsubi]++;
            }
        }

        return $matsubiArray;
    }

    public function matsubiTotals($matsubiArray)
    {
        $matsubiTotals = [];
        foreach ($matsubiArray as $matsubi) {
            foreach ($matsubi as $key => $value) {
                if (!isset($matsubiTotals[$key])) {
                    $matsubiTotals[$key]['total'] = 0;
                }
                $matsubiTotals[$key]['total'] += $value;
            }
        }

        ksort($matsubiTotals);
        $total = array_sum(array_column($matsubiTotals, 'total'));

        foreach ($matsubiTotals as &$data) {
            $percentage = round(($data['total'] / $total) * 100, 1);
            $data['percentage'] = $percentage . '%';
        }

        return $matsubiTotals;
    }

    public function highSettingNumbersCount($hallData)
    {
        return $hallData->where('is_high_setting', 1)->groupBy('slot_number')->map(function ($group) {
            $slotNumber = $group->first()['slot_number'];
            $count = $group->count();
            $slotMachineName = $group->first()->slotMachine->name;
            
            return [
                'slot_number' => $slotNumber,
                'count' => $count,
                'slot_machine_name' => $slotMachineName,
            ];
        })->sortBy('slot_number')->sortByDesc('count')->values()->all();
    }

    public function getAllDateData($hallData)
    {
        $allDateData = [];

        foreach ($hallData as $data) {
            $slotNumber = $data['slot_number'];

            $allDateData[$slotNumber][$data['date']]['name'] = $data['slotMachine']['name'];
            $allDateData[$slotNumber][$data['date']]['game_count'] = $data['game_count'];
            $allDateData[$slotNumber][$data['date']]['difference_coins'] = $data['difference_coins'];
            $allDateData[$slotNumber][$data['date']]['is_high_setting'] = $data['is_high_setting'];
        }

        return $allDateData;
    }
}
