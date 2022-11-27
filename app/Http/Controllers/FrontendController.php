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
            '77205105',
            '81847743',
            '81559259',
        ];
        $favorites = (new RawItem)->onlyBasic()->whereIn('external_id', $favorite)->get();
        $stats = [
            'favorites' => count($favorites),
            'total_cars' => (new RawItem)->onlyBasic()->count(),
            'changes' => (new RawItem)->onlyBasic(false)->count(),
        ];

        return view('welcome', compact('stats', 'favorites'));
    }
}
