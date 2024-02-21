<?php

namespace App\Services;
use App\Models\HallData;

class HallDataService
{
    // TODO:Enumなどで管理する
    CONST COINS_PER_SPIN = 3;

    public function fetchHallData($hallId, $period = null) {
        return HallData::whereHallId($hallId)
            ->with('slotMachine')
            ->orderBy('date', 'desc')
            ->when($period, function($q, $period) {
                return $q->where('date', '>=', now()->subDays($period));
            })
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

    public function getMachineWinRates($hallData)
    {
        $machineWinRates = [];

        foreach ($hallData as $data) {
            $date = $data['date'];
            $machineName = $data['slotMachine']['name'];

            if (!isset($machineWinRates[$machineName][$date])) {
                $machineWinRates[$machineName][$date]['count'] = 0;
                $machineWinRates[$machineName][$date]['win_count'] = 0;
                $machineWinRates[$machineName][$date]['game_count'] = 0;
                $machineWinRates[$machineName][$date]['difference_coins'] = 0;
            } else {
                $machineWinRates[$machineName][$date]['count']++;
                $machineWinRates[$machineName][$date]['game_count'] += $data['game_count'];
                $machineWinRates[$machineName][$date]['difference_coins'] += $data['difference_coins'];
                
                if ($data['difference_coins'] > 0) {
                    $machineWinRates[$machineName][$date]['win_count']++;
                }
            }
        }

        foreach ($machineWinRates as $machineName => $values) {
            foreach ($values as $key => $value) {
                if ($value['game_count'] > 0) {
                    $machineWinRates[$machineName][$key]['average_game_count'] = floor($value['game_count'] / $value['count']);
                    $machineWinRates[$machineName][$key]['average_difference_coins'] = floor($value['difference_coins'] / $value['count']);
                    $machineWinRates[$machineName][$key]['average_kikaiwari'] = number_format((($value['game_count'] * self::COINS_PER_SPIN + $value['difference_coins']) / ($value['game_count'] * self::COINS_PER_SPIN) * 100), 2);
                } else {
                    $machineWinRates[$machineName][$key]['average_game_count'] = 0;
                    $machineWinRates[$machineName][$key]['average_difference_coins'] = 0;
                    $machineWinRates[$machineName][$key]['average_kikaiwari'] = 0;
                }
            }
        }

        return $machineWinRates;
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
}
