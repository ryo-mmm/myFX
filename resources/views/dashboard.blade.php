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

            {{-- 1. ドル円メインカード --}}
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
                            <p class="text-xs opacity-80">最終更新 (日本時間)</p>
                            <p class="text-lg font-mono font-bold">
                                {{ $usdjpy ? \Carbon\Carbon::parse($usdjpy->measured_at)->addHours(9)->format('H:i:s') : '--:--:--' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 2. 4つの指標カード --}}
            <div class="grid grid-cols-2 gap-4 mb-8">
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
                        {{ $data ? \Carbon\Carbon::parse($data->measured_at)->addHours(9)->format('H:i') : '--:--' }}
                    </p>
                </div>
                @endforeach
            </div>

            {{-- 3. リスクレベル定義 (復活) --}}
            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 mb-8">
                <h3 class="text-sm font-semibold text-gray-400 uppercase mb-3">リスクレベル定義</h3>
                <div class="grid grid-cols-3 gap-2">
                    <div class="p-2 rounded bg-green-50 border border-green-100 text-center">
                        <span class="block text-xs font-bold text-green-700">0 - 15</span>
                        <span class="text-xs text-green-600 font-medium">安定</span>
                    </div>
                    <div class="p-2 rounded bg-yellow-50 border border-yellow-100 text-center">
                        <span class="block text-xs font-bold text-yellow-700">15 - 20</span>
                        <span class="text-xs text-yellow-600 font-medium">注意</span>
                    </div>
                    <div class="p-2 rounded bg-red-50 border border-red-100 text-center">
                        <span class="block text-xs font-bold text-red-700">20+</span>
                        <span class="text-xs text-red-600 font-medium">警戒</span>
                    </div>
                </div>
            </div>

            {{-- 4. グラフセクション --}}
            <div class="space-y-8">
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">VIX 24時間トレンド</h3>
                    <div style="height:300px;"><canvas id="vixChart"></canvas></div>
                </div>

                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">ドル円 24時間トレンド</h3>
                    <div style="height:300px;"><canvas id="usdjpyChart"></canvas></div>
                </div>

                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">ドル円 vs 米10年債利回り</h3>
                    <div style="height:400px;"><canvas id="comparisonChart"></canvas></div>
                </div>
            </div>

            {{-- 5. 履歴テーブル (判定バッジ復活) --}}
            <div class="mt-8 bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="p-6 border-b border-gray-100">
                    <h3 class="text-lg font-bold text-gray-800">最新履歴 (VIX)</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase">取得日時 (JST)</th>
                                <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase">VIX指数</th>
                                <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase">判定</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($historyVix as $vix)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    {{ \Carbon\Carbon::parse($vix->measured_at)->addHours(9)->format('m/d H:i') }}
                                </td>
                                <td class="px-6 py-4 text-sm font-mono font-bold text-gray-900">{{ number_format($vix->close, 2) }}</td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 text-xs rounded-full font-bold {{ $vix->close >= 20 ? 'bg-red-100 text-red-700' : ($vix->close >= 15 ? 'bg-yellow-100 text-yellow-700' : 'bg-green-100 text-green-700') }}">
                                        {{ $vix->close >= 20 ? '警戒' : ($vix->close >= 15 ? '注意' : '安定') }}
                                    </span>
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
            const labels = @json($chartLabels);
            const usdValues = @json($usdjpyValues);
            const us10yValues = @json($us10yValues);
            const vixLabels = @json($vixLabels);
            const vixValues = @json($vixValues);

            const commonOptions = {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    intersect: false,
                    mode: 'index'
                }
            };

            // VIX
            new Chart(document.getElementById('vixChart'), {
                type: 'line',
                data: {
                    labels: vixLabels,
                    datasets: [{
                        label: 'VIX',
                        data: vixValues,
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        fill: true
                    }]
                },
                options: commonOptions
            });

            // USDJPY
            new Chart(document.getElementById('usdjpyChart'), {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'USD/JPY',
                        data: usdValues,
                        borderColor: '#2563eb',
                        fill: false
                    }]
                },
                options: commonOptions
            });

            // Comparison
            new Chart(document.getElementById('comparisonChart'), {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                            label: 'USD/JPY (左)',
                            data: usdValues,
                            borderColor: '#2563eb',
                            yAxisID: 'y-usd'
                        },
                        {
                            label: '米10年債 % (右)',
                            data: us10yValues,
                            borderColor: '#f59e0b',
                            yAxisID: 'y-10y'
                        }
                    ]
                },
                options: {
                    ...commonOptions,
                    scales: {
                        'y-usd': {
                            position: 'left'
                        },
                        'y-10y': {
                            position: 'right',
                            grid: {
                                drawOnChartArea: false
                            }
                        }
                    }
                }
            });
        };
    </script>
</x-app-layout>