<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Process;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// 30分おきにPythonスクリプトを実行して全指標を更新
Schedule::call(function () {
    // python3 のフルパスや scripts のパスは環境に合わせて調整してください
    Process::run('/usr/bin/python3 /var/www/html/scripts/fetch_market_data.py');
})->everyThirtyMinutes();
