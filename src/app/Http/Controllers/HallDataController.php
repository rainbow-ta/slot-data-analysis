<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Hall;
use App\Services\HallDataService;
use App\Services\EventHallDataService;
use App\Services\HighSettingService;

class HallDataController extends Controller
{
    public function event($hallId)
    {
        $hallDataService = new HallDataService();
        $eventHallDataService = new EventHallDataService();
        $highSettingService = new HighSettingService();

        $hallData = $eventHallDataService->getDataWithEventDate($hallId);

        return Inertia::render('HallData/Event', [
            'hallName' => Hall::whereId($hallId)->pluck('name')->first(),
            'allDate' => $hallData->unique('date')->pluck('date'),
            'highSettingMachines' => $highSettingService->calculateHighSettingMachines($hallData),
            'uniqueDateCount' => $hallData->unique('date')->count(),
            'highSettingSlotNumbers' => $highSettingService->calculateHighSettingSlotNumbers($hallData),
            'allDateData' => $hallDataService->getAllDateData($hallData),
        ]);
    }

    public function detail(Request $request)
    {
        $hall = Hall::whereId($request->id)->first();

        $startDate = $request->startDate ?? now()->subDays(14)->startOfDay()->toDateString();
        $endDate = $request->endDate ?? now()->endOfDay()->toDateString();

        $hallDataService = new HallDataService();
        $hallData = $hallDataService->fetchHallData($request->id, $startDate, $endDate);

        $matstubiArray = $hallDataService->matsubiCount($hallData);
        $matsubiTotals = $hallDataService->matsubiTotals($matstubiArray);

        // 高設定データを取得
        $differenceCoinsBySlotMachines = $hallDataService->getDifferenceCoinsBySlotMachines($hallData);
        $sumDifferenceCoins = $hallDataService->getSumDifferenceCoins($hallData);
        $highSettingNumbers = $hallDataService->getPredictionHighSettingNumbers($hallData, $differenceCoinsBySlotMachines, $sumDifferenceCoins);
        $highSettingTotals = $hallDataService->getHighSettingTotals($highSettingNumbers, $hallData);

        $allDateData = $hallDataService->getAllDateData($hallData);

        return Inertia::render('HallData/Detail', [
            'hall' => $hall,
            'matsubiArray' => $matstubiArray,
            'matsubiTotals' => $matsubiTotals,
            'highSettingNumbers' => $hallDataService->highSettingNumbersCount($hallData),
            'allDate' => $hallData->unique('date')->pluck('date'),
            'machineWinRates' => $hallDataService->getMachineWinRates($hallData),
            'allDateData' => $allDateData,
            'slumpSlotNumbers' => $hallDataService->calculateSlumpSlotNumbers($allDateData),
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);
    }
}
