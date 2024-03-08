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

    public function getHighSettingMachines($hallData)
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

        return $highSettingMachines;
    }
}
