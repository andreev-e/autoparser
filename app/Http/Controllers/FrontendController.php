<?php

namespace App\Http\Controllers;

use App\Models\RawItem;

class FrontendController extends Controller
{
    public function index()
    {
        $last = RawItem::query()->latest()->first()->external_id;
        $favorite = [
            '81723431',
            '79889665',
            '81559259',
            $last
        ];

        $favorites = (new RawItem)->onlyBasic()->whereIn('external_id', $favorite)->get();
        $stats = [
            'favorites' => count($favorites),
            'total_cars' => (new RawItem)->onlyBasic()->count(),
            'changes' => (new RawItem)->onlyBasic(false)->count(),
        ];

        $fields = [
            'customs_passed', 'for_rent', 'active_ads'
        ];

        return view('welcome', compact('stats', 'favorites', 'fields'));
    }
}
