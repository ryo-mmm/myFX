<script src="https://cdn.tailwindcss.com"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-bold text-2xl text-gray-800 leading-tight tracking-tight">
                {{ __('トレード分析ダッシュボード') }}
            </h2>
            <div class="hidden sm:block text-sm text-gray-500 font-medium">
                Live Market Analysis
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-12 bg-[#f8fafc]"> {{-- より洗練されたグレー背景 --}}
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- 1. ドル円メインカード (レスポンシブ対応) --}}
            <div class="mb-6 sm:mb-8">
                <div class="relative overflow-hidden bg-gradient-to-br from-indigo-600 via-blue-700 to-blue-800 p-6 sm:p-8 rounded-3xl shadow-2xl shadow-blue-200/50 text-white">
                    {{-- 装飾用の背景サークル --}}
                    <div class="absolute -right-10 -top-10 w-40 h-40 bg-white/10 rounded-full blur-3xl"></div>

                    <div class="relative z-10">
                        <h3 class="text-xs sm:text-sm font-bold uppercase tracking-widest opacity-80 mb-1">USD / JPY（ドル円）</h3>
                        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
                            <div>
                                <span class="text-5xl sm:text-7xl font-black tracking-tighter leading-none">
                                    {{ $usdjpy ? number_format($usdjpy->close, 2) : '---.--' }}
                                </span>
                                <span class="text-xl sm:text-2xl ml-2 font-bold opacity-70">JPY</span>
                            </div>
                            <div class="flex flex-row sm:flex-col items-center sm:items-end gap-2 sm:gap-0">
                                <p class="text-[10px] sm:text-xs uppercase font-bold opacity-60">Last Updated (JST)</p>
                                <p class="text-lg sm:text-xl font-mono font-bold bg-black/20 px-3 py-1 rounded-lg backdrop-blur-sm">
                                    {{ $usdjpy ? \Carbon\Carbon::parse($usdjpy->measured_at)->addHours(9)->format('H:i:s') : '--:--:--' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 2. 4つの指標カード & リスクレベル定義 (グリッド最適化) --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                {{-- 指標カード (2x2) --}}
                <div class="lg:col-span-2 grid grid-cols-2 gap-4">
                    @foreach(['VIX' => 'VIX指数', 'US10Y' => '米10年債', 'DXY' => 'ドル指数', 'SP500' => 'S&P500'] as $key => $label)
                    @php $data = $latestData[$key] ?? null; @endphp
                    <div class="bg-white p-5 rounded-2xl shadow-sm border transition-all duration-300 hover:shadow-md {{ ($key === 'VIX' && $data && $data->close >= 20) ? 'border-red-200 bg-red-50/50' : 'border-gray-100 hover:border-blue-200' }}">
                        <h3 class="text-[10px] sm:text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">{{ $label }}</h3>
                        <div class="flex items-baseline justify-between">
                            <span class="text-xl sm:text-3xl font-black text-gray-900 leading-none">
                                {{ $data ? number_format($data->close, ($key === 'US10Y' || $key === 'DXY' ? 3 : 2)) : '---' }}
                            </span>
                            <p class="text-[10px] font-mono font-bold text-gray-400">
                                {{ $data ? \Carbon\Carbon::parse($data->measured_at)->addHours(9)->format('H:i') : '--:--' }}
                            </p>
                        </div>
                    </div>
                    @endforeach
                </div>

                {{-- リスクレベル定義 (スマホでは最後、PCでは横に) --}}
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex flex-col justify-center">
                    <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4">Risk Level Definition</h3>
                    <div class="space-y-3">
                        <div class="flex items-center p-2 rounded-xl bg-green-50/50 border border-green-100">
                            <span class="w-12 text-xs font-black text-green-700">0-15</span>
                            <span class="ml-auto text-xs text-green-600 font-bold uppercase">Stable</span>
                        </div>
                        <div class="flex items-center p-2 rounded-xl bg-yellow-50/50 border border-yellow-100">
                            <span class="w-12 text-xs font-black text-yellow-700">15-20</span>
                            <span class="ml-auto text-xs text-yellow-600 font-bold uppercase">Caution</span>
                        </div>
                        <div class="flex items-center p-2 rounded-xl bg-red-50/50 border border-red-100">
                            <span class="w-12 text-xs font-black text-red-700">20+</span>
                            <span class="ml-auto text-xs text-red-600 font-bold uppercase">Danger</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 4. グラフセクション (2カラムレイアウト) --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 sm:gap-8">
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                    <h3 class="text-base font-bold text-gray-800 mb-6 flex items-center">
                        <span class="w-2 h-5 bg-blue-500 rounded mr-2"></span>VIX 24h Trend
                    </h3>
                    <div class="h-[250px] sm:h-[300px]"><canvas id="vixChart"></canvas></div>
                </div>

                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                    <h3 class="text-base font-bold text-gray-800 mb-6 flex items-center">
                        <span class="w-2 h-5 bg-indigo-500 rounded mr-2"></span>USD/JPY 24h Trend
                    </h3>
                    <div class="h-[250px] sm:h-[300px]"><canvas id="usdjpyChart"></canvas></div>
                </div>

                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                    <h3 class="text-base font-bold text-gray-800 mb-2 flex items-center">
                        <span class="w-2 h-5 bg-purple-500 rounded mr-2"></span>RSI (14) - Momentum
                    </h3>
                    <div class="h-[180px] sm:h-[200px]"><canvas id="rsiChart"></canvas></div>
                    <div class="flex justify-between text-[10px] font-bold text-gray-400 mt-4 uppercase tracking-widest px-2">
                        <span class="text-blue-400">Oversold (30)</span>
                        <span class="text-red-400">Overbought (70)</span>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                    <h3 class="text-base font-bold text-gray-800 mb-6 flex items-center">
                        <span class="w-2 h-5 bg-orange-500 rounded mr-2"></span>USD/JPY vs US10Y
                    </h3>
                    <div class="h-[350px] sm:h-[400px]"><canvas id="comparisonChart"></canvas></div>
                </div>
            </div>

            {{-- 5. 履歴テーブル --}}
            <div class="mt-8 bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-5 bg-gray-50/50 border-b border-gray-100">
                    <h3 class="text-base font-bold text-gray-800">Latest VIX History</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50/80">
                                <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Time (JST)</th>
                                <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Value</th>
                                <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach($historyVix as $vix)
                            <tr class="hover:bg-blue-50/30 transition-colors">
                                <td class="px-6 py-4 text-sm font-medium text-gray-600">
                                    {{ \Carbon\Carbon::parse($vix->measured_at)->addHours(9)->format('m/d H:i') }}
                                </td>
                                <td class="px-6 py-4 text-sm font-mono font-black text-gray-900">
                                    {{ number_format($vix->close, 2) }}
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider {{ $vix->close >= 20 ? 'bg-red-100 text-red-700' : ($vix->close >= 15 ? 'bg-yellow-100 text-yellow-700' : 'bg-green-100 text-green-700') }}">
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

            // テクニカル指標のデータ
            const ma25 = @json($ma25Values);
            const ma75 = @json($ma75Values);
            const ma200 = @json($ma200Values);
            const rsiValues = @json($rsiValues);

            // RSIの移動平均（シグナル）をJS側で計算
            // これにより「短期的な勢い」と「平均的な勢い」の差が見えるようになります
            const rsiSignal = rsiValues.map((v, i, a) => {
                if (i < 9) return null; // 9点溜まるまでは表示しない
                const slice = a.slice(i - 9, i);
                const sum = slice.reduce((s, x) => s + (x || 0), 0);
                return sum / 9;
            });

            const commonOptions = {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    intersect: false,
                    mode: 'index'
                },
                plugins: {
                    legend: {
                        display: true,
                        labels: {
                            boxWidth: 12,
                            font: {
                                size: 10
                            }
                        }
                    }
                }
            };

            // 1. VIX Chart
            new Chart(document.getElementById('vixChart'), {
                type: 'line',
                data: {
                    labels: vixLabels,
                    datasets: [{
                        label: 'VIX',
                        data: vixValues,
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        fill: true,
                        pointRadius: 0
                    }]
                },
                options: commonOptions
            });

            // 2. USD/JPY Chart (MA25, 75, 200)
            new Chart(document.getElementById('usdjpyChart'), {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                            label: 'USD/JPY 価格',
                            data: usdValues,
                            borderColor: '#2563eb',
                            borderWidth: 2,
                            pointRadius: 0,
                            fill: false
                        },
                        {
                            label: '25MA (短期)',
                            data: ma25,
                            borderColor: '#10b981',
                            borderWidth: 1,
                            pointRadius: 0,
                            fill: false
                        },
                        {
                            label: '75MA (中期)',
                            data: ma75,
                            borderColor: '#f59e0b',
                            borderWidth: 1,
                            pointRadius: 0,
                            fill: false
                        },
                        {
                            label: '200MA (長期)',
                            data: ma200,
                            borderColor: '#ef4444',
                            borderWidth: 1.5,
                            pointRadius: 0,
                            fill: false
                        }
                    ]
                },
                options: commonOptions
            });

            // 3. RSI Chart (RSI 14 + RSI Signal)
            const rsiCtx = document.getElementById('rsiChart');
            if (rsiCtx) {
                new Chart(rsiCtx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                                label: 'RSI (14)',
                                data: rsiValues,
                                borderColor: '#8b5cf6', // 紫（メイン）
                                borderWidth: 2,
                                pointRadius: 0,
                                fill: false,
                                tension: 0.3
                            },
                            {
                                label: 'RSI シグナル (短期)',
                                data: rsiSignal,
                                borderColor: '#ec4899', // ピンク（補助線）
                                borderWidth: 1.5,
                                borderDash: [3, 3], // 点線にする
                                pointRadius: 0,
                                fill: false
                            }
                        ]
                    },
                    options: {
                        ...commonOptions,
                        scales: {
                            y: {
                                min: 0,
                                max: 100,
                                ticks: {
                                    stepSize: 20
                                },
                                grid: {
                                    color: (ctx) => (ctx.tick.value === 70 || ctx.tick.value === 30) ? '#ffccd5' : '#f0f0f0'
                                }
                            }
                        }
                    }
                });
            }

            // 4. Comparison Chart
            new Chart(document.getElementById('comparisonChart'), {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                            label: 'USD/JPY (左)',
                            data: usdValues,
                            borderColor: '#2563eb',
                            yAxisID: 'y-usd',
                            pointRadius: 0
                        },
                        {
                            label: '米10年債 % (右)',
                            data: us10yValues,
                            borderColor: '#f59e0b',
                            yAxisID: 'y-10y',
                            pointRadius: 0
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