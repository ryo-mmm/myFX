<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarketData extends Model
{
    protected $fillable = ['symbol', 'open', 'high', 'low', 'close', 'vix_index', 'measured_at'];

    // 日時データを自動的に日付オブジェクトに変換
    protected $casts = [
        'measured_at' => 'datetime',
    ];
}
