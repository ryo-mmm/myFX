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

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
                    <h3 class="text-sm font-semibold text-gray-400 uppercase">最新VIX指数</h3>
                    <div class="mt-2 flex items-baseline">
                        <span class="text-3xl font-bold text-gray-900">{{ $latestVix->close ?? '---' }}</span>
                        @if($latestVix)
                        <span class="ml-2 text-sm font-medium text-gray-500">at {{ $latestVix->measured_at->format('H:i') }}</span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 mb-8">
                <h3 class="text-lg font-bold text-gray-800 mb-4">VIX 24時間トレンド</h3>
                <div style="position: relative; height:300px; width:100%">
                    <canvas id="vixChart"></canvas>
                </div>
            </div>

        </div>
    </div>

    <script>
        // window.onload を使うことで、Chart.jsの読み込み完了を確実に待ちます
        window.onload = function() {
            const canvas = document.getElementById('vixChart');
            if (!canvas) {
                console.error("Canvas要素が見つかりません");
                return;
            }

            const ctx = canvas.getContext('2d');

            // PHPからデータを確実に受け取る（エディタで赤線が出ても無視してOK）
            const labels = <?php echo json_encode($vixLabels); ?>;
            const data = <?php echo json_encode($vixValues); ?>;

            console.log("Labels:", labels); // ブラウザのコンソールでデータを確認用
            console.log("Data:", data);

            if (data.length === 0) {
                alert("表示するデータが0件です。Tinkerでデータを入れたか確認してください。");
                return;
            }

            const latestValue = data[data.length - 1]; // 最新のVIX値を取得
            const isHighRisk = latestValue >= 20; // 20以上ならハイリスクと判定

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'VIX指数',
                        data: data,
                        borderColor: isHighRisk ? '#ef4444' : '#3b82f6', // 赤 : 青
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
                            beginAtZero: false,
                            grid: {
                                borderDash: [5, 5]
                            }
                        }
                    }
                }
            });
        };
    </script>
</x-app-layout>