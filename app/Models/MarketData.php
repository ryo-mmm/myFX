<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use HasFactory;

class MarketData extends Model
{
    protected $fillable = ['symbol', 'open', 'high', 'low', 'close', 'vix_index', 'measured_at'];

    // テーブル名を明示的に指定
    protected $table = 'market_data';

    // 日時データを自動的に日付オブジェクトに変換
    protected $casts = [
        'measured_at' => 'datetime',
    ];
}
