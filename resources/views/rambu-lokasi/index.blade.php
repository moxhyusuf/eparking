@extends('layouts.app')

@section('title', 'Rambu Lokasi')

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
                                <h2 class="mb-0">Rambu Lokasi</h2>
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
                            <h5 class="mb-0">Peta Rambu Parkir</h5>
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
                            <h5 class="mb-0">Daftar Rambu Lokasi</h5>
                            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambah">
                                <i class="ti ti-plus"></i> Tambah Rambu
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
                                <table id="tabelRambu" class="table table-hover table-bordered align-middle w-100">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Rambu</th>
                                            <th>Jenis Rambu</th>
                                            <th>Keterangan</th>
                                            <th>Latitude</th>
                                            <th>Longitude</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($rambus as $i => $r)
                                            <tr>
                                                <td>{{ $i + 1 }}</td>
                                                <td class="text-center">
                                                    <img src="{{ asset('img/rambu/' . $r->jenis_rambu . '.png') }}" alt="{{ $r->jenis_rambu }}" style="height:40px; width:40px; object-fit:contain;">
                                                </td>
                                                <td>{{ str_replace('_', ' ', ucfirst($r->jenis_rambu)) }}</td>
                                                <td>{{ $r->keterangan ?? '-' }}</td>
                                                <td><code>{{ $r->latitude }}</code></td>
                                                <td><code>{{ $r->longitude }}</code></td>
                                                <td>
                                                    <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#modalEdit" data-id="{{ $r->id }}" data-keterangan="{{ $r->keterangan }}" data-jenis-rambu="{{ $r->jenis_rambu }}" data-lat="{{ $r->latitude }}" data-lng="{{ $r->longitude }}">
                                                        <i class="ti ti-pencil"></i> Edit
                                                    </button>
                                                    <form action="{{ route('rambu-lokasi.destroy', $r) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus rambu ini?')">
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
                <form action="{{ route('rambu-lokasi.store') }}" method="POST" id="formTambah">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Tambah Rambu Lokasi</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row mb-3">
                            <div class="col-6">
                                <label class="form-label">Jenis Rambu <span class="text-danger">*</span></label>
                                <select name="jenis_rambu" id="tambah_jenis_rambu" class="form-select" required onchange="previewRambu('tambah')">
                                    <option value="parkir">Parkir</option>
                                    <option value="parkir_motor">Parkir Motor</option>
                                    <option value="parkir_mobil">Parkir Mobil</option>
                                    <option value="parkir_disabilitas">Parkir Disabilitas</option>
                                    <option value="dilarang_parkir">Dilarang Parkir</option>
                                </select>
                            </div>
                            <div class="col-3 d-flex align-items-end">
                                <img id="tambah_preview" src="{{ asset('img/rambu/parkir.png') }}" style="height:60px; width:60px; object-fit:contain; border:1px solid #dee2e6; border-radius:.375rem; padding:4px;">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Keterangan</label>
                            <input type="text" name="keterangan" class="form-control" placeholder="cth: Parkir khusus motor roda dua">
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
                <form method="POST" id="formEdit">
                    @csrf @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Rambu Lokasi</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row mb-3">
                            <div class="col-6">
                                <label class="form-label">Jenis Rambu <span class="text-danger">*</span></label>
                                <select name="jenis_rambu" id="edit_jenis_rambu" class="form-select" required onchange="previewRambu('edit')">
                                    <option value="parkir">Parkir</option>
                                    <option value="parkir_motor">Parkir Motor</option>
                                    <option value="parkir_mobil">Parkir Mobil</option>
                                    <option value="parkir_disabilitas">Parkir Disabilitas</option>
                                    <option value="dilarang_parkir">Dilarang Parkir</option>
                                </select>
                            </div>
                            <div class="col-3 d-flex align-items-end">
                                <img id="edit_preview" src="{{ asset('img/rambu/parkir.png') }}" style="height:60px; width:60px; object-fit:contain; border:1px solid #dee2e6; border-radius:.375rem; padding:4px;">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Keterangan</label>
                            <input type="text" name="keterangan" id="edit_keterangan" class="form-control">
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
        // ── Base URL asset untuk icon rambu ────────────────────────
        const assetBase = '{{ asset('img/rambu') }}';

        // ── DataTable ──────────────────────────────────────────────
        $('#tabelRambu').DataTable({
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/id.json'
            },
            columnDefs: [{
                orderable: false,
                targets: [1, 6]
            }]
        });

        // ── Leaflet icon per jenis rambu ───────────────────────────
        function rambuIcon(jenis) {
            return L.icon({
                iconUrl: `${assetBase}/${jenis}.png`,
                iconSize: [40, 46],
                iconAnchor: [18, 36],
                popupAnchor: [0, -36],
            });
        }

        // ── Peta Utama ─────────────────────────────────────────────
        const mapUtama = L.map('map').setView([-7.7543, 113.2159], 16);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap'
        }).addTo(mapUtama);

        // Ambang batas zoom, marker rambu baru tampil kalau zoom sudah sedekat ini
        const MIN_ZOOM_RAMBU = 15;
        const rambuLayer = L.layerGroup();

        @foreach ($rambus as $r)
            @if ($r->latitude && $r->longitude)
                L.marker(
                        [{{ $r->latitude }}, {{ $r->longitude }}], {
                            icon: rambuIcon('{{ $r->jenis_rambu }}')
                        }
                    ).addTo(rambuLayer)
                    .bindPopup(
                        '<img src="{{ asset('img/rambu/' . $r->jenis_rambu . '.png') }}" style="height:40px;display:block;margin:0 auto 6px">' +
                        '<strong>{{ str_replace('_', ' ', ucfirst($r->jenis_rambu)) }}</strong>' +
                        '<br>{{ e($r->keterangan ?? '-') }}'
                    );
            @endif
        @endforeach

        // Tampil/sembunyi layer rambu sesuai level zoom peta utama
        function toggleRambuLayer() {
            if (mapUtama.getZoom() >= MIN_ZOOM_RAMBU) {
                if (!mapUtama.hasLayer(rambuLayer)) rambuLayer.addTo(mapUtama);
            } else {
                if (mapUtama.hasLayer(rambuLayer)) mapUtama.removeLayer(rambuLayer);
            }
        }
        mapUtama.on('zoomend', toggleRambuLayer);
        toggleRambuLayer(); // cek kondisi awal pas peta pertama kali dimuat

        // ── Layer info: Lokasi Jukir (read-only) ───────────────────
        @foreach ($lokasis as $l)
            @if ($l->latitude && $l->longitude)
                L.marker([{{ $l->latitude }}, {{ $l->longitude }}])
                    .addTo(mapUtama)
                    .bindPopup('<strong>{{ e($l->nama_jukir) }}</strong><br><small>{{ e($l->nama_lokasi ?? '-') }}</small>');
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

        // ── Helper modal map (sama pola dengan lokasi-parkir) ──────
        // ponytail: init sekali, invalidateSize saat re-open
        function initModalMap(mapId, latId, lngId, defaultLat, defaultLng, jenis) {
            const m = L.map(mapId).setView([defaultLat, defaultLng], 16);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap'
            }).addTo(m);

            let marker = L.marker([defaultLat, defaultLng], {
                icon: rambuIcon(jenis)
            }).addTo(m);

            m.on('click', function(e) {
                const lat = e.latlng.lat.toFixed(7);
                const lng = e.latlng.lng.toFixed(7);
                document.getElementById(latId).value = lat;
                document.getElementById(lngId).value = lng;
                marker.setLatLng([lat, lng]);
            });

            return {
                map: m,
                marker
            };
        }

        // ── Preview gambar rambu saat select berubah ───────────────
        function previewRambu(prefix) {
            const jenis = document.getElementById(`${prefix}_jenis_rambu`).value;
            document.getElementById(`${prefix}_preview`).src = `${assetBase}/${jenis}.png`;
            // Update icon marker di modal map jika sudah init
            const m = prefix === 'tambah' ? mapTambah : mapEdit;
            if (m) m.marker.setIcon(rambuIcon(jenis));
        }

        // ── Modal Tambah ───────────────────────────────────────────
        let mapTambah = null;

        document.getElementById('modalTambah').addEventListener('shown.bs.modal', function() {
            if (mapTambah) {
                mapTambah.map.invalidateSize();
                return;
            }
            const jenis = document.getElementById('tambah_jenis_rambu').value;
            mapTambah = initModalMap('mapTambah', 'tambah_lat', 'tambah_lng', -7.7543, 113.2159, jenis);
            mapTambah.marker.remove(); // user harus klik sendiri
        });

        document.getElementById('modalTambah').addEventListener('hidden.bs.modal', function() {
            document.getElementById('tambah_lat').value = '';
            document.getElementById('tambah_lng').value = '';
            if (mapTambah) mapTambah.marker.remove();
            document.getElementById('formTambah').reset();
            document.getElementById('tambah_preview').src = `${assetBase}/parkir.png`;
        });

        // ── Modal Edit ─────────────────────────────────────────────
        let mapEdit = null;

        document.getElementById('modalEdit').addEventListener('show.bs.modal', function(e) {
            const btn = e.relatedTarget;
            document.getElementById('formEdit').action = `/rambu-lokasi/${btn.dataset.id}`;
            document.getElementById('edit_keterangan').value = btn.dataset.keterangan ?? '';
            document.getElementById('edit_jenis_rambu').value = btn.dataset.jenisRambu;
            document.getElementById('edit_lat').value = btn.dataset.lat;
            document.getElementById('edit_lng').value = btn.dataset.lng;
            document.getElementById('edit_preview').src = `${assetBase}/${btn.dataset.jenisRambu}.png`;

            this.dataset.lat = btn.dataset.lat;
            this.dataset.lng = btn.dataset.lng;
            this.dataset.jenis = btn.dataset.jenisRambu;
        });

        document.getElementById('modalEdit').addEventListener('shown.bs.modal', function() {
            const lat = parseFloat(this.dataset.lat);
            const lng = parseFloat(this.dataset.lng);
            const jenis = this.dataset.jenis;

            if (!mapEdit) {
                mapEdit = initModalMap('mapEdit', 'edit_lat', 'edit_lng', lat, lng, jenis);
            } else {
                mapEdit.map.invalidateSize();
                mapEdit.map.setView([lat, lng], 16);
                mapEdit.marker.setLatLng([lat, lng]);
                mapEdit.marker.setIcon(rambuIcon(jenis));
                document.getElementById('edit_lat').value = lat;
                document.getElementById('edit_lng').value = lng;
            }
        });
    </script>
@endpush
