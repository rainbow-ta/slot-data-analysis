<?php

namespace App\Services;
use App\Models\HallData;

class HallDataService
{
    // TODO:Enumなどで管理する
    CONST COINS_PER_SPIN = 3;

    private $dataType;

    public function __construct($dataType = 'all')
    {
        $this->dataType = $dataType;
    }

    public function fetchHallData($hallId, $startDate, $endDate, $selectedDates, $slotMachineNames) {
        return HallData::where('hall_id', $hallId)
            ->with('slotMachine')
            ->when(!empty($slotMachineNames), function ($query) use ($slotMachineNames) {
                return $query->whereHas('slotMachine', function ($subQuery) use ($slotMachineNames) {
                    foreach ($slotMachineNames as $index => $slotMachineName) {
                        if ($index === 0) {
                            $subQuery->where('name', 'LIKE', "%$slotMachineName%");
                        } else {
                            $subQuery->orWhere('name', 'LIKE', "%$slotMachineName%");
                        }
                    }
                });
            })
            ->when($this->dataType === 'event', function ($query) use($hallId) {
                return $query->whereIn('date', function ($query) use ($hallId) {
                    $query->select('date')
                        ->from('hall_data')
                        ->where('hall_id', $hallId)
                        ->whereIsHighSetting(1)
                        ->groupBy('date');
                });
            })
            ->when($this->dataType === 'all' && !empty($selectedDates), function ($query) use ($selectedDates) {
                return $query->whereIn('date', $selectedDates);
            })
            ->when($this->dataType === 'all' && empty($selectedDates), function ($query) use ($startDate, $endDate) {
                return $query->whereBetween('date', [$startDate, $endDate]);
            })
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

    public function calculateHighSettingMachines($hallData)
    {
        $highSettingMachines = [];

        foreach ($hallData as $data) {
            $date = $data['date'];
            $machineName = $data['slotMachine']['name'];

            if (!isset($highSettingMachines[$machineName][$date])) {
                $highSettingMachines[$machineName][$date]['count'] = 0;
                $highSettingMachines[$machineName][$date]['high_setting_count'] = 0;
            }

            $highSettingMachines[$machineName][$date]['count']++;
            if ($this->dataType === 'event') {
                if ($data['is_high_setting']) {
                    $highSettingMachines[$machineName][$date]['high_setting_count']++;
                }
            } else {
                if ($data['is_high_setting'] || $data['is_predicted_high_setting']) {
                    $highSettingMachines[$machineName][$date]['high_setting_count']++;
                }
            }
        }

        foreach ($highSettingMachines as $machineName => &$gameData) {
            $total = 0;

            foreach ($gameData as $date => $data) {
                if ($this->dataType === 'event') {
                    if (isset($data['high_setting_count']) && $data['high_setting_count'] !== 0) {
                        $total++;
                    }
                } else {
                    if ((isset($data['high_setting_count']) && $data['high_setting_count'] !== 0) ||
                        (isset($data['is_predicted_high_setting']) && $data['is_predicted_high_setting'] !== 0)
                    ) {
                        $total++;
                    }
                }
            }

            $gameData['total'] = $total;
        }

        uasort($highSettingMachines, function ($a, $b) {
            return $b['total'] - $a['total'];
        });

        return $highSettingMachines;
    }

    /**
     * ホールデータを基に高設定の台番号データを返却する
     *
     * @param \Illuminate\Support\Collection $hallData
     *
     * @return array
     */
    public function calculateHighSettingSlotNumbers($hallData)
    {
        return $hallData->when($this->dataType === 'event', function ($query) {
                return $query->filter(function ($item) {
                    return $item->is_high_setting == 1;
                });
            }, function ($query) {
                return $query->filter(function ($item) {
                    return $item->is_high_setting == 1 || $item->is_predicted_high_setting == 1;
                });
            })
            ->groupBy('slot_number')
            ->map(function ($group) {
                $count = $group->count();
                $sumGameCount = $group->sum('game_count');
                $sumDifferenceCoins = $group->sum('difference_coins');
    
                $averageGameCount = 0;
                $averageRtp = 0;
                if ($sumGameCount) {
                    $averageGameCount = floor($sumGameCount / $count);
                    $averageRtp = number_format((($sumGameCount * self::COINS_PER_SPIN + $sumDifferenceCoins) / ($sumGameCount * self::COINS_PER_SPIN) * 100), 2);
                }
    
                return [
                    'slot_number' => $group->first()['slot_number'],
                    'count' => $count,
                    'average_game_count' => $averageGameCount,
                    'average_rtp' => $averageRtp,
                    'slot_machine_name' => $group->first()->slotMachine->name,
                ];
            })->sortBy('slot_number')->sortByDesc('average_rtp')->sortByDesc('count')->values()->all();
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
