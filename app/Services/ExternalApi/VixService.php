<?php

namespace App\Services\ExternalApi;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class VixService
{
    /**
     * VIX指数を取得する
     * 今回は例としてAlpha Vantage APIを想定した構造です
     */
    public function fetchVixIndex()
    {
        try {
            // .envに設定したAPIキーを使用してリクエストを送る
            $apiKey = config('services.alpha_vantage.key');
            $url = "https://www.alphavantage.co/query";

            $response = Http::get($url, [
                'function' => 'VIX', // 実際のエンドポイントに合わせて調整
                'apikey' => $apiKey
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            throw new \Exception("API request failed");
        } catch (\Exception $e) {
            Log::error("VIX取得エラー: " . $e->getMessage());
            return null;
        }
    }
}
