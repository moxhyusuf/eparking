@extends('layouts.app')

@section('title', 'Monitoring Jukir')

@push('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css">
@endpush

@section('content')
    <div class="pc-container">
        <div class="pc-content">
            <div class="page-header">
                <div class="page-block">
                    <div class="row align-items-center">
                        <div class="col-md-12">
                            <div class="page-header-title">
                                <h2 class="mb-0">Monitoring Jukir</h2>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Daftar Juru Parkir</h5>
                        </div>
                        <div class="card-body">

                            {{-- Filter --}}
                            <div class="row mb-3 g-2 align-items-end">
                                <div class="col-md-3">
                                    <label class="form-label mb-1 fw-semibold">Koordinator</label>
                                    <select id="filterKoordinator" class="form-select select2">
                                        <option value="">Semua Koordinator</option>
                                        @foreach ($jukirs->pluck('koordinator_wasda')->unique()->sort() as $val)
                                            <option value="{{ $val }}">{{ $val }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label mb-1 fw-semibold">Sub Koordinator</label>
                                    <select id="filterSubKoordinator" class="form-select select2">
                                        <option value="">Semua Sub Koordinator</option>
                                        @foreach ($jukirs->pluck('sub_koordinator_wasda')->unique()->sort() as $val)
                                            <option value="{{ $val }}">{{ $val }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label mb-1 fw-semibold">Lokasi</label>
                                    <select id="filterLokasi" class="form-select select2">
                                        <option value="">Semua Lokasi</option>
                                        @foreach ($jukirs->pluck('lokasi')->unique()->sort() as $val)
                                            <option value="{{ $val }}">{{ $val }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <button id="btnReset" class="btn btn-secondary w-100">Reset Filter</button>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table id="tabelJukir" class="table table-hover table-bordered align-middle w-100">
                                    <thead>
                                        <tr>
                                            <th>No. Absen</th>
                                            <th>Nama Jukir</th>
                                            <th>Koordinator</th>
                                            <th>Sub Koordinator</th>
                                            <th>Lokasi</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($jukirs as $jukir)
                                            <tr>
                                                <td>{{ $jukir->no_absen }}</td>
                                                <td>{{ $jukir->nama }}</td>
                                                <td>{{ $jukir->koordinator_wasda }}</td>
                                                <td>{{ $jukir->sub_koordinator_wasda }}</td>
                                                <td>{{ $jukir->lokasi }}</td>
                                                <td>
                                                    <a href="{{ route('monitoring.detail', $jukir->id) }}" class="btn btn-info btn-sm text-white">
                                                        <i class="ti ti-eye"></i> Detail
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
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
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(function() {
            const table = $('#tabelJukir').DataTable({
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/id.json'
                },
                columnDefs: [{
                    orderable: false,
                    targets: 5
                }]
            });

            $('.select2').select2({
                theme: 'bootstrap-5',
                allowClear: true,
                width: '100%'
            });

            $.fn.dataTable.ext.search.push(function(settings, data) {
                const koordinator = $('#filterKoordinator').val();
                const sub = $('#filterSubKoordinator').val();
                const lokasi = $('#filterLokasi').val();

                if (koordinator && data[2] !== koordinator) return false;
                if (sub && data[3] !== sub) return false;
                if (lokasi && data[4] !== lokasi) return false;

                return true;
            });

            $('#filterKoordinator, #filterSubKoordinator, #filterLokasi').on('change', function() {
                table.draw();
            });

            $('#btnReset').on('click', function() {
                $('#filterKoordinator, #filterSubKoordinator, #filterLokasi').val(null).trigger('change');
            });
        });
    </script>
@endpush
