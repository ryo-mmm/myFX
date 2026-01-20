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

        // 1. ドル円 24時間データ
        $usdjpyChartData = MarketData::where('symbol', 'USDJPY')
            ->where('measured_at', '>=', now()->subDay())
            ->orderBy('measured_at', 'asc')
            ->get();

        // 2. 米10年債 24時間データ (比較グラフ用)
        $us10yChartData = MarketData::where('symbol', 'US10Y')
            ->where('measured_at', '>=', now()->subDay())
            ->orderBy('measured_at', 'asc')
            ->get();

        // グラフで使いやすいように加工
        $chartLabels = $usdjpyChartData->pluck('measured_at')->map(fn($d) => $d->format('H:i'));
        $usdjpyValues = $usdjpyChartData->pluck('close');
        $us10yValues = $us10yChartData->pluck('close');

        // --- 既存のVIXデータ取得はそのまま ---
        $historyVix = MarketData::where('symbol', 'VIX')->orderBy('measured_at', 'desc')->limit(20)->get();
        $vixData = MarketData::where('symbol', 'VIX')->where('measured_at', '>=', now()->subDay())->orderBy('measured_at', 'asc')->get();
        $vixLabels = $vixData->pluck('measured_at')->map(fn($d) => $d->format('H:i'));
        $vixValues = $vixData->pluck('close');

        return view('dashboard', compact(
            'userName',
            'usdjpy',
            'latestData',
            'historyVix',
            'vixLabels',
            'vixValues',
            'chartLabels',
            'usdjpyValues',
            'us10yValues'
        ));
    }
}
