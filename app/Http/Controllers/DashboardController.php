<?php

namespace App\Http\Controllers;

use App\Models\MarketData;
use App\Models\AnalysisResult;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * ダッシュボードのメイン画面を表示
     */
    public function index()
    {
        // 1. 最新のVIX指数を取得
        $latestVix = MarketData::where('symbol', 'VIX')
            ->orderBy('measured_at', 'desc')
            ->first();

        // 2. 最新の分析結果を取得（AI予測スコア用）
        $latestAnalysis = AnalysisResult::orderBy('created_at', 'desc')->first();

        // 3. グラフ表示用に過去24時間分のVIXデータを取得
        $vixData = MarketData::where('symbol', 'VIX')
            ->where('measured_at', '>=', now()->subDay())
            ->orderBy('measured_at', 'asc') // チャートは古い順から並べる
            ->get();

        // グラフ描画用に、時間(HH:mm)と数値だけの配列を作る
        $vixLabels = $vixData->pluck('measured_at')->map(fn($d) => $d->format('H:i'));
        $vixValues = $vixData->pluck('close');

        // ビューにすべてのデータを渡す
        return view('dashboard', [
            'latestVix'    => $latestVix,
            'latestAnalysis' => $latestAnalysis,
            'vixLabels'    => $vixLabels,
            'vixValues'    => $vixValues,
        ]);
    }
}
