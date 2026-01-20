<script src="https://cdn.tailwindcss.com"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('トレード分析ダッシュボード') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-100">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="mb-8">
                <div class="bg-gradient-to-r from-blue-600 to-indigo-700 p-6 rounded-2xl shadow-lg text-white">
                    <h3 class="text-sm font-semibold uppercase opacity-80">USD / JPY（ドル円）</h3>
                    <div class="flex items-end justify-between">
                        <div class="mt-2">
                            <span class="text-5xl font-black tracking-tighter">
                                {{ $usdjpy ? number_format($usdjpy->close, 2) : '---.--' }}
                            </span>
                            <span class="text-xl ml-1 font-bold opacity-70">円</span>
                        </div>
                        <div class="text-right">
                            <p class="text-xs opacity-80">最終更新</p>
                            <p class="text-lg font-mono font-bold">{{ $usdjpy ? $usdjpy->measured_at->format('H:i:s') : '--:--:--' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
                @foreach(['VIX' => 'VIX指数', 'US10Y' => '米10年債', 'DXY' => 'ドル指数', 'SP500' => 'S&P500'] as $key => $label)
                @php $data = $latestData[$key] ?? null; @endphp
                <div class="bg-white p-4 rounded-xl shadow-sm border {{ ($key === 'VIX' && $data && $data->close >= 20) ? 'border-red-500 bg-red-50' : 'border-gray-200' }}">
                    <h3 class="text-xs font-semibold text-gray-400 uppercase">{{ $label }}</h3>
                    <div class="mt-1 flex items-baseline">
                        <span class="text-2xl font-bold text-gray-900">
                            {{ $data ? number_format($data->close, ($key === 'US10Y' || $key === 'DXY' ? 3 : 2)) : '---' }}
                        </span>
                    </div>
                    <p class="text-[10px] text-gray-400 mt-1 italic">
                        {{ $data ? $data->measured_at->format('H:i') : '--:--' }}
                    </p>
                </div>
                @endforeach
            </div>

            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 mb-8">
                <h3 class="text-sm font-semibold text-gray-400 uppercase mb-3">リスクレベル定義</h3>
                <div class="grid grid-cols-3 gap-2">
                    <div class="p-2 rounded bg-green-50 border border-green-100 text-center">
                        <span class="block text-xs font-bold text-green-700">0 - 15</span>
                        <span class="text-xs text-green-600 font-medium">安定（買い場）</span>
                    </div>
                    <div class="p-2 rounded bg-yellow-50 border border-yellow-100 text-center">
                        <span class="block text-xs font-bold text-yellow-700">15 - 20</span>
                        <span class="text-xs text-yellow-600 font-medium">やや注意</span>
                    </div>
                    <div class="p-2 rounded bg-red-50 border border-red-100 text-center">
                        <span class="block text-xs font-bold text-red-700">20+</span>
                        <span class="text-xs text-red-600 font-medium">警戒（暴落リスク）</span>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 mb-8">
                <h3 class="text-lg font-bold text-gray-800 mb-4">VIX 24時間トレンド</h3>
                <div style="position: relative; height:300px; width:100%">
                    <canvas id="vixChart"></canvas>
                </div>
            </div>

            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 mb-8">
                <h3 class="text-lg font-bold text-gray-800 mb-4">ドル円 24時間トレンド</h3>
                <div style="position: relative; height:300px; width:100%">
                    <canvas id="usdjpyChart"></canvas>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="p-6 border-b border-gray-100">
                    <h3 class="text-lg font-bold text-gray-800">最新履歴 (過去20回)</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">取得日時</th>
                                <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">VIX指数</th>
                                <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">判定</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($historyVix as $vix)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $vix->measured_at->format('m/d H:i') }}</td>
                                <td class="px-6 py-4 text-sm font-mono font-bold text-gray-900">{{ number_format($vix->close, 2) }}</td>
                                <td class="px-6 py-4">
                                    @if($vix->close >= 20)
                                    <span class="px-2 py-1 text-xs bg-red-100 text-red-700 rounded-full font-bold">警戒</span>
                                    @elseif($vix->close >= 15)
                                    <span class="px-2 py-1 text-xs bg-yellow-100 text-yellow-700 rounded-full font-bold">注意</span>
                                    @else
                                    <span class="px-2 py-1 text-xs bg-green-100 text-green-700 rounded-full font-bold">安定</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        window.onload = function() {
            const ctx = document.getElementById('vixChart').getContext('2d');

            const labels = @json($vixLabels);
            const data = @json($vixValues);

            if (data.length === 0) return;

            const latestValue = data[data.length - 1];
            const isHighRisk = latestValue >= 20;

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'VIX指数',
                        data: data,
                        borderColor: isHighRisk ? '#ef4444' : '#3b82f6',
                        backgroundColor: isHighRisk ? 'rgba(239, 68, 68, 0.1)' : 'rgba(59, 130, 246, 0.1)',
                        borderWidth: 3,
                        tension: 0.3,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: false
                        }
                    }
                }
            });
        };

        const ctxUsd = document.getElementById('usdjpyChart').getContext('2d');
        const labelsUsd = @json($usdjpyLabels);
        const dataUsd = @json($usdjpyValues);

        new Chart(ctxUsd, {
            type: 'line',
            data: {
                labels: labelsUsd,
                datasets: [{
                    label: 'USD/JPY',
                    data: dataUsd,
                    borderColor: '#2563eb', // 青色
                    backgroundColor: 'rgba(37, 99, 235, 0.1)',
                    borderWidth: 2,
                    pointRadius: 0, // 点を消してスッキリさせる
                    tension: 0.2,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: false,
                        ticks: {
                            callback: (value) => value.toFixed(2)
                        } // 小数点2桁
                    }
                }
            }
        });
    </script>
</x-app-layout>