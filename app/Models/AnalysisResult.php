<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnalysisResult extends Model
{
    protected $fillable = ['symbol', 'prediction_percentage', 'prediction_type', 'basis_data', 'is_correct'];

    protected $casts = [
        'basis_data' => 'array', // 根拠データ（RSI等）を配列で保存
    ];
}
