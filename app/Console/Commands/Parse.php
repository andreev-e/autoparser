<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class Parse extends Command
{
    protected $signature = 'parse:all';

    protected $description = 'Command description';

    public function handle()
    {
        $endpoint = "https://api2.myauto.ge/ka/products";
        $client = new \GuzzleHttp\Client();
        $id = 5;
        $value = "ABC";

        $response = $client->request('GET', $endpoint, ['query' => [
        ]]);

        $statusCode = $response->getStatusCode();
        $content = $response->getBody();


        $content = json_decode($response->getBody(), true);
        foreach ($content['data']['items'] as $item) {

        }
        dd($content) ;
        return Command::SUCCESS;
    }
}
