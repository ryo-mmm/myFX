<?php

namespace App\Services;

use Scheb\YahooFinanceApi\ApiClientFactory;
use DateTime;

class YahooFinanceService
{
    /**
     * Yahoo FinanceからVIXの最新価格を取得
     */
    public function getVixQuote()
    {
        // クライアントの作成
        $client = ApiClientFactory::createApiClient();

        // VIXのシンボルは ^VIX です
        $quote = $client->getQuote("^VIX");

        if (!$quote) {
            return null;
        }

        return [
            'close'       => $quote->getRegularMarketPrice(),
            'open'        => $quote->getRegularMarketOpen(),
            'high'        => $quote->getRegularMarketDayHigh(),
            'low'         => $quote->getRegularMarketDayLow(),
            'measured_at' => $quote->getRegularMarketTime(), // これはDateTimeオブジェクトで返ります
        ];
    }
}
