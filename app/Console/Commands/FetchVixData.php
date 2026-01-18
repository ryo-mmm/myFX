<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\MarketData;
use Illuminate\Support\Facades\Log;

class FetchVixData extends Command
{
    // コマンド名を定義（ここが空だとさっきのエラーになります）
    protected $signature = 'app:fetch-vix';
    protected $description = 'Yahoo FinanceからVIX指数を取得してDBに保存します';

    public function handle()
    {
        $this->info('Google FinanceからVIXデータを取得中...');

        try {
            // Google FinanceのVIXページ（INDEXCBOE: VIX）
            $url = "https://www.google.com/finance/quote/VIX:INDEXCBOE";

            // ブラウザからのアクセスを装うためのUser-Agentを設定
            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
            ])->get($url);

            if (!$response->successful()) {
                throw new \Exception("Google Financeにアクセスできませんでした。");
            }

            $html = $response->body();

            // 正規表現で「data-last-price」という属性に隠されている価格を抽出
            // Google FinanceのHTML構造に基づいた抽出方法です
            if (preg_match('/data-last-price="([\d\.]+)"/', $html, $matches)) {
                $currentPrice = (float)$matches[1];

                \App\Models\MarketData::create([
                    'symbol' => 'VIX',
                    'open'   => 0, // スクレイピングでは現在値のみ取得
                    'high'   => 0,
                    'low'    => 0,
                    'close'  => $currentPrice,
                    'volume' => 0,
                    'measured_at' => now(),
                ]);

                $this->info("取得成功: VIX = {$currentPrice}");
            } else {
                throw new \Exception("HTMLから価格データを見つけられませんでした。");
            }
        } catch (\Exception $e) {
            $this->error("エラーが発生しました: " . $e->getMessage());
        }
    }
}
