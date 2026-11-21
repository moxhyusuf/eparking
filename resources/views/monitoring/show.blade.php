@extends('layouts.app')

@section('title', 'Detail Setoran - ' . $jukir->nama)

@push('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
@endpush

@section('content')
    <div class="pc-container">
        <div class="pc-content">

            <div class="page-header">
                <div class="page-block">
                    <div class="row align-items-center">
                        <div class="col-md-12">
                            <div class="page-header-title">
                                <h2 class="mb-0">Detail Setoran</h2>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Info Jukir --}}
            <div class="row mb-3">
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Informasi Juru Parkir</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-sm table-borderless mb-0">
                                        <tr>
                                            <th width="160">Nama</th>
                                            <td>{{ $jukir->nama }}</td>
                                        </tr>
                                        <tr>
                                            <th>No. Absen</th>
                                            <td>{{ $jukir->no_absen }}</td>
                                        </tr>
                                        <tr>
                                            <th>Lokasi</th>
                                            <td>{{ $jukir->lokasi }}</td>
                                        </tr>
                                        <tr>
                                            <th>Bulan</th>
                                            <td>
                                                {{ \Carbon\Carbon::createFromDate($jukir->tahun, $jukir->bulan, 1)->translatedFormat('F Y') }}
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-sm table-borderless mb-0">
                                        <tr>
                                            <th width="160">Koordinator</th>
                                            <td>{{ $jukir->koordinator_wasda }}</td>
                                        </tr>
                                        <tr>
                                            <th>Sub Koordinator</th>
                                            <td>{{ $jukir->sub_koordinator_wasda }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Summary Pendapatan --}}
            <div class="row mb-3">
                <div class="col-md-4">
                    <div class="card bg-primary text-white mb-0">
                        <div class="card-body py-3">
                            <div class="small mb-1">Total R2</div>
                            <div class="fw-bold fs-5">Rp {{ number_format($totalR2, 0, ',', '.') }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-success text-white mb-0">
                        <div class="card-body py-3">
                            <div class="small mb-1">Total R4</div>
                            <div class="fw-bold fs-5">Rp {{ number_format($totalR4, 0, ',', '.') }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-warning text-white mb-0">
                        <div class="card-body py-3">
                            <div class="small mb-1">Total Pendapatan</div>
                            <div class="fw-bold fs-5">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tabel Setoran --}}
            <div class="row">
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <h5 class="mb-0">Riwayat Setoran</h5>
                            @if (Auth::user()->role !== 'jukir')
                                <a href="{{ route('monitoring.index') }}" class="btn btn-secondary btn-sm">
                                    <i class="ti ti-arrow-left"></i> Kembali
                                </a>
                            @endif
                        </div>
                        <div class="card-body">
                            @if ($setorans->isEmpty())
                                <div class="alert alert-info mb-0">
                                    Tidak ada data setoran untuk jukir ini.
                                </div>
                            @else
                                <div class="table-responsive">
                                    <table id="tabelSetoran" class="table table-hover table-bordered align-middle w-100">
                                        <thead class="table-dark">
                                            <tr>
                                                <th width="50">#</th>
                                                <th>Tanggal</th>
                                                <th>R2</th>
                                                <th>R4</th>
                                                <th>Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($setorans as $i => $s)
                                                <tr>
                                                    <td>{{ $i + 1 }}</td>
                                                    <td>{{ \Carbon\Carbon::parse($s->tanggal)->translatedFormat('d F Y') }}</td>
                                                    <td>Rp {{ number_format($s->r2, 0, ',', '.') }}</td>
                                                    <td>Rp {{ number_format($s->r4, 0, ',', '.') }}</td>
                                                    <td class="fw-semibold">Rp {{ number_format($s->r2 + $s->r4, 0, ',', '.') }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot class="table-secondary fw-bold">
                                            <tr>
                                                <td colspan="2" class="text-end">Total</td>
                                                <td>Rp {{ number_format($totalR2, 0, ',', '.') }}</td>
                                                <td>Rp {{ number_format($totalR4, 0, ',', '.') }}</td>
                                                <td>Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection

@push('js')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    @if ($setorans->isNotEmpty())
        <script>
            $(function() {
                $('#tabelSetoran').DataTable({
                    language: {
                        url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/id.json'
                    },
                    paging: false,
                    searching: false,
                    info: false,
                    columnDefs: [{
                        orderable: false,
                        targets: 0
                    }]
                });
            });
        </script>
    @endif
@endpush
