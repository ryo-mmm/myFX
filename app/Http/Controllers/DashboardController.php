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

        // 2. チャート用データの取得（直近24時間）
        $usdjpyChartData = MarketData::where('symbol', 'USDJPY')->where('measured_at', '>=', now()->subDay())->orderBy('measured_at', 'asc')->get();
        $us10yChartData = MarketData::where('symbol', 'US10Y')->where('measured_at', '>=', now()->subDay())->orderBy('measured_at', 'asc')->get();
        $vixData = MarketData::where('symbol', 'VIX')->where('measured_at', '>=', now()->subDay())->orderBy('measured_at', 'asc')->get();

        // 3. ラベルの加工（DBの時刻に強制的に9時間プラスする）
        $chartLabels = $usdjpyChartData->map(function ($data) {
            return Carbon::parse($data->measured_at)->addHours(9)->format('H:i');
        });

        $usdjpyValues = $usdjpyChartData->pluck('close');
        $us10yValues = $us10yChartData->pluck('close');

        $vixLabels = $vixData->map(function ($data) {
            return Carbon::parse($data->measured_at)->addHours(9)->format('H:i');
        });
        $vixValues = $vixData->pluck('close');

        // 4. 履歴テーブル用（VIXの最新20件）
        $historyVix = MarketData::where('symbol', 'VIX')->orderBy('measured_at', 'desc')->limit(20)->get();

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
