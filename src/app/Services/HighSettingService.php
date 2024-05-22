<?php

namespace App\Services;

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
}
