@extends('layouts.app')

@section('title', 'Konfigurasi Sheet')

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
                                <h2 class="mb-0">Konfigurasi Sheet</h2>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Daftar Sheet</h5>
                            <div class="d-flex gap-2">
                                {{-- Tombol Sync --}}
                                <form action="{{ route('config.sync') }}" method="POST" onsubmit="return confirm('Sync semua sheet sekarang? Proses ini bisa memakan waktu.')">
                                    @csrf
                                    <button type="submit" class="btn btn-success btn-sm">
                                        <i class="ti ti-refresh"></i> Sync Sekarang
                                    </button>
                                </form>
                                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambah">
                                    <i class="ti ti-plus"></i> Tambah Sheet
                                </button>
                            </div>
                        </div>
                        <div class="card-body">

                            @if (session('success'))
                                <div class="alert alert-success alert-dismissible fade show">
                                    {{ session('success') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            @endif

                            @if (session('error'))
                                <div class="alert alert-danger alert-dismissible fade show">
                                    {{ session('error') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            @endif

                            @if ($errors->any())
                                <div class="alert alert-danger alert-dismissible fade show">
                                    {{ $errors->first() }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            @endif

                            <div class="table-responsive">
                                <table id="tabelConfig" class="table table-hover table-bordered align-middle w-100">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Spreadsheet ID</th>
                                            <th>GID</th>
                                            <th>Bulan</th>
                                            <th>Tahun</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($configs as $i => $cfg)
                                            <tr>
                                                <td>{{ $i + 1 }}</td>
                                                <td><code>{{ $cfg->spreadsheet_id }}</code></td>
                                                <td><code>{{ $cfg->gid }}</code></td>
                                                <td>{{ \Carbon\Carbon::create()->month($cfg->bulan)->translatedFormat('F') }}</td>
                                                <td>{{ $cfg->tahun }}</td>
                                                <td>
                                                    <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#modalEdit" data-id="{{ $cfg->id }}" data-spreadsheet_id="{{ $cfg->spreadsheet_id }}" data-gid="{{ $cfg->gid }}" data-bulan="{{ $cfg->bulan }}" data-tahun="{{ $cfg->tahun }}">
                                                        <i class="ti ti-pencil"></i> Edit
                                                    </button>
                                                    <form action="{{ route('config.destroy', $cfg) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus konfigurasi ini?')">
                                                        @csrf @method('DELETE')
                                                        <button class="btn btn-danger btn-sm">
                                                            <i class="ti ti-trash"></i> Hapus
                                                        </button>
                                                    </form>
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

    {{-- Modal Tambah --}}
    <div class="modal fade" id="modalTambah" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('config.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Tambah Sheet</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Spreadsheet ID</label>
                            <input type="text" name="spreadsheet_id" class="form-control" required placeholder="1gfXbVDS367atZP8H2UcC4hXH_5nkG928BsXxJj0PLrE">
                            <div class="form-text">Ambil dari URL Google Sheets setelah /d/</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">GID</label>
                            <input type="text" name="gid" class="form-control" required placeholder="1958663128">
                            <div class="form-text">Ambil dari URL Google Sheets setelah #gid=</div>
                        </div>
                        <div class="row">
                            <div class="col-6 mb-3">
                                <label class="form-label">Bulan</label>
                                <select name="bulan" class="form-select" required>
                                    @foreach (range(1, 12) as $b)
                                        <option value="{{ $b }}">{{ \Carbon\Carbon::create()->month($b)->translatedFormat('F') }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label">Tahun</label>
                                <input type="number" name="tahun" class="form-control" value="{{ date('Y') }}" min="2020" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Edit --}}
    <div class="modal fade" id="modalEdit" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="formEdit" method="POST">
                    @csrf @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Sheet</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Spreadsheet ID</label>
                            <input type="text" name="spreadsheet_id" id="edit_spreadsheet_id" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">GID</label>
                            <input type="text" name="gid" id="edit_gid" class="form-control" required>
                        </div>
                        <div class="row">
                            <div class="col-6 mb-3">
                                <label class="form-label">Bulan</label>
                                <select name="bulan" id="edit_bulan" class="form-select" required>
                                    @foreach (range(1, 12) as $b)
                                        <option value="{{ $b }}">{{ \Carbon\Carbon::create()->month($b)->translatedFormat('F') }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label">Tahun</label>
                                <input type="number" name="tahun" id="edit_tahun" class="form-control" min="2020" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-warning">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $('#tabelConfig').DataTable({
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/id.json'
            },
            columnDefs: [{
                orderable: false,
                targets: 5
            }]
        });

        document.getElementById('modalEdit').addEventListener('show.bs.modal', function(e) {
            const btn = e.relatedTarget;
            const form = document.getElementById('formEdit');

            form.action = `/config/${btn.dataset.id}`;
            document.getElementById('edit_spreadsheet_id').value = btn.dataset.spreadsheet_id;
            document.getElementById('edit_gid').value = btn.dataset.gid;
            document.getElementById('edit_bulan').value = btn.dataset.bulan;
            document.getElementById('edit_tahun').value = btn.dataset.tahun;
        });
    </script>
@endpush
