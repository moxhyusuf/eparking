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
                        <input type="month" name="bulan" value="{{ $bulan }}" class="form-control form-control-sm" style="width:160px">
                        <button type="submit" class="btn btn-primary btn-sm">Tampilkan</button>
                    </form>
                </div>
            </div>

            @if (Auth::user()->role === 'admin')
                {{-- ADMIN STATS --}}
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <div class="card stat-card primary h-100">
                            <div class="card-body">
                                <div class="text-muted small">Total Jukir</div>
                                <div class="fs-3 fw-bold">{{ $totalJukir }}</div>
                                <div class="text-muted small mt-1"><i class="ti ti-users"></i> Terdaftar</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card info h-100">
                            <div class="card-body">
                                <div class="text-muted small">Total User</div>
                                <div class="fs-3 fw-bold">{{ $totalUser }}</div>
                                <div class="text-muted small mt-1"><i class="ti ti-user"></i> Akun aktif</div>
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

                {{-- Row Grafik Fluktuasi Harian & Donut --}}
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

                {{-- Fluktuasi Per Jukir (horizontal bar) --}}
                <div class="row g-3 mb-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Fluktuasi Pendapatan Per-Jukir</h5>
                            </div>
                            <div class="card-body">
                                <div style="height: {{ count($jukirLabels) * 28 }}px; min-height: 300px;">
                                    <canvas id="chartPerJukir"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                {{-- JUKIR STATS --}}
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
                    }
                ]
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
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Tanggal'
                        }
                    }
                }
            }
        });

        @if (Auth::user()->role === 'admin')
            // Donut
            new Chart(document.getElementById('chartDonut'), {
                type: 'doughnut',
                data: {
                    labels: ['R2', 'R4'],
                    datasets: [{
                        data: [{{ $totalR2 }}, {{ $totalR4 }}],
                        backgroundColor: ['rgba(13,110,253,0.8)', 'rgba(220,53,69,0.8)'],
                    }]
                },
                options: {
                    plugins: {
                        legend: {
                            position: 'bottom'
                        },
                        tooltip: {
                            callbacks: {
                                label: ctx => ' Rp ' + ctx.parsed.toLocaleString('id-ID')
                            }
                        }
                    }
                }
            });

            // Horizontal bar per jukir
            const jukirLabels = @json($jukirLabels);
            const jukirR2 = @json($jukirR2);
            const jukirR4 = @json($jukirR4);

            new Chart(document.getElementById('chartPerJukir'), {
                type: 'bar',
                data: {
                    labels: jukirLabels,
                    datasets: [{
                            label: 'R2',
                            data: jukirR2,
                            backgroundColor: 'rgba(13, 110, 253, 0.8)',
                        },
                        {
                            label: 'R4',
                            data: jukirR4,
                            backgroundColor: 'rgba(220, 53, 69, 0.8)',
                        }
                    ]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top'
                        },
                        tooltip: {
                            callbacks: {
                                label: ctx => ctx.dataset.label + ': Rp ' + ctx.parsed.x.toLocaleString('id-ID')
                            }
                        }
                    },
                    scales: {
                        x: {
                            stacked: false,
                            ticks: {
                                callback: v => 'Rp ' + v.toLocaleString('id-ID')
                            }
                        },
                        y: {
                            stacked: false
                        }
                    }
                }
            });
        @endif
    </script>
@endpush
