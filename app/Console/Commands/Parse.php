<?php

namespace App\Console\Commands;

use App\Models\RawItem;
use App\Models\Source;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class Parse extends Command
{
    protected $signature = 'parse:all';

    protected $description = 'Command description';

    protected int $pages = 10;

    protected array $locations_ge = [2, 3, 4, 7, 15, 30, 113, 52, 37, 36, 38, 39, 40, 31, 5, 41, 44, 47, 48, 53, 54, 8, 16, 6, 14, 13, 12, 11, 10, 9, 55, 56, 57, 59, 58, 61, 62, 63, 64, 66, 71, 72, 74, 75, 76, 77, 78, 80, 81, 82, 83, 84, 85, 86, 87, 88, 91, 96, 97, 101, 109];

    public function handle()
    {
        $source = Source::query()->oldest()->first();
        $client = new \GuzzleHttp\Client();

//        foreach (RawItem::query()->whereNotIn('data->location_id', $this->locations_ge)->get() as $item) {
//            RawItem::query()->where('external_id', $item->external_id)->delete();
//        }

        //https://api2.myauto.ge/appdata/other_en.json

        for ($i = 0; $i < $this->pages; $i++) {
            $response = $client->request('GET', $source->base_url, ['query' => [
                'Page' => $source->page,
                'Period' => '3w',
                'Locs' => implode('.', $this->locations_ge),
            ]]);

            $statusCode = $response->getStatusCode();

            if ($statusCode === 200) {

                DB::beginTransaction();
                $content = json_decode($response->getBody(), true);
                foreach ($content['data']['items'] as $item) {
                    foreach ($item as $key => $itemDatum) {
                        $item[$key] = is_array($itemDatum) ? json_encode($itemDatum) : $itemDatum;
                    }

                    $found = RawItem::getState($source->id, $item['car_id']);
                    if ($found) {
                        $fullDiff = array_merge(array_diff($found->data, $item), array_diff($item, $found->data));
                        if (count($fullDiff) > 0) {
                            RawItem::query()->create(
                                [
                                    'source_id' => $source->id,
                                    'external_id' => $item['car_id'],
                                    'data' => $fullDiff,
                                ]);
                            $this->info('Update');
                        } else {
                            $this->info('No diff');
                        }
                    } else {
                        RawItem::query()->create(
                            [
                                'source_id' => $source->id,
                                'external_id' => $item['car_id'],
                                'data' => $item,
                                'is_basic' => true,
                            ]);
                    }
                }
                $source->page = $source->page + 1;
                if ($content['data']['meta']['current_page'] >= $content['data']['meta']['last_page']) {
                    $source->page = 1;
                }
                $source->save();
                DB::commit();
                $this->info($source->name . ' p.' . $source->page - 1);

            } else {
                $this->error('HTTP ' . $statusCode);
            }
        }
        return Command::SUCCESS;
    }
}
