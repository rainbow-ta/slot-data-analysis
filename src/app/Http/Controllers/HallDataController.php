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
    public function index()
    {
        return Inertia::render('HallData/Index', [
            'halls' => Hall::get(),
        ]);
    }

    public function event($hallId)
    {
        $hallDataService = new HallDataService();
        $eventHallDataService = new EventHallDataService();
        $highSettingService = new HighSettingService();

        $hallData = $eventHallDataService->getDataWithEventDate($hallId);

        return Inertia::render('HallData/Event', [
            'hallName' => Hall::whereId($hallId)->pluck('name')->first(),
            'allDate' => $hallData->unique('date')->pluck('date'),
            'highSettingMachines' => $highSettingService->getHighSettingMachines($hallData),
            'uniqueDateCount' => $hallData->unique('date')->count(),
            'highSettingSlotNumbers' => $highSettingService->calculateHighSettingSlotNumbers($hallData),
            'allDateData' => $hallDataService->getAllDateData($hallData),
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
