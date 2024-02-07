<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\DomCrawler\Crawler;
use App\Models\Store;
use App\Models\SlotsData;
use App\Models\SlotMachine;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\ConsoleOutput;

class ScrapeAnaslot extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:scrape-anaslot';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scrapes Ana Slot data for the past 3 months';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $output = new ConsoleOutput();
        $stores = Store::select('id', 'ana_slot_url_name')->get();

        if ($stores->isEmpty()) {
            $this->info('No stores found.');
            return;
        }

        $today = now();
        $threeMonthsAgo = now()->subMonths(3);
        $currentDate = clone $threeMonthsAgo;

        // プログレスバー開始
        $totalDays = $threeMonthsAgo->diff($today)->days;
        $progressBar = new ProgressBar($output, $totalDays * count($stores));
        $progressBar->start();

        // 直近3ヶ月のデータを取得する
        while ($currentDate <= $today) {
            foreach ($stores as $store) {
                $formattedCurrentDate = $currentDate->format('Y-m-d');
                $this->processSlotsData($store, $formattedCurrentDate);

                $progressBar->advance();
            }

            $currentDate->addDay();
        }

        // プログレスバー終了
        $progressBar->finish();
        $output->writeln("\n");
        $this->info('Scraping completed successfully.');
    }

    private function processSlotsData($store, $formattedCurrentDate)
    {
        $slotsData = SlotsData::where('date', $formattedCurrentDate)
            ->where('stores_id', $store->id)
            ->first();

        if ($slotsData) {
            return;
        }

        $dataArray = $this->scrape($store, $formattedCurrentDate);

        if ($dataArray) {
            $this->insertSlotsData($dataArray);
        }
    }

    private function scrape($store, $formattedDate)
    {
        // サイトの負荷を軽減するために待機時間を設定
        $sleepTime = rand(1, 5);
        sleep($sleepTime);

        $client = new \GuzzleHttp\Client();
        $dataArray = [];

        $url = "https://ana-slo.com/{$formattedDate}-{$store->ana_slot_url_name}-data/";
        $response = $client->request('GET', $url);
        $html = $response->getBody()->getContents();
        $crawler = new Crawler($html);

        $tbodyFirst = $crawler->filter('tbody')->first();

        if ($tbodyFirst->filter('th')->count() > 0) {
            $tbody = new Crawler($crawler->filter('tbody')->eq(1)->html());
        } else {
            $tbody = new Crawler($tbodyFirst->html());
        }

        $tbody->filter('tr')->each(function (Crawler $row) use (&$dataArray, $formattedDate, $store) {
            $rowData = [];

            $row->filter('td')->each(function (Crawler $cell) use (&$rowData) {
                $cellText = str_replace('1/', '', str_replace(',', '', $cell->text()));
                $rowData[] = $cellText;
            });

            $slotMachine = SlotMachine::firstOrCreate(['name' => $rowData[0]]);


            $countRowData = count($rowData);

            // 店舗によってデータ構造が異なるため、要素数で判定して取得する
            if ($countRowData === 9) {
                $dataArray[] = [
                    'slot_machines_id' => $slotMachine->id,
                    'slot_number' => $rowData[1] ?? null,
                    'game_count' => $rowData[2] ?? null,
                    'difference_coins' => $rowData[3] ?? null,
                    'big_bonus_count' => $rowData[4] ?? null,
                    'regular_bonus_count' => $rowData[5] ?? null,
                    'synthesis_probability' => $rowData[6] ?? null,
                    'big_bonus_probability' => $rowData[7] ?? null,
                    'regular_bonus_probability' => $rowData[8] ?? null,
                    'date' => $formattedDate,
                    'stores_id' => $store->id,
                ];
            } else {
                $dataArray[] = [
                    'slot_machines_id' => $slotMachine->id,
                    'slot_number' => $rowData[1] ?? null,
                    'game_count' => $rowData[2] ?? null,
                    'difference_coins' => $rowData[3] ?? null,
                    'big_bonus_count' => $rowData[4] ?? null,
                    'regular_bonus_count' => $rowData[5] ?? null,
                    'art_count' => $rowData[6] ?? null,
                    'synthesis_probability' => $rowData[7] ?? null,
                    'big_bonus_probability' => $rowData[8] ?? null,
                    'regular_bonus_probability' => $rowData[9] ?? null,
                    'art_probability' => $rowData[10] ?? null,
                    'date' => $formattedDate,
                    'stores_id' => $store->id,
                ];
            }
        });

        return $dataArray;
    }

    private function insertSlotsData($dataArray)
    {
        DB::beginTransaction();

        try {
            SlotsData::insert($dataArray);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            $this->error('Error inserting data: ' . $e->getMessage());
        }
    }
}
