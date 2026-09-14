@extends('layouts.app')

@section('title', 'Laporan Setoran')

@push('css')
    {{-- Choices.js --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js@10/public/assets/styles/choices.min.css">
    {{-- DataTables --}}
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

    <style>
        /* ── Filter card ─────────────────────────────────── */
        .filter-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, .07);
        }

        .filter-label {
            font-size: .75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: #6c757d;
            margin-bottom: 6px;
        }

        /* Choices: fix width & clipping */
        .choices {
            width: 100%;
        }

        .choices__inner {
            min-height: 42px;
            border-radius: 8px !important;
            border-color: #dee2e6 !important;
            padding: 6px 10px !important;
            font-size: .9rem;
        }

        .choices__list--dropdown {
            border-radius: 8px !important;
        }

        .choices__item {
            white-space: normal !important;
        }

        .choices__list--dropdown .choices__item {
            padding: 8px 12px;
        }

        /* ── Stat cards ──────────────────────────────────── */
        .stat-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, .07);
            transition: transform .15s;
        }

        .stat-card:hover {
            transform: translateY(-2px);
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
        }

        /* ── Table card ──────────────────────────────────── */
        .table-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, .07);
        }

        .table-card .card-header {
            border-radius: 12px 12px 0 0 !important;
            background: #fff;
            border-bottom: 1px solid #f0f0f0;
            padding: 16px 20px;
        }

        /* Style perbaikan DataTables wrapper & control */
        .dataTables_wrapper {
            padding: 1rem 1.25rem;
        }

        div.dataTables_wrapper div.dataTables_filter input {
            border-radius: 8px;
            padding: 5px 10px;
        }

        div.dataTables_wrapper div.dataTables_length select {
            border-radius: 8px;
            padding: 5px 25px 5px 10px;
        }

        .month-badge {
            background: #e8f0fe;
            color: #1a56db;
            font-size: .75rem;
            padding: 4px 12px;
            border-radius: 20px;
            font-weight: 600;
        }
    </style>
@endpush

@section('content')
    <div class="pc-container">
        <div class="pc-content">

            {{-- Page header --}}
            <div class="page-header mb-4">
                <div class="page-block">
                    <div class="d-flex align-items-center gap-3">
                        <div>
                            <h2 class="mb-0">Laporan Setoran</h2>
                            <p class="text-muted mb-0 mt-1" style="font-size:.85rem">
                                Data setoran harian juru parkir
                            </p>
                        </div>
                        <span class="month-badge ms-2">
                            {{ DateTime::createFromFormat('!m', (int) $bulan)->format('F') }} {{ $tahun }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- ── Filter ───────────────────────────────────── --}}
            <div class="card filter-card mb-4">
                <div class="card-body py-3 px-4">
                    <form method="GET" id="formFilter">
                        <div class="row g-3 align-items-end">

                            <div class="col-6 col-md-3">
                                <div class="filter-label">Bulan</div>
                                <select name="bulan" id="selBulan" class="form-select">
                                    @php
                                        $namabulan = [
                                            1 => 'Januari',
                                            2 => 'Februari',
                                            3 => 'Maret',
                                            4 => 'April',
                                            5 => 'Mei',
                                            6 => 'Juni',
                                            7 => 'Juli',
                                            8 => 'Agustus',
                                            9 => 'September',
                                            10 => 'Oktober',
                                            11 => 'November',
                                            12 => 'Desember',
                                        ];
                                    @endphp
                                    @foreach ($namabulan as $num => $nama)
                                        <option value="{{ sprintf('%02d', $num) }}" @selected((int) $bulan === $num)>
                                            {{ $nama }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-6 col-md-3">
                                <div class="filter-label">Tahun</div>
                                <select name="tahun" id="selTahun" class="form-select">
                                    @foreach (range(2026, 2022, -1) as $y)
                                        <option value="{{ $y }}" @selected((int) $tahun === $y)>{{ $y }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3">
                                <div class="filter-label">Jukir</div>
                                <select name="jukir_id" id="selJukir" class="form-select">
                                    <option value="">— Semua Jukir —</option>
                                    @foreach ($jukirList as $j)
                                        <option value="{{ $j->id }}" @selected($jukirId == $j->id)>
                                            {{ $j->no_absen }} – {{ $j->nama }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3">
                                <div class="filter-label">Koordinator</div>
                                <select name="koordinator" id="selKoordinator" class="form-select">
                                    <option value="">— Semua Koordinator —</option>
                                    @foreach ($koordinatorList as $k)
                                        <option value="{{ $k }}" @selected($koordinator === $k)>{{ $k }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2 d-flex gap-2">
                                <button type="submit" class="btn btn-primary flex-fill">
                                    <i class="ti ti-search me-1"></i>Tampilkan
                                </button>
                                <a href="{{ route('laporan.index') }}" class="btn btn-outline-secondary px-3" title="Reset Filter">
                                    <i class="ti ti-refresh"></i>
                                </a>
                            </div>

                        </div>
                    </form>
                </div>
            </div>

            {{-- ── Stat cards ───────────────────────────────── --}}
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="card stat-card h-100">
                        <div class="card-body d-flex align-items-center gap-3">
                            <div class="stat-icon" style="background:#e8f4fd">
                                <i class="ti ti-motorbike" style="color:#0d6efd"></i>
                            </div>
                            <div>
                                <div class="text-muted" style="font-size:.75rem;font-weight:600;text-transform:uppercase">Total R2</div>
                                <div class="fw-bold" style="font-size:1.35rem">Rp {{ number_format($totalR2, 0, ',', '.') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card stat-card h-100">
                        <div class="card-body d-flex align-items-center gap-3">
                            <div class="stat-icon" style="background:#fdecea">
                                <i class="ti ti-car" style="color:#dc3545"></i>
                            </div>
                            <div>
                                <div class="text-muted" style="font-size:.75rem;font-weight:600;text-transform:uppercase">Total R4</div>
                                <div class="fw-bold" style="font-size:1.35rem">Rp {{ number_format($totalR4, 0, ',', '.') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card stat-card h-100">
                        <div class="card-body d-flex align-items-center gap-3">
                            <div class="stat-icon" style="background:#e6f9f0">
                                <i class="ti ti-cash" style="color:#198754"></i>
                            </div>
                            <div>
                                <div class="text-muted" style="font-size:.75rem;font-weight:600;text-transform:uppercase">Total Gabungan</div>
                                <div class="fw-bold" style="font-size:1.35rem">Rp {{ number_format($totalR2 + $totalR4, 0, ',', '.') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── Tabel ────────────────────────────────────── --}}
            <div class="card table-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">Data Setoran</h5>
                        <small class="text-muted">{{ $setorans->count() }} baris ditemukan</small>
                    </div>
                    <a href="{{ route('laporan.export', request()->query()) }}" class="btn btn-success btn-sm px-3">
                        <i class="ti ti-file-spreadsheet me-1"></i>Export Excel
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table id="tblLaporan" class="table table-hover mb-0 w-100">
                            <thead>
                                <tr>
                                    <th class="px-3" style="width: 5%">No</th>
                                    <th>Tanggal</th>
                                    <th>No Absen</th>
                                    <th>Nama Jukir</th>
                                    <th>Koordinator</th>
                                    <th>Lokasi</th>
                                    <th class="text-end">R2 (Rp)</th>
                                    <th class="text-end">R4 (Rp)</th>
                                    <th class="text-end">Total (Rp)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($setorans as $i => $s)
                                    <tr>
                                        <td class="px-3 text-muted" style="font-size:.8rem">{{ $i + 1 }}</td>
                                        <td>
                                            <span class="badge bg-light text-dark border" style="font-size:.78rem">
                                                {{ $s->tanggal->format('d/m/Y') }}
                                            </span>
                                        </td>
                                        <td><code>{{ $s->jukir->no_absen ?? '-' }}</code></td>
                                        <td class="fw-semibold">{{ $s->jukir->nama ?? '-' }}</td>
                                        <td>{{ $s->jukir->koordinator_wasda ?? '-' }}</td>
                                        <td class="text-muted" style="font-size:.85rem">{{ $s->jukir->lokasi ?? '-' }}</td>
                                        <td class="text-end" style="color:#0d6efd;font-weight:600">
                                            {{ number_format($s->r2, 0, ',', '.') }}
                                        </td>
                                        <td class="text-end" style="color:#dc3545;font-weight:600">
                                            {{ number_format($s->r4, 0, ',', '.') }}
                                        </td>
                                        <td class="text-end fw-bold">
                                            {{ number_format($s->r2 + $s->r4, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            @if ($setorans->isNotEmpty())
                                <tfoot>
                                    <tr>
                                        <td colspan="6" class="text-end fw-bold px-3">Total</td>
                                        <td class="text-end fw-bold" style="color:#0d6efd">{{ number_format($totalR2, 0, ',', '.') }}</td>
                                        <td class="text-end fw-bold" style="color:#dc3545">{{ number_format($totalR4, 0, ',', '.') }}</td>
                                        <td class="text-end fw-bold">{{ number_format($totalR2 + $totalR4, 0, ',', '.') }}</td>
                                    </tr>
                                </tfoot>
                            @endif
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection

@push('js')
    {{-- jQuery & DataTables --}}
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    {{-- Choices.js --}}
    <script src="https://cdn.jsdelivr.net/npm/choices.js@10/public/assets/scripts/choices.min.js"></script>

    <script>
        $(document).ready(function() {

            ['selBulan', 'selTahun'].forEach(id => {
                const el = document.getElementById(id);
                if (el) {
                    new Choices(el, {
                        searchEnabled: false,
                        itemSelectText: '',
                        shouldSort: false,
                    });
                }
            });

            ['selJukir', 'selKoordinator'].forEach(id => {
                const el = document.getElementById(id);
                if (el) {
                    new Choices(el, {
                        searchEnabled: true,
                        itemSelectText: '',
                        shouldSort: false,
                        searchPlaceholderValue: 'Cari...',
                        noResultsText: 'Tidak ditemukan',
                    });
                }
            });


            $('#tblLaporan').DataTable({
                language: {
                    search: 'Cari:',
                    lengthMenu: 'Tampilkan _MENU_ baris',
                    info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',
                    infoEmpty: 'Menampilkan 0 sampai 0 dari 0 data',
                    zeroRecords: 'Tidak ada data setoran yang cocok',
                    paginate: {
                        first: '«',
                        last: '»',
                        next: '›',
                        previous: '‹',
                    },
                },
                pageLength: 25,
                columnDefs: [{
                        orderable: false,
                        targets: [0, 8]
                    },
                    {
                        searchable: false,
                        targets: [0]
                    }
                ],
                order: [
                    [1, 'asc']
                ]
            });
        });
    </script>
@endpush
