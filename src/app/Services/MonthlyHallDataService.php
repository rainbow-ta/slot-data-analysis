<?php

namespace App\Services;

use App\Models\HallData;
use Carbon\Carbon;

class MonthlyHallDataService
{
    private $hallDataCollection;

    public function __construct($hallId, $months)
    {
        $this->hallDataCollection = $this->fetchHallData($hallId, $months);
    }

    private function fetchHallData($hallId, $months)
    {
        $currentDate = Carbon::now();
        $threeMonthsAgo = $currentDate->subMonths($months);

        return HallData::with('slotMachine')
            ->where('hall_id', $hallId)
            ->where('date', '>=', $threeMonthsAgo)
            ->orderBy('date', 'desc')
            ->get();
    }

    public function calculateSlotMachineCountsByDate($isPredictedHighSetting = false)
    {
        $slotMachineCountsByDate = [];

        foreach ($this->hallDataCollection as $hallData) {
            $date = Carbon::parse($hallData['date'])->format('Y/m');
            $slotMachineName = $hallData['slotMachine']['name'];
            $slotNumber = $hallData['slot_number'];
            if ($isPredictedHighSetting) {
                $isCount = $hallData['is_high_setting'] || $hallData['is_predicted_high_setting'];
            } else {
                $isCount = $hallData['is_high_setting'];
            }
            
            // 機種名がまだ配列に存在しない場合は、空の配列を初期化する
            if (!isset($slotMachineCountsByDate[$slotMachineName])) {
                $slotMachineCountsByDate[$slotMachineName] = [];
                $slotMachineCountsByDate[$slotMachineName]['合計'] = [];
            }

            // 日付がまだ配列に存在しない場合は、空の配列を初期化する
            if (!isset($slotMachineCountsByDate[$slotMachineName][$date])) {
                $slotMachineCountsByDate[$slotMachineName][$date] = [];
            }

            // 台番号をキーとしてカウントを増やす
            if (!isset($slotMachineCountsByDate[$slotMachineName][$date][$slotNumber])) {
                $slotMachineCountsByDate[$slotMachineName][$date][$slotNumber] = 0;
            }

            if ($isCount) {
                if (!isset($slotMachineCountsByDate[$slotMachineName]['合計'][$slotNumber])) {
                    $slotMachineCountsByDate[$slotMachineName]['合計'][$slotNumber]['count'] = 0;
                }

                $slotMachineCountsByDate[$slotMachineName][$date][$slotNumber]++;
                $slotMachineCountsByDate[$slotMachineName]['合計'][$slotNumber]['count']++;
            }
        }

        foreach ($slotMachineCountsByDate as $slotMachineName => $countsByDate) {
            // 最新の日付を取得する
            $newestDate = collect($countsByDate)->keys()->sortByDesc(function ($date) {
                return $date;
            })->first();

            // 最新の日付をキーとして、台番号とカウントのペアを取得し、slot_numberに格納する
            $newestDateKeys = array_keys($slotMachineCountsByDate[$slotMachineName][$newestDate]);

            sort($newestDateKeys);

            $slotMachineCountsByDate[$slotMachineName]['slot_number'] = $newestDateKeys;
        }

        // 上位5番目までの台番号にis_top5フラグを設定する
        foreach ($slotMachineCountsByDate as &$countsBySlotMachine) {
            $sortedCounts = collect($countsBySlotMachine['合計'])->pluck('count')->sort()->reverse()->values()->toArray();
            $top5Counts = array_slice($sortedCounts, 0, 5);
            foreach ($countsBySlotMachine['合計'] as $slotNumber => &$countData) {
                $isTop5 = in_array($countData['count'], $top5Counts);
                $countData['is_top5'] = $isTop5;
            }
        }

        return $slotMachineCountsByDate;
    }
}
