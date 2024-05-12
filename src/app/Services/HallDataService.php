<?php

namespace App\Services;
use App\Models\HallData;

class HallDataService
{
    // TODO:Enumなどで管理する
    CONST COINS_PER_SPIN = 3;

    public function fetchHallData($hallId, $startDate, $endDate, $slotMachineName) {
        return HallData::where('hall_id', $hallId)
            ->with('slotMachine')
            ->when($slotMachineName, function ($query) use ($slotMachineName) {
                return $query->whereHas('slotMachine', function ($subQuery) use ($slotMachineName) {
                    $subQuery->where('name', 'LIKE', "%$slotMachineName%");
                });
            })
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date', 'desc')
            ->get();
    }

    public function matsubiCount($hallData)
    {
        $matsubiArray = [];

        foreach ($hallData as $data) {
            if ($data['is_high_setting'] === 1 || $data['is_predicted_high_setting'] === 1) {
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
        return $hallData->filter(function ($item) {
                return $item->is_high_setting == 1 || $item->is_predicted_high_setting == 1;
            })->groupBy('slot_number')
            ->map(function ($group) {
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
            $allDateData[$slotNumber][$data['date']]['big_bonus_count'] = $data['big_bonus_count'];
            $allDateData[$slotNumber][$data['date']]['regular_bonus_count'] = $data['regular_bonus_count'];
            $allDateData[$slotNumber][$data['date']]['art_count'] = $data['art_count'];
            $allDateData[$slotNumber][$data['date']]['synthesis_probability'] = $data['synthesis_probability'];
            $allDateData[$slotNumber][$data['date']]['big_bonus_probability'] = $data['big_bonus_probability'];
            $allDateData[$slotNumber][$data['date']]['regular_bonus_probability'] = $data['regular_bonus_probability'];
            $allDateData[$slotNumber][$data['date']]['art_probability'] = $data['art_probability'];
            $allDateData[$slotNumber][$data['date']]['is_high_setting'] = $data['is_high_setting'];
        }

        return $allDateData;
    }

    public function calculateSlumpSlotNumbers($allDateData)
    {
        $slumpSlotNumbers = [];
        $slotName = '';
        $reversedDateData = [];
        $total = 0;

        foreach ($allDateData as $slotNumber => $dateData) {
            if (empty($dateData)) {
                continue;
            }

            $firstKey = array_key_first($dateData);
            $slotName = $dateData[$firstKey]['name'];

            $reversedDateData = array_reverse($dateData);
            $yesterday = '';

            foreach ($reversedDateData as $date => $data) {
                if ($yesterday) {
                    $slumpSlotNumbers[$slotNumber][$date] = $slumpSlotNumbers[$slotNumber][$yesterday] + $data['difference_coins'];
                } else {
                    $slumpSlotNumbers[$slotNumber][$date] = $data['difference_coins'];
                }

                $total += $data['difference_coins'];
                $yesterday = $date;
            }

            $slumpSlotNumbers[$slotNumber]['slotName'] = $slotName;
            $slumpSlotNumbers[$slotNumber]['total'] = $total;
            $total = 0;
        }

        return $slumpSlotNumbers;
    }
}
