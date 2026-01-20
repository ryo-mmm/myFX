<?php

namespace App\Http\Controllers;

use App\Models\MarketData;
use App\Models\AnalysisResult;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $userName = $request->user()->name;

        // ドル円 (メイン指標)
        $usdjpy = MarketData::where('symbol', 'USDJPY')
            ->orderBy('measured_at', 'desc')
            ->first();

        $symbols = ['VIX', 'US10Y', 'DXY', 'SP500'];
        $latestData = [];
        foreach ($symbols as $symbol) {
            $latestData[$symbol] = MarketData::where('symbol', $symbol)
                ->orderBy('measured_at', 'desc')
                ->first();
        }

        // VIX履歴とグラフ用データ (変更なし)
        $historyVix = MarketData::where('symbol', 'VIX')->orderBy('measured_at', 'desc')->limit(20)->get();
        $vixData = MarketData::where('symbol', 'VIX')->where('measured_at', '>=', now()->subDay())->orderBy('measured_at', 'asc')->get();
        $vixLabels = $vixData->pluck('measured_at')->map(fn($d) => $d->format('H:i'));
        $vixValues = $vixData->pluck('close');

        $usdjpyData = MarketData::where('symbol', 'USDJPY')
            ->where('measured_at', '>=', now()->subDay())
            ->orderBy('measured_at', 'asc')
            ->get();

        $usdjpyLabels = $usdjpyData->pluck('measured_at')->map(fn($d) => $d->format('H:i'));
        $usdjpyValues = $usdjpyData->pluck('close');

        return view('dashboard', compact('userName', 'usdjpy', 'latestData', 'historyVix', 'vixLabels', 'vixValues', 'usdjpyLabels', 'usdjpyValues'));
    }
}
