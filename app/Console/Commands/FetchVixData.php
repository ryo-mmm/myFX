<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ExternalApi\VixService;
use App\Models\MarketData;
use Illuminate\Support\Facades\Log;

class FetchVixData extends Command
{
    // ターミナルで実行する時のコマンド名
    protected $signature = 'app:fetch-vix';

    // コマンドの説明
    protected $description = 'Alpha VantageからVIX指数を取得して保存します';

    /**
     * コマンドの実行ロジック
     */
    public function handle(VixService $vixService)
    {
        $this->info('VIXデータの取得を開始します...');

        $data = $vixService->fetchVixIndex();

        if (!$data) {
            $this->error('データの取得に失敗しました。');
            return 1;
        }

        // APIのレスポンス形式に合わせてデータを抽出
        // ※Alpha VantageのVIXエンドポイントの構造に合わせる必要があります
        try {
            // 仮の構造: 最新の終値を抽出する例
            // 実際はAPIレスポンスのJSON構造をログで確認しながら調整します
            $vixValue = $data['Global Quote']['05. price'] ?? null;

            if ($vixValue) {
                MarketData::create([
                    'symbol' => 'VIX',
                    'open' => 0, // VIXは指数なのでcloseのみでも可
                    'high' => 0,
                    'low' => 0,
                    'close' => $vixValue,
                    'vix_index' => $vixValue,
                    'measured_at' => now(),
                ]);

                $this->info("VIX指数 {$vixValue} を保存しました。");
            } else {
                $this->warn('有効なVIX値が見つかりませんでした。API制限の可能性があります。');
                Log::warning('VIX API Response:', $data);
            }
        } catch (\Exception $e) {
            $this->error('保存中にエラーが発生しました: ' . $e->getMessage());
        }

        return 0;
    }
}
