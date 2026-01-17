<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderDistribution extends Model
{
    protected $fillable = ['symbol', 'order_book_data', 'long_ratio', 'short_ratio', 'snapshot_at'];

    protected $casts = [
        'order_book_data' => 'array', // JSONを自動で配列として扱えるようにする
        'snapshot_at' => 'datetime',
    ];
}
