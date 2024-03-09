<?php

namespace App\Services;
use App\Models\HallData;
use App\Models\SlotMachine;

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
            }

            $machineWinRates[$machineName][$date]['count']++;
            $machineWinRates[$machineName][$date]['game_count'] += $data['game_count'];
            $machineWinRates[$machineName][$date]['difference_coins'] += $data['difference_coins'];

            if ($data['difference_coins'] > 0) {
                $machineWinRates[$machineName][$date]['win_count']++;
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

    /**
     * 高設定らしきデータの台番号を返却する
     * @param collection $hallData
     * @param collection $differenceCoinsBySlotMachines
     * @param collection $sortSumDifferenceCoins
     * @return array
     */
    public function getPredictionHighSettingNumbers($hallData, $differenceCoinsBySlotMachines, $sortSumDifferenceCoins)
    {
        // 差枚が20000枚以上の機種
        $keysOver20000 = [];
        foreach ($differenceCoinsBySlotMachines as $key => $value) {
            if ($value >= 20000) {
                $keysOver20000[] = $key;
            }
        }
        $slotMachineIds = SlotMachine::whereIn('name', $keysOver20000)->pluck('id');

        // 差枚が20000枚以上の台番号
        $filtered = $sortSumDifferenceCoins->filter(function ($item) {
            return $item['sum_difference_coins'] >= 20000;
        });
        $slotNumbers = $filtered->pluck('slot_number');

        return $hallData->whereIn('slot_machines_id', $slotMachineIds)
            ->whereIn('slot_number', $slotNumbers)
            ->pluck('slot_number')
            ->unique();
    }

    /**
     * 高設定の台番号を返却する
     * @param collection $predictionHighSettingNumbers
     * @param collection $hallData
     * @return array
     */
    public function getHighSettingTotals($predictionHighSettingNumbers, $hallData)
    {
        $maxCount = $predictionHighSettingNumbers->count();
        $filteredHallData = $hallData->filter(function ($item) {
            return $item->is_high_setting == 1;
        });

        $highSettingTotals = [];
        foreach ($filteredHallData as $data) {
            $date = $data['date'];

            if (!isset($highSettingTotals[$date])) {
                $highSettingTotals[$date]['win_count'] = 0;
            } else {
                if ($predictionHighSettingNumbers->contains($data['slot_number'])) {
                    $highSettingTotals[$date]['win_count']++;
                }
            }
        }

        foreach ($highSettingTotals as $key => $val) {
            $percentage = 0;
            if ($val['win_count']) {
                $percentage = round(($val['win_count'] / $maxCount) * 100, 1);
            }
            $highSettingTotals[$key]['percentage'] = $percentage . '%';
            $highSettingTotals[$key]['count'] = $maxCount;
        }

        return $highSettingTotals;
    }
}
