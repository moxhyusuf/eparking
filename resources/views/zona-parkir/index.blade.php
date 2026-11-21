{{-- resources/views/zona-parkir/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Zona Parkir')

@push('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        .badge-boleh {
            background: #0d6efd;
        }

        .badge-dilarang {
            background: #dc3545;
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
                                <h2 class="mb-0">Zona Parkir</h2>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Peta --}}
            <div class="row mb-3">
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Peta Zona Parkir</h5>
                            <div class="d-flex gap-2 align-items-center small">
                                <span class="badge badge-boleh px-2 py-1">— Boleh Parkir</span>
                                <span class="badge badge-dilarang px-2 py-1">— Dilarang Parkir</span>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div id="map" style="height: 420px; border-radius: 0 0 .5rem .5rem;"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tabel --}}
            <div class="row">
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Daftar Zona Parkir</h5>
                            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambah">
                                <i class="ti ti-plus"></i> Tambah Zona
                            </button>
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
                                <table id="tabelZona" class="table table-hover table-bordered align-middle w-100">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Nama Zona</th>
                                            <th>Status</th>
                                            <th>Jumlah Titik</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($zonas as $i => $z)
                                            <tr>
                                                <td>{{ $i + 1 }}</td>
                                                <td>{{ $z->nama ?? '-' }}</td>
                                                <td>
                                                    @if ($z->status === 'boleh')
                                                        <span class="badge badge-boleh">Boleh Parkir</span>
                                                    @else
                                                        <span class="badge badge-dilarang">Dilarang Parkir</span>
                                                    @endif
                                                </td>
                                                <td>{{ count($z->koordinat) }} titik</td>
                                                <td>
                                                    <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#modalEdit" data-id="{{ $z->id }}" data-nama="{{ $z->nama }}" data-status="{{ $z->status }}" data-koordinat="{{ json_encode($z->koordinat) }}">
                                                        <i class="ti ti-pencil"></i> Edit
                                                    </button>
                                                    <form action="{{ route('zona-parkir.destroy', $z) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus zona ini?')">
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
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="{{ route('zona-parkir.store') }}" method="POST" id="formTambah">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Tambah Zona Parkir</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row mb-3">
                            <div class="col-6">
                                <label class="form-label">Nama Zona</label>
                                <input type="text" name="nama" class="form-control" placeholder="cth: Jl. Ahmad Yani (Utara)">
                            </div>
                            <div class="col-6">
                                <label class="form-label">Status <span class="text-danger">*</span></label>
                                <select name="status" class="form-select" required>
                                    <option value="boleh">Boleh Parkir</option>
                                    <option value="dilarang">Dilarang Parkir</option>
                                </select>
                            </div>
                        </div>
                        <input type="hidden" name="koordinat" id="tambah_koordinat">
                        <div class="form-text mb-2">
                            <i class="ti ti-route"></i>
                            Klik pada peta untuk menambah titik garis. Minimal 2 titik.
                            <button type="button" class="btn btn-outline-secondary btn-sm ms-2" onclick="resetTambahLine()">
                                <i class="ti ti-refresh"></i> Reset Garis
                            </button>
                        </div>
                        <div id="mapTambah" style="height: 320px; border-radius: .5rem; border: 1px solid #dee2e6;"></div>
                        <div id="tambah_info" class="form-text text-muted mt-1">Belum ada titik.</div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" id="btnSimpanTambah" disabled>Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Edit --}}
    <div class="modal fade" id="modalEdit" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" id="formEdit">
                    @csrf @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Zona Parkir</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row mb-3">
                            <div class="col-6">
                                <label class="form-label">Nama Zona</label>
                                <input type="text" name="nama" id="edit_nama" class="form-control">
                            </div>
                            <div class="col-6">
                                <label class="form-label">Status <span class="text-danger">*</span></label>
                                <select name="status" id="edit_status" class="form-select" required>
                                    <option value="boleh">Boleh Parkir</option>
                                    <option value="dilarang">Dilarang Parkir</option>
                                </select>
                            </div>
                        </div>
                        <input type="hidden" name="koordinat" id="edit_koordinat">
                        <div class="form-text mb-2">
                            <i class="ti ti-route"></i>
                            Klik pada peta untuk menambah/mengubah titik garis.
                            <button type="button" class="btn btn-outline-secondary btn-sm ms-2" onclick="resetEditLine()">
                                <i class="ti ti-refresh"></i> Reset Garis
                            </button>
                        </div>
                        <div id="mapEdit" style="height: 320px; border-radius: .5rem; border: 1px solid #dee2e6;"></div>
                        <div id="edit_info" class="form-text text-muted mt-1"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-warning" id="btnSimpanEdit">Update</button>
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
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        // ── DataTable ──────────────────────────────────────────────
        $('#tabelZona').DataTable({
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/id.json'
            },
            columnDefs: [{
                orderable: false,
                targets: 4
            }]
        });

        // ── Peta Utama (read-only) ─────────────────────────────────
        const mapUtama = L.map('map').setView([-7.7543, 113.2159], 16);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap'
        }).addTo(mapUtama);

        @foreach ($zonas as $z)
            @if (!empty($z->koordinat) && count($z->koordinat) >= 2)
                L.polyline(
                        {!! json_encode($z->koordinat) !!}, {
                            color: '{{ $z->status === 'boleh' ? '#0d6efd' : '#dc3545' }}',
                            weight: 8,
                            opacity: 0.8
                        }
                    ).addTo(mapUtama)
                    .bindPopup('<strong>{{ e($z->nama ?? 'Zona') }}</strong><br><span>{{ $z->status === 'boleh' ? '✅ Boleh Parkir' : '🚫 Dilarang Parkir' }}</span>');
            @endif
        @endforeach

        // ── Layer info: Lokasi Jukir (read-only) ───────────────────
        @foreach ($lokasis as $l)
            @if ($l->latitude && $l->longitude)
                L.marker([{{ $l->latitude }}, {{ $l->longitude }}])
                    .addTo(mapUtama)
                    .bindPopup('<strong>{{ e($l->nama_jukir) }}</strong><br><small>{{ e($l->nama_lokasi ?? '-') }}</small>');
            @endif
        @endforeach

        // ── Layer info: Rambu Lokasi (read-only) ───────────────────
        const assetBase = '{{ asset('img/rambu') }}';

        function rambuIcon(jenis) {
            return L.icon({
                iconUrl: `${assetBase}/${jenis}.png`,
                iconSize: [30, 30],
                iconAnchor: [15, 30],
                popupAnchor: [0, -30]
            });
        }
        @foreach ($rambus as $r)
            @if ($r->latitude && $r->longitude)
                L.marker([{{ $r->latitude }}, {{ $r->longitude }}], {
                        icon: rambuIcon('{{ $r->jenis_rambu }}')
                    })
                    .addTo(mapUtama)
                    .bindPopup(
                        '<img src="{{ asset('img/rambu/' . $r->jenis_rambu . '.png') }}" style="height:36px;display:block;margin:0 auto 4px">' +
                        '<strong>{{ str_replace('_', ' ', ucfirst($r->jenis_rambu)) }}</strong>' +
                        '<br><small>{{ e($r->keterangan ?? '-') }}</small>'
                    );
            @endif
        @endforeach

        // ── Helper: init modal map dengan polyline interaktif ──────
        // ponytail: polyline dibangun dari array points, hidden input di-sync setiap klik
        function initLineMap(mapId, infoId, hiddenId, btnId, initialPoints = []) {
            const m = L.map(mapId).setView([-7.7543, 113.2159], 15);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap'
            }).addTo(m);

            let points = [...initialPoints];
            let polyline = L.polyline(points, {
                color: '#0d6efd',
                weight: 8
            }).addTo(m);
            let markers = points.map(p => L.circleMarker(p, {
                radius: 5,
                color: '#0d6efd'
            }).addTo(m));

            if (points.length >= 2) m.fitBounds(polyline.getBounds());

            function sync() {
                document.getElementById(hiddenId).value = JSON.stringify(points);
                document.getElementById(infoId).textContent = `${points.length} titik terpilih.`;
                if (btnId) document.getElementById(btnId).disabled = points.length < 2;
            }

            m.on('click', function(e) {
                const pt = [parseFloat(e.latlng.lat.toFixed(7)), parseFloat(e.latlng.lng.toFixed(7))];
                points.push(pt);
                polyline.setLatLngs(points);
                markers.push(L.circleMarker(pt, {
                    radius: 5,
                    color: '#0d6efd'
                }).addTo(m));
                sync();
            });

            sync();

            return {
                map: m,
                reset() {
                    points = [];
                    polyline.setLatLngs([]);
                    markers.forEach(mk => mk.remove());
                    markers = [];
                    sync();
                },
                reload(newPoints) {
                    points = [...newPoints];
                    polyline.setLatLngs(points);
                    markers.forEach(mk => mk.remove());
                    markers = newPoints.map(p => L.circleMarker(p, {
                        radius: 5,
                        color: '#0d6efd'
                    }).addTo(m));
                    if (points.length >= 2) m.fitBounds(polyline.getBounds());
                    sync();
                }
            };
        }

        // ── Modal Tambah ───────────────────────────────────────────
        let mtambah = null;

        document.getElementById('modalTambah').addEventListener('shown.bs.modal', function() {
            if (mtambah) {
                mtambah.map.invalidateSize();
                return;
            }
            mtambah = initLineMap('mapTambah', 'tambah_info', 'tambah_koordinat', 'btnSimpanTambah');
        });

        document.getElementById('modalTambah').addEventListener('hidden.bs.modal', function() {
            if (mtambah) mtambah.reset();
            document.getElementById('formTambah').reset();
        });

        function resetTambahLine() {
            if (mtambah) mtambah.reset();
        }

        // ── Modal Edit ─────────────────────────────────────────────
        let medit = null;

        document.getElementById('modalEdit').addEventListener('show.bs.modal', function(e) {
            const btn = e.relatedTarget;
            document.getElementById('formEdit').action = `/zona-parkir/${btn.dataset.id}`;
            document.getElementById('edit_nama').value = btn.dataset.nama ?? '';
            document.getElementById('edit_status').value = btn.dataset.status;
            // simpan koordinat untuk dipakai di shown
            this.dataset.koordinat = btn.dataset.koordinat;
        });

        document.getElementById('modalEdit').addEventListener('shown.bs.modal', function() {
            const pts = JSON.parse(this.dataset.koordinat || '[]');

            if (!medit) {
                medit = initLineMap('mapEdit', 'edit_info', 'edit_koordinat', null, pts);
            } else {
                medit.map.invalidateSize();
                medit.reload(pts);
            }
        });

        function resetEditLine() {
            if (medit) medit.reset();
        }
    </script>
@endpush
