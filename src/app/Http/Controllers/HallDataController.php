<?php

namespace App\Http\Controllers;

use App\Exceptions\HallDataNotFoundException;
use App\Http\Requests\HallData\IndexRequest;
use App\Http\Requests\HallData\UpdateRequest;
use App\Http\Resources\HallDataResource;
use App\UseCases\HallData\IndexAction;
use App\UseCases\HallData\UpdateAction;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Hall;
use App\Services\HallDataService;
use App\Services\EventHallDataService;
use App\Services\MonthlyHallDataService;
use App\Services\HighSettingService;
use Carbon\Carbon;

class HallDataController extends Controller
{
    public function index(IndexRequest $request, IndexAction $action, $hallId)
    {
        $validated = $request->validated();

        $date = $validated['date'] ?? Carbon::today()->format('Y-m-d');

        return Inertia::render('HallData/Index', [
            'hallCollection' => new HallDataResource($action($hallId, $date), $date),
        ]);
    }

    public function update(UpdateRequest $request, UpdateAction $action, $hallId, $hallDataId)
    {
        $validated = $request->validated();

        try {
            $action($hallDataId, $validated['is_high_setting']);

            return redirect()->route('hall-data.index', ['id' => $hallId, 'date' => $validated['date']]);
        } catch (HallDataNotFoundException $e) {
            return abort(404, $e->getMessage());
        } catch (\Exception $e) {
            return abort(500);
        }
    }

    public function event($hallId)
    {
        $hallDataService = new HallDataService();
        $eventHallDataService = new EventHallDataService();
        $highSettingService = new HighSettingService();

        $hallData = $eventHallDataService->getDataWithEventDate($hallId);

        $monthlyHallDataService = new MonthlyHallDataService($hallId, 12);
        $slotMachineCountsByDate = $monthlyHallDataService->calculateSlotMachineCountsByDate();

        return Inertia::render('HallData/Event', [
            'hallName' => Hall::whereId($hallId)->pluck('name')->first(),
            'allDate' => $hallData->unique('date')->pluck('date'),
            'highSettingMachines' => $highSettingService->calculateHighSettingMachines($hallData),
            'uniqueDateCount' => $hallData->unique('date')->count(),
            'highSettingSlotNumbers' => $highSettingService->calculateHighSettingSlotNumbers($hallData),
            'allDateData' => $hallDataService->getAllDateData($hallData),
            'slotMachineCountsByDate' => $slotMachineCountsByDate,
        ]);
    }

    public function detail(Request $request)
    {
        $monthlyHallDataService = new MonthlyHallDataService($request->id, 3);
        $slotMachineCountsByDate = $monthlyHallDataService->calculateSlotMachineCountsByDate(true);

        $hall = Hall::whereId($request->id)->first();

        $startDate = $request->startDate ?? now()->subDays(14)->startOfDay()->toDateString();
        $endDate = $request->endDate ?? now()->endOfDay()->toDateString();

        $hallDataService = new HallDataService();
        $hallData = $hallDataService->fetchHallData($request->id, $startDate, $endDate);

        $matstubiArray = $hallDataService->matsubiCount($hallData);
        $matsubiTotals = $hallDataService->matsubiTotals($matstubiArray);

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
            'slotMachineCountsByDate' => $slotMachineCountsByDate,
        ]);
    }
}
