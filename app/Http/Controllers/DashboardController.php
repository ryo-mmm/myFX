<?php

namespace App\Http\Controllers;

use App\Models\MarketData;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $userName = $request->user()->name;

        // 1. 最新データの取得
        $usdjpy = MarketData::where('symbol', 'USDJPY')->orderBy('measured_at', 'desc')->first();

        $symbols = ['VIX', 'US10Y', 'DXY', 'SP500'];
        $latestData = [];
        foreach ($symbols as $symbol) {
            $latestData[$symbol] = MarketData::where('symbol', $symbol)->orderBy('measured_at', 'desc')->first();
        }

        // 2. チャート用データの取得（直近24時間分）
        // テクニカル指標を表示するため、USDJPYはしっかり全カラム取得します
        $usdjpyChartData = MarketData::where('symbol', 'USDJPY')
            ->where('measured_at', '>=', now()->subDay())
            ->orderBy('measured_at', 'asc')
            ->get();

        $us10yChartData = MarketData::where('symbol', 'US10Y')
            ->where('measured_at', '>=', now()->subDay())
            ->orderBy('measured_at', 'asc')
            ->get();

        $vixData = MarketData::where('symbol', 'VIX')
            ->where('measured_at', '>=', now()->subDay())
            ->orderBy('measured_at', 'asc')
            ->get();

        // 3. ラベルの加工（9時間プラスして日本時間に）
        $chartLabels = $usdjpyChartData->map(function ($data) {
            return Carbon::parse($data->measured_at)->addHours(9)->format('H:i');
        });

        // --- ドル円テクニカルデータ抽出 ---
        $usdjpyValues = $usdjpyChartData->pluck('close');
        $ma25Values   = $usdjpyChartData->pluck('ma25');
        $ma75Values   = $usdjpyChartData->pluck('ma75');
        $ma200Values  = $usdjpyChartData->pluck('ma200');
        $rsiValues    = $usdjpyChartData->pluck('rsi');

        // --- VIX用ラベルと値 ---
        $vixLabels = $vixData->map(function ($data) {
            return Carbon::parse($data->measured_at)->addHours(9)->format('H:i');
        });
        $vixValues = $vixData->pluck('close');

        // 米10年債
        $us10yValues = $us10yChartData->pluck('close');

        // 4. 履歴テーブル用（VIXの最新20件）
        $historyVix = MarketData::where('symbol', 'VIX')
            ->orderBy('measured_at', 'desc')
            ->limit(10)
            ->get();

        // 全ての変数を compact で View に渡します
        return view('dashboard', compact(
            'userName',
            'usdjpy',
            'latestData',
            'historyVix',
            'vixLabels',
            'vixValues',
            'chartLabels',
            'usdjpyValues',
            'us10yValues',
            'ma25Values',
            'ma75Values',
            'ma200Values',
            'rsiValues'
        ));
    }
}
