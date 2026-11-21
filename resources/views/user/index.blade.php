@extends('layouts.app')

@section('title', 'Kelola User')

@push('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
@endpush

@section('content')
    <div class="pc-container">
        <div class="pc-content">
            <div class="page-header">
                <div class="page-block">
                    <h2 class="mb-0">Kelola User</h2>
                </div>
            </div>

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show">
                    {{ $errors->first() }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            {{-- Filter bulan/tahun --}}
            <form method="GET" class="row g-2 mb-3">
                <div class="col-auto">
                    <select name="bulan" class="form-select form-select-sm">
                        @foreach (range(1, 12) as $b)
                            <option value="{{ $b }}" @selected($b == $bulan)>
                                {{ DateTime::createFromFormat('!m', $b)->format('F') }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto">
                    <input type="number" name="tahun" value="{{ $tahun }}" class="form-control form-control-sm" style="width:90px">
                </div>
                <div class="col-auto">
                    <button class="btn btn-sm btn-secondary">Filter</button>
                </div>
            </form>

            <div class="row">
                {{-- Kiri: Daftar User --}}
                <div class="col-lg-7">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Daftar User</h5>
                            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambah">
                                <i class="ti ti-plus"></i> Tambah User
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="tabelUser" class="table table-hover table-bordered align-middle w-100">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Nama</th>
                                            <th>No. Absen</th>
                                            <th>Username</th>
                                            <th>Role</th>
                                            <th>Jukir Assigned</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($users as $i => $user)
                                            <tr>
                                                <td>{{ $i + 1 }}</td>
                                                <td>{{ $user->nama }}</td>
                                                <td>{{ $user->absen }}</td>
                                                <td>{{ $user->username }}</td>
                                                <td>
                                                    <span class="badge bg-{{ $user->role === 'admin' ? 'danger' : 'primary' }}">
                                                        {{ ucfirst($user->role) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    @if ($user->jukirs->isNotEmpty())
                                                        @foreach ($user->jukirs as $j)
                                                            <span class="badge bg-light text-dark border me-1">
                                                                {{ $j->no_absen }} — {{ $j->nama }}
                                                                <a href="#" class="text-danger ms-1" onclick="event.preventDefault(); document.getElementById('detach-{{ $j->id }}').submit();" title="Cabut">×</a>
                                                                <form id="detach-{{ $j->id }}" action="{{ route('user.detach', $j) }}" method="POST" class="d-none">
                                                                    @csrf @method('DELETE')
                                                                </form>
                                                            </span>
                                                        @endforeach
                                                    @else
                                                        <span class="text-muted small">—</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#modalEdit" data-id="{{ $user->id }}" data-nama="{{ $user->nama }}" data-absen="{{ $user->absen }}" data-username="{{ $user->username }}" data-role="{{ $user->role }}">
                                                        <i class="ti ti-pencil"></i>
                                                    </button>
                                                    <form action="{{ route('user.destroy', $user) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus user {{ $user->nama }}?')">
                                                        @csrf @method('DELETE')
                                                        <button class="btn btn-danger btn-sm"><i class="ti ti-trash"></i></button>
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

                {{-- Kanan: Assign Jukir --}}
                <div class="col-lg-5">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Jukir Belum Di-assign</h5>
                        </div>
                        <div class="card-body">
                            @if ($unmapped->isEmpty())
                                <p class="text-muted small">Semua jukir sudah di-assign.</p>
                            @else
                                <form action="{{ route('user.assign') }}" method="POST">
                                    @csrf
                                    <div class="mb-3">
                                        <label class="form-label">Assign ke User</label>
                                        <select name="user_id" class="form-select form-select-sm" required>
                                            <option value="">— Pilih User —</option>
                                            @foreach ($users as $u)
                                                <option value="{{ $u->id }}">{{ $u->nama }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Pilih Jukir</label>
                                        <div style="max-height:300px;overflow-y:auto" class="border rounded p-2">
                                            @foreach ($unmapped as $j)
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="jukir_ids[]" value="{{ $j->id }}" id="jk{{ $j->id }}">
                                                    <label class="form-check-label small" for="jk{{ $j->id }}">
                                                        {{ $j->no_absen }} — {{ $j->nama }}
                                                        <span class="text-muted">({{ $j->lokasi }})</span>
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    <button class="btn btn-primary btn-sm w-100">Assign</button>
                                </form>
                            @endif
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
                <form action="{{ route('user.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Tambah User</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nama</label>
                            <input type="text" name="nama" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">No. Absen</label>
                            <input type="text" name="absen" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" name="username" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Role</label>
                            <select name="role" class="form-select" required>
                                <option value="jukir">Jukir</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required minlength="6">
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
                        <h5 class="modal-title">Edit User</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nama</label>
                            <input type="text" name="nama" id="edit_nama" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">No. Absen</label>
                            <input type="text" name="absen" id="edit_absen" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" name="username" id="edit_username" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Role</label>
                            <select name="role" id="edit_role" class="form-select" required>
                                <option value="jukir">Jukir</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password <small class="text-muted">(kosongkan jika tidak diubah)</small></label>
                            <input type="password" name="password" class="form-control" minlength="6">
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
        $('#tabelUser').DataTable({
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/id.json'
            },
            columnDefs: [{
                orderable: false,
                targets: [5, 6]
            }]
        });

        document.getElementById('modalEdit').addEventListener('show.bs.modal', function(e) {
            const btn = e.relatedTarget;
            const form = document.getElementById('formEdit');
            form.action = `/user/${btn.dataset.id}`;
            document.getElementById('edit_nama').value = btn.dataset.nama;
            document.getElementById('edit_absen').value = btn.dataset.absen;
            document.getElementById('edit_username').value = btn.dataset.username;
            document.getElementById('edit_role').value = btn.dataset.role;
        });
    </script>
@endpush
