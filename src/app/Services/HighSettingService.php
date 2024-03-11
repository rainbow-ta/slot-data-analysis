<?php

namespace App\Services;

use App\Models\HallData;
use App\Models\HallEvent;
use Illuminate\Support\Facades\DB;

class HighSettingService
{
    CONST COINS_PER_SPIN = 3;
    CONST HIGH_SETTING_RTP = 108;
    CONST REQUIRED_GAME_COUNT = 4000;

    public function isHighSetting($gameCount, $differenceCoins)
    {
        if ($gameCount === null || $differenceCoins === null) {
            return false;
        }

        if (substr($differenceCoins, 0, 1) === '-') {
            // 差枚がマイナス
            return false;
        }

        $gameCount = intval($gameCount);
        if ($gameCount < self::REQUIRED_GAME_COUNT ) {
            return false;
        }

        // 差枚が高設定の期待枚数以上かを判定
        $expectedCoins = $this->calculateExpectedCoins(self::HIGH_SETTING_RTP, $gameCount);
        $differenceCoins = intval($differenceCoins);
        if ($differenceCoins < $expectedCoins) {
            return false;
        }

        return true;
    }

    public function calculateExpectedCoins($rtp, $gameCount)
    {
        return ($gameCount * self::COINS_PER_SPIN * $rtp / 100) - ($gameCount * self::COINS_PER_SPIN);
    }

    public function updateHighSettingFromInterviewResults()
    {
        $hallEvents = HallEvent::get();
        $hallEvents->each(function ($item) {
            HallData::whereHallId($item->hall_id)
                ->whereDate('date', $item->date)
                ->update(['is_high_setting' => false]);
        });

        $path = 'database/.sql/update_hall_data_is_high_setting.sql';
        DB::unprepared(file_get_contents($path));
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
            if ($data['is_high_setting']) {
                $highSettingMachines[$machineName][$date]['high_setting_count']++;
            }
        }

        foreach ($highSettingMachines as $machineName => &$gameData) {
            $total = 0;

            foreach ($gameData as $date => $data) {
                if (isset($data['high_setting_count']) && $data['high_setting_count'] !== 0) {
                    $total++;
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
        return $hallData->where('is_high_setting', 1)->groupBy('slot_number')->map(function ($group) {
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
}
