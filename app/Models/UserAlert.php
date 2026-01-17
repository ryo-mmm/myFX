<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAlert extends Model
{
    protected $fillable = ['user_id', 'symbol', 'threshold_percentage', 'is_active'];

    // Userモデルとのリレーション（このアラートは誰のものか）
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
