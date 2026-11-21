{{-- resources/views/lokasi-parkir/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Lokasi Parkir')

@push('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
@endpush

@section('content')
    <div class="pc-container">
        <div class="pc-content">
            <div class="page-header">
                <div class="page-block">
                    <div class="row align-items-center">
                        <div class="col-md-12">
                            <div class="page-header-title">
                                <h2 class="mb-0">Lokasi Parkir</h2>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Peta --}}
            <div class="row mb-3">
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Peta Lokasi Jukir</h5>
                        </div>
                        <div class="card-body p-0">
                            <div id="map" style="height: 400px; border-radius: 0 0 .5rem .5rem;"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tabel --}}
            <div class="row">
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Daftar Lokasi Parkir</h5>
                            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambah">
                                <i class="ti ti-plus"></i> Tambah Lokasi
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
                                <table id="tabelLokasi" class="table table-hover table-bordered align-middle w-100">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Nama Jukir</th>
                                            <th>Nama Lokasi</th>
                                            <th>Latitude</th>
                                            <th>Longitude</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($lokasis as $i => $l)
                                            <tr>
                                                <td>{{ $i + 1 }}</td>
                                                <td>{{ $l->nama_jukir }}</td>
                                                <td>{{ $l->nama_lokasi ?? '-' }}</td>
                                                <td><code>{{ $l->latitude }}</code></td>
                                                <td><code>{{ $l->longitude }}</code></td>
                                                <td>
                                                    <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#modalEdit" data-id="{{ $l->id }}" data-nama-jukir="{{ $l->nama_jukir }}" data-nama-lokasi="{{ $l->nama_lokasi }}" data-lat="{{ $l->latitude }}" data-lng="{{ $l->longitude }}">
                                                        <i class="ti ti-pencil"></i> Edit
                                                    </button>
                                                    <form action="{{ route('lokasi-parkir.destroy', $l) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus lokasi ini?')">
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
                <form action="{{ route('lokasi-parkir.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Tambah Lokasi Parkir</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row mb-3">
                            <div class="col-6">
                                <label class="form-label">Nama Jukir <span class="text-danger">*</span></label>
                                <input type="text" name="nama_jukir" class="form-control" required placeholder="cth: Budi Santoso">
                            </div>
                            <div class="col-6">
                                <label class="form-label">Nama Lokasi</label>
                                <input type="text" name="nama_lokasi" class="form-control" placeholder="cth: Parkir Alun-alun">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-6">
                                <label class="form-label">Latitude <span class="text-danger">*</span></label>
                                <input type="text" name="latitude" id="tambah_lat" class="form-control" required readonly placeholder="Klik peta di bawah...">
                            </div>
                            <div class="col-6">
                                <label class="form-label">Longitude <span class="text-danger">*</span></label>
                                <input type="text" name="longitude" id="tambah_lng" class="form-control" required readonly placeholder="Klik peta di bawah...">
                            </div>
                        </div>
                        <div class="form-text mb-2"><i class="ti ti-map-pin"></i> Klik titik pada peta untuk mengisi koordinat.</div>
                        <div id="mapTambah" style="height: 300px; border-radius: .5rem; border: 1px solid #dee2e6;"></div>
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
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="formEdit" method="POST">
                    @csrf @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Lokasi Parkir</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row mb-3">
                            <div class="col-6">
                                <label class="form-label">Nama Jukir <span class="text-danger">*</span></label>
                                <input type="text" name="nama_jukir" id="edit_nama_jukir" class="form-control" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label">Nama Lokasi</label>
                                <input type="text" name="nama_lokasi" id="edit_nama_lokasi" class="form-control">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-6">
                                <label class="form-label">Latitude <span class="text-danger">*</span></label>
                                <input type="text" name="latitude" id="edit_lat" class="form-control" required readonly>
                            </div>
                            <div class="col-6">
                                <label class="form-label">Longitude <span class="text-danger">*</span></label>
                                <input type="text" name="longitude" id="edit_lng" class="form-control" required readonly>
                            </div>
                        </div>
                        <div class="form-text mb-2"><i class="ti ti-map-pin"></i> Klik titik pada peta untuk memindahkan koordinat.</div>
                        <div id="mapEdit" style="height: 300px; border-radius: .5rem; border: 1px solid #dee2e6;"></div>
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
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        // ── DataTable ──────────────────────────────────────────────
        $('#tabelLokasi').DataTable({
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/id.json'
            },
            columnDefs: [{
                orderable: false,
                targets: 5
            }]
        });

        // ── Peta Utama (read-only, tampil semua marker) ────────────
        const mapUtama = L.map('map').setView([-7.7543, 113.2159], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap'
        }).addTo(mapUtama);

        @foreach ($lokasis as $l)
            @if ($l->latitude && $l->longitude)
                L.marker([{{ $l->latitude }}, {{ $l->longitude }}])
                    .addTo(mapUtama)
                    .bindPopup('<strong>{{ e($l->nama_jukir) }}</strong><br>{{ e($l->nama_lokasi ?? '-') }}');
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
                        '<br><small class="text-muted">{{ e($r->keterangan ?? '-') }}</small>'
                    );
            @endif
        @endforeach

        // ── Layer info: Zona Parkir (read-only) ────────────────────
        @foreach ($zonas as $z)
            @if (!empty($z->koordinat) && count($z->koordinat) >= 2)
                L.polyline({!! json_encode($z->koordinat) !!}, {
                        color: '{{ $z->status === 'boleh' ? '#0d6efd' : '#dc3545' }}',
                        weight: 8,
                        opacity: 0.7
                    }).addTo(mapUtama)
                    .bindPopup('<strong>{{ e($z->nama ?? 'Zona') }}</strong><br>{{ $z->status === 'boleh' ? '✅ Boleh Parkir' : '🚫 Dilarang Parkir' }}');
            @endif
        @endforeach

        // ── Helper: init map di dalam modal ───────────────────────
        // ponytail: map di modal di-init sekali saat modal pertama show, invalidateSize setelahnya
        function initModalMap(mapId, latInput, lngInput, defaultLat, defaultLng) {
            const m = L.map(mapId).setView([defaultLat, defaultLng], 15);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap'
            }).addTo(m);

            let marker = L.marker([defaultLat, defaultLng]).addTo(m);

            m.on('click', function(e) {
                const lat = e.latlng.lat.toFixed(7);
                const lng = e.latlng.lng.toFixed(7);
                document.getElementById(latInput).value = lat;
                document.getElementById(lngInput).value = lng;
                marker.setLatLng([lat, lng]);
            });

            return {
                map: m,
                marker
            };
        }

        // ── Modal Tambah ───────────────────────────────────────────
        let mapTambah = null;

        document.getElementById('modalTambah').addEventListener('shown.bs.modal', function() {
            if (mapTambah) {
                mapTambah.map.invalidateSize();
                return;
            }
            mapTambah = initModalMap('mapTambah', 'tambah_lat', 'tambah_lng', -7.7543, 113.2159);
            // Hapus marker default — user harus klik sendiri
            mapTambah.marker.remove();
            mapTambah.map.on('click', function(e) {
                if (!mapTambah.marker._map) mapTambah.marker.addTo(mapTambah.map);
            });
        });

        // Reset input koordinat saat modal Tambah ditutup
        document.getElementById('modalTambah').addEventListener('hidden.bs.modal', function() {
            document.getElementById('tambah_lat').value = '';
            document.getElementById('tambah_lng').value = '';
            if (mapTambah) mapTambah.marker.remove();
        });

        // ── Modal Edit ─────────────────────────────────────────────
        let mapEdit = null;

        document.getElementById('modalEdit').addEventListener('show.bs.modal', function(e) {
            const btn = e.relatedTarget;
            const id = btn.dataset.id;
            const namaJukir = btn.dataset.namaJukir;
            const namaLokasi = btn.dataset.namaLokasi ?? '';
            const lat = parseFloat(btn.dataset.lat);
            const lng = parseFloat(btn.dataset.lng);

            // Isi form
            document.getElementById('formEdit').action = `/lokasi-parkir/${id}`;
            document.getElementById('edit_nama_jukir').value = namaJukir;
            document.getElementById('edit_nama_lokasi').value = namaLokasi;
            document.getElementById('edit_lat').value = lat;
            document.getElementById('edit_lng').value = lng;

            // Simpan untuk dipakai di shown
            document.getElementById('modalEdit').dataset.lat = lat;
            document.getElementById('modalEdit').dataset.lng = lng;
        });

        document.getElementById('modalEdit').addEventListener('shown.bs.modal', function() {
            const lat = parseFloat(this.dataset.lat);
            const lng = parseFloat(this.dataset.lng);

            if (!mapEdit) {
                mapEdit = initModalMap('mapEdit', 'edit_lat', 'edit_lng', lat, lng);
            } else {
                mapEdit.map.invalidateSize();
                mapEdit.map.setView([lat, lng], 15);
                mapEdit.marker.setLatLng([lat, lng]);
                document.getElementById('edit_lat').value = lat;
                document.getElementById('edit_lng').value = lng;
            }
        });
    </script>
@endpush
