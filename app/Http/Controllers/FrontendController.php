<?php

namespace App\Http\Controllers;

use App\Models\RawItem;

class FrontendController extends Controller
{
    public function index()
    {
        $stats = [
            'total_cars' => RawItem::query()->where('is_basic', true)->count(),
            'changes' => RawItem::query()->where('is_basic', false)->count(),
        ];
        return view('welcome', compact('stats'));
    }
}
