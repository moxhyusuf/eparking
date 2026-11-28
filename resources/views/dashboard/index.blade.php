@extends('layouts.app')

@section('title', 'Dashboard')

@push('css')
    <style>
        .stat-card {
            border-left: 4px solid;
        }

        .stat-card.primary {
            border-color: #0d6efd;
        }

        .stat-card.success {
            border-color: #198754;
        }

        .stat-card.warning {
            border-color: #ffc107;
        }

        .stat-card.info {
            border-color: #0dcaf0;
        }
    </style>
@endpush

@section('content')
    <div class="pc-container">
        <div class="pc-content">
            <div class="page-header">
                <div class="page-block">
                    <div class="row align-items-center">
                        <div class="col-md-12">
                            <div class="page-header-title">
                                <h2 class="mb-0">Dashboard</h2>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Filter Bulan --}}
            <div class="row mb-3">
                <div class="col-auto">
                    <form method="GET" class="d-flex align-items-center gap-2">
                        <label class="fw-semibold mb-0">Bulan:</label>
                        @if ($periodes->isNotEmpty())
                            <select name="bulan" class="form-select form-select-sm" style="width:200px">
                                @foreach ($periodes as $p)
                                    @php $val = sprintf('%04d-%02d', $p->tahun, $p->bulan); @endphp
                                    <option value="{{ $val }}" @selected($bulan === $val)>
                                        {{ \Carbon\Carbon::createFromDate($p->tahun, $p->bulan, 1)->translatedFormat('F Y') }}
                                    </option>
                                @endforeach
                            </select>
                        @else
                            <span class="text-muted small">Belum ada data periode</span>
                        @endif
                        <button type="submit" class="btn btn-primary btn-sm">Tampilkan</button>
                    </form>
                </div>
            </div>

            @if (Auth::user()->role === 'admin')
                {{-- ======== ADMIN ======== --}}
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <div class="card stat-card primary h-100">
                            <div class="card-body">
                                <div class="text-muted small">Total Jukir</div>
                                <div class="fs-3 fw-bold">{{ $totalJukir }}</div>
                                <div class="text-muted small mt-1"><i class="ti ti-users"></i> Periode ini</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card info h-100">
                            <div class="card-body">
                                <div class="text-muted small">Total Pendapatan</div>
                                <div class="fs-4 fw-bold">Rp {{ number_format($totalR2 + $totalR4, 0, ',', '.') }}</div>
                                <div class="text-muted small mt-1"><i class="ti ti-cash"></i> R2 + R4</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card success h-100">
                            <div class="card-body">
                                <div class="text-muted small">Total R2 Bulan Ini</div>
                                <div class="fs-4 fw-bold">Rp {{ number_format($totalR2, 0, ',', '.') }}</div>
                                <div class="text-muted small mt-1"><i class="ti ti-motorbike"></i> Roda 2</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card warning h-100">
                            <div class="card-body">
                                <div class="text-muted small">Total R4 Bulan Ini</div>
                                <div class="fs-4 fw-bold">Rp {{ number_format($totalR4, 0, ',', '.') }}</div>
                                <div class="text-muted small mt-1"><i class="ti ti-car"></i> Roda 4</div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Grafik Harian + Donut --}}
                <div class="row g-3 mb-4">
                    <div class="col-md-8">
                        <div class="card h-100">
                            <div class="card-header">
                                <h5 class="mb-0">Fluktuasi Harian</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="chartHarian" height="120"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card h-100">
                            <div class="card-header">
                                <h5 class="mb-0">Komposisi Pendapatan</h5>
                            </div>
                            <div class="card-body d-flex align-items-center justify-content-center">
                                <canvas id="chartDonut" height="200"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Target Tahunan --}}
                <div class="row g-3 mb-4">
                    <div class="col-md-12">
                        <div class="card border-0 bg-light">
                            <div class="card-body py-3">
                                <div class="d-flex flex-wrap align-items-center gap-3">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="ti ti-target fs-5 text-primary"></i>
                                        <span class="fw-semibold">Target Pendapatan Tahunan</span>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="text-muted small">Rp</span>
                                        <input type="text" id="inputTarget" class="form-control form-control-sm" style="width:180px" placeholder="0" inputmode="numeric">
                                        <button class="btn btn-primary btn-sm" id="btnSimpanTarget">Simpan</button>
                                    </div>
                                    <div class="vr d-none d-md-block"></div>
                                    <div class="d-flex flex-column">
                                        <span class="text-muted small">Sudah tercapai</span>
                                        <span class="fw-bold" id="labelPencapaian">Rp 0</span>
                                    </div>
                                    <div class="d-flex flex-column">
                                        <span class="text-muted small">Sisa target</span>
                                        <span class="fw-bold" id="labelSisa">Rp 0</span>
                                    </div>
                                    <div style="flex:1; min-width:200px;">
                                        <div class="d-flex justify-content-between small mb-1">
                                            <span id="labelPersen">0%</span>
                                            <span id="labelTargetDisplay">Target: Rp 0</span>
                                        </div>
                                        <div class="progress" style="height:10px;">
                                            <div class="progress-bar" id="progressTarget" role="progressbar" style="width:0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Per Jukir --}}
                <div class="row g-3 mb-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Pendapatan Per Jukir</h5>
                            </div>
                            <div class="card-body p-0">
                                <div id="chartPerJukir" style="width: 100%; height: 600px;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                {{-- ======== JUKIR ======== --}}
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="card stat-card success h-100">
                            <div class="card-body">
                                <div class="text-muted small">Total R2 Bulan Ini</div>
                                <div class="fs-4 fw-bold">Rp {{ number_format($totalR2, 0, ',', '.') }}</div>
                                <div class="text-muted small mt-1"><i class="ti ti-motorbike"></i> Roda 2</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card stat-card warning h-100">
                            <div class="card-body">
                                <div class="text-muted small">Total R4 Bulan Ini</div>
                                <div class="fs-4 fw-bold">Rp {{ number_format($totalR4, 0, ',', '.') }}</div>
                                <div class="text-muted small mt-1"><i class="ti ti-car"></i> Roda 4</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card stat-card primary h-100">
                            <div class="card-body">
                                <div class="text-muted small">Total Pendapatan</div>
                                <div class="fs-4 fw-bold">Rp {{ number_format($totalR2 + $totalR4, 0, ',', '.') }}</div>
                                <div class="text-muted small mt-1"><i class="ti ti-cash"></i> R2 + R4</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Setoran Harian Saya</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="chartHarian" height="100"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

        </div>
    </div>
@endsection

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/echarts@5.4.3/dist/echarts.min.js"></script>
    <script>
        const labels = @json($labels);
        const dataR2 = @json($chartR2);
        const dataR4 = @json($chartR4);

        new Chart(document.getElementById('chartHarian'), {
            type: 'line',
            data: {
                labels,
                datasets: [{
                        label: 'R2',
                        data: dataR2,
                        borderColor: 'rgba(13, 110, 253, 1)',
                        backgroundColor: 'rgba(13, 110, 253, 0.1)',
                        tension: 0.3,
                        fill: true,
                        pointRadius: 3,
                    },
                    {
                        label: 'R4',
                        data: dataR4,
                        borderColor: 'rgba(220, 53, 69, 1)',
                        backgroundColor: 'rgba(220, 53, 69, 0.1)',
                        tension: 0.3,
                        fill: true,
                        pointRadius: 3,
                    },
                ],
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top'
                    }
                },
                scales: {
                    y: {
                        ticks: {
                            callback: v => 'Rp ' + v.toLocaleString('id-ID')
                        },
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Tanggal'
                        }
                    },
                },
            },
        });

        @if (Auth::user()->role === 'admin')
            new Chart(document.getElementById('chartDonut'), {
                type: 'doughnut',
                data: {
                    labels: ['R2', 'R4'],
                    datasets: [{
                        data: [{{ $totalR2 }}, {{ $totalR4 }}],
                        backgroundColor: ['rgba(13,110,253,0.8)', 'rgba(220,53,69,0.8)'],
                    }],
                },
                options: {
                    plugins: {
                        legend: {
                            position: 'bottom'
                        },
                        tooltip: {
                            callbacks: {
                                label: ctx => ' Rp ' + ctx.parsed.toLocaleString('id-ID'),
                            },
                        },
                    },
                },
            });

            // ===== TARGET TAHUNAN (localStorage) =====
            const tahunAktif = '{{ $bulan }}'.substring(0, 4);
            const storageKey = 'eparking_target_' + tahunAktif;
            const totalPendapatanTahunIni = {{ $totalPendapatanTahun }};

            function formatRp(val) {
                return 'Rp ' + Math.round(val).toLocaleString('id-ID');
            }

            function updateTargetUI(target) {
                const persen = target > 0 ? Math.min(100, (totalPendapatanTahunIni / target) * 100) : 0;
                const sisa = Math.max(0, target - totalPendapatanTahunIni);

                document.getElementById('labelPencapaian').textContent = formatRp(totalPendapatanTahunIni);
                document.getElementById('labelSisa').textContent = formatRp(sisa);
                document.getElementById('labelPersen').textContent = persen.toFixed(1) + '%';
                document.getElementById('labelTargetDisplay').textContent = 'Target: ' + formatRp(target);

                const bar = document.getElementById('progressTarget');
                bar.style.width = persen + '%';
                bar.setAttribute('aria-valuenow', persen);
                bar.className = 'progress-bar' + (persen >= 100 ? ' bg-success' : persen >= 60 ? ' bg-primary' : ' bg-warning');

                const inp = document.getElementById('inputTarget');
                if (target > 0) inp.value = Math.round(target).toLocaleString('id-ID');
            }

            try {
                const saved = localStorage.getItem(storageKey);
                if (saved) updateTargetUI(parseFloat(saved));
            } catch (e) {}

            document.getElementById('inputTarget').addEventListener('input', function() {
                const raw = this.value.replace(/\D/g, '');
                this.value = raw ? parseInt(raw).toLocaleString('id-ID') : '';
            });

            document.getElementById('btnSimpanTarget').addEventListener('click', function() {
                const raw = document.getElementById('inputTarget').value.replace(/\D/g, '');
                const val = parseFloat(raw) || 0;
                try {
                    localStorage.setItem(storageKey, val);
                } catch (e) {}
                updateTargetUI(val);
                this.textContent = 'Tersimpan!';
                setTimeout(() => this.textContent = 'Simpan', 1500);
            });

            // ===== CHART PER JUKIR (ECharts) =====
            const allJukirLabels = @json($jukirLabels);
            const allJukirR2 = @json($jukirR2);
            const allJukirR4 = @json($jukirR4);

            // Mengurutkan nama jukir berdasarkan abjad A-Z
            const urutanJukir = allJukirLabels
           .map((nama, index) => ({
            nama: nama,
            r2: allJukirR2[index],
            r4: allJukirR4[index]
            }))
            .sort((a, b) => a.nama.localeCompare(b.nama, 'id', {
            sensitivity: 'base'
            }));

            const jukirLabelsAZ = urutanJukir.map(item => item.nama);
            const jukirR2AZ = urutanJukir.map(item => item.r2);
            const jukirR4AZ = urutanJukir.map(item => item.r4);

            function getEChartsOption() {
                const chartH = Math.max(400, allJukirLabels.length * 28);
                document.getElementById('chartPerJukir').style.height = chartH + 'px';

                return {
                    legend: {
                        top: 8,
                        data: ['R2', 'R4']
                    },
                    tooltip: {
                        trigger: 'axis',
                        axisPointer: {
                            type: 'shadow'
                        },
                        formatter: params => {
                            let out = params[0].name + '<br>';
                            params.forEach(p => {
                                out += p.marker + p.seriesName + ': Rp ' + p.value.toLocaleString('id-ID') + '<br>';
                            });
                            return out;
                        }
                    },
                    grid: {
                        left: 160,
                        right: 40,
                        top: 44,
                        bottom: 36,
                        containLabel: false
                    },
                    xAxis: {
                        type: 'value',
                        axisLabel: {
                            formatter: v => 'Rp ' + (v >= 1000000 ? (v / 1000000).toFixed(1) + 'jt' : (v / 1000).toFixed(0) + 'rb')
                        }
                    },
                    yAxis: {
                    type: 'category',
                    data: jukirLabelsAZ,
                    inverse: true,
                    axisLabel: {
                        fontSize: 11,
                        width: 150,
                        overflow: 'truncate'
                    }
                    },
                    dataZoom: [{
                            type: 'slider',
                            yAxisIndex: 0,
                            start: 0,
                            end: Math.min(100, Math.round(3000 / chartH * 100)),
                            width: 16,
                            right: 8,
                            filterMode: 'filter'
                        },
                        {
                            type: 'inside',
                            yAxisIndex: 0,
                            zoomOnMouseWheel: false,
                            moveOnMouseWheel: true
                        }
                    ],
                    series: [{
                    name: 'R2',
                    type: 'bar',
                    stack: 'total',
                    data: jukirR2AZ,
                    itemStyle: {
                        color: '#646b73' // Abu-abu muda
                    },
                    barMaxWidth: 20
                    },
                    {
                    name: 'R4',
                    type: 'bar',
                    stack: 'total',
                    data: jukirR4AZ,
                    itemStyle: {
                        color: '#2c2d2e' 
                    },
                    barMaxWidth: 20
                    }
                ]

            const eJukir = echarts.init(document.getElementById('chartPerJukir'));
            eJukir.setOption(getEChartsOption());
            window.addEventListener('resize', () => eJukir.resize());
        @endif
    </script>
@endpush
