<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Hall;
use App\Services\HallDataService;

class HallDataController extends Controller
{
    public function index()
    {
        return Inertia::render('HallData/Index', [
            'halls' => Hall::get(),
        ]);
    }

    public function summary($hallId)
    {
        $hallDataService = new HallDataService();
        $hallData = $hallDataService->fetchHallData($hallId);
        $differenceCoinsBySlotMachines = $hallDataService->getDifferenceCoinsBySlotMachines($hallData);

        return Inertia::render('HallData/Summary', [
            'hallName' => Hall::whereId($hallId)->pluck('name')->first(),
            'differenceCoinsBySlotMachines' => $differenceCoinsBySlotMachines,
        ]);
    }

    public function detail($hallId)
    {
        $hallDataService = new HallDataService();
        $hallData = $hallDataService->fetchHallData($hallId, 30);  // TODO:期間を指定出来るようにする

        $matstubiArray = $hallDataService->matsubiCount($hallData);
        $matsubiTotals = $hallDataService->matsubiTotals($matstubiArray);

        return Inertia::render('HallData/Detail', [
            'hallName' => Hall::whereId($hallId)->pluck('name')->first(),
            'matsubiArray' => $matstubiArray,
            'matsubiTotals' => $matsubiTotals,
            'highSettingNumbers' => $hallDataService->highSettingNumbersCount($hallData),
            'allDate' => $hallData->unique('date')->pluck('date'),
            'machineWinRates' => $hallDataService->getMachineWinRates($hallData),
            'allDateData' => $hallDataService->getAllDateData($hallData),
        ]);
    }
}
