<?php

namespace App\Http\Controllers;

use App\Models\RawItem;

class FrontendController extends Controller
{
    public function index()
    {
        $favorite = [
            '81723431',
            '79889665',
        ];
        $favorites = RawItem::query()->whereIn('external_id', $favorite)->get();
        $stats = [
            'favorites' => count($favorites),
            'total_cars' => (new RawItem)->onlyBasic()->count(),
            'changes' => (new RawItem)->onlyBasic(false)->count(),
        ];

        return view('welcome', compact('stats', 'favorites'));
    }
}
