
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan Pengiriman</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            margin: 0;
            padding: 30px;
            color: #333;
        }

        .container {
            max-width: 850px;
            margin: 20px auto;
        }

        .card {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        h2 {
            margin-top: 0;
            margin-bottom: 10px;
        }

        .description {
            color: #777;
            margin-bottom: 25px;
            line-height: 1.5;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            font-weight: bold;
            margin-bottom: 8px;
        }

        input, textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 7px;
            font-size: 14px;
        }

        input:focus, textarea:focus {
            outline: none;
            border-color: #2563eb;
        }

        textarea {
            min-height: 110px;
            resize: vertical;
        }
        select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 7px;
            font-size: 14px;
            background: white;
        }

        select:disabled {
            background: #f3f4f6;
            cursor: not-allowed;
        }
        .info {
            font-size: 12px;
            color: #777;
            margin-top: 6px;
        }

        .success {
            background: #dcfce7;
            color: #166534;
            padding: 13px;
            border-radius: 7px;
            margin-bottom: 20px;
        }

        .error {
            background: #fee2e2;
            color: #991b1b;
            padding: 13px;
            border-radius: 7px;
            margin-bottom: 20px;
        }

        .button {
            background: #2563eb;
            color: white;
            border: none;
            padding: 12px 22px;
            border-radius: 7px;
            cursor: pointer;
            font-size: 14px;
        }

        .button:hover {
            background: #1d4ed8;
        }

        .button:disabled {
            background: #93a9d5;
            cursor: not-allowed;
        }

        .back {
            display: inline-block;
            margin-bottom: 20px;
            color: #2563eb;
            text-decoration: none;
        }

        .search-box {
            display: flex;
            gap: 10px;
        }

        .search-box input {
            flex: 1;
            min-width: 0;
        }

        .search-results {
            margin-top: 12px;
            border: 1px solid #e5e7eb;
            border-radius: 7px;
            overflow: hidden;
            display: none;
        }

        .result-item {
            display: block;
            width: 100%;
            padding: 13px;
            background: white;
            border: none;
            border-bottom: 1px solid #eee;
            text-align: left;
            cursor: pointer;
            font-size: 13px;
            line-height: 1.5;
        }

        .result-item:last-child {
            border-bottom: none;
        }

        .result-item:hover {
            background: #eff6ff;
        }

        .search-message {
            font-size: 13px;
            color: #666;
            margin-top: 10px;
        }

        .selected-info {
            background: #eff6ff;
            padding: 12px;
            border-radius: 7px;
            color: #1e40af;
            font-size: 13px;
            margin-top: 10px;
            display: none;
        }

        @media (max-width: 600px) {
            body {
                padding: 15px;
            }

            .card {
                padding: 20px;
            }

            .search-box {
                flex-direction: column;
            }
        }
    </style>
</head>

<body>

<div class="container">

    <a href="{{ route(Auth::user()->role . '.index') }}" class="back">
        ← Kembali ke Dashboard
    </a>

    <div class="card">

        <h2>Pengaturan Pengiriman</h2>

        <p class="description">
            Kelola lokasi asal pengiriman toko yang akan digunakan
            dalam pengaturan layanan pengiriman.
        </p>

        @if(session('success'))
            <div class="success">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="error">
                <strong>Terjadi kesalahan:</strong>
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        {{-- Pilihan wilayah asal pengiriman --}}

        <div class="form-group">
            <label for="provinsi">Provinsi</label>
            <select id="provinsi">
                <option value="">Memuat provinsi...</option>
            </select>
        </div>

        <div class="form-group">
            <label for="kota">Kabupaten/Kota</label>
            <select id="kota" disabled>
                <option value="">Pilih provinsi terlebih dahulu</option>
            </select>
        </div>

        <div class="form-group">
            <label for="kecamatan">Kecamatan</label>
            <select id="kecamatan" disabled>
                <option value="">Pilih kabupaten/kota terlebih dahulu</option>
            </select>
        </div>

        <div class="form-group">
            <label for="desa">Desa/Kelurahan</label>
            <select id="desa" disabled>
                <option value="">Pilih kecamatan terlebih dahulu</option>
            </select>
        </div>

        <p class="info">
            Pilih wilayah sesuai alamat asal pengiriman toko.
        </p>

        <hr style="border: 0; border-top: 1px solid #eee; margin: 25px 0;">
        {{-- Pencarian lokasi RajaOngkir --}}
        <div class="form-group">

            <label for="search_lokasi">
                Cari Lokasi Asal Pengiriman
            </label>

            <div class="search-box">
                <input
                    type="text"
                    id="search_lokasi"
                    placeholder="Lokasi akan terisi otomatis dari pilihan wilayah"
                >

                <button
                    type="button"
                    class="button"
                    id="btnCari"
                >
                    Cari Lokasi
                </button>
            </div>

            <p class="info">
                Lokasi akan terisi otomatis setelah memilih desa.
                Klik Cari Lokasi untuk mendapatkan Origin ID RajaOngkir.
            </p>

            <div id="searchMessage" class="search-message"></div>

            <div id="searchResults" class="search-results"></div>

            <div id="selectedInfo" class="selected-info">
                Lokasi berhasil dipilih.
            </div>

        </div>

        <hr style="border: 0; border-top: 1px solid #eee; margin: 25px 0;">

        {{-- Form penyimpanan --}}
        <form
            action="{{ route(Auth::user()->role . '.pengaturan-pengiriman.update') }}"
            method="POST"
        >

            @csrf
            @method('PUT')

            <div class="form-group">

                <label for="origin_id">Origin ID</label>

                <input
                    type="text"
                    id="origin_id"
                    name="origin_id"
                    value="{{ old('origin_id', $pengaturan->origin_id) }}"
                    placeholder="Origin ID akan terisi otomatis"
                    required
                >

                <p class="info">
                    Terisi otomatis setelah memilih hasil pencarian RajaOngkir.
                </p>

            </div>

            <div class="form-group">

                <label for="alamat_asal">
                    Alamat Asal Pengiriman
                </label>

                <textarea
                    id="alamat_asal"
                    name="alamat_asal"
                    placeholder="Alamat asal pengiriman"
                    required
                >{{ old('alamat_asal', $pengaturan->alamat_asal) }}</textarea>

                <p class="info">
                    Periksa kembali alamat sebelum menyimpan pengaturan.
                </p>

            </div>

            <button type="submit" class="button">
                Simpan Pengaturan
            </button>

        </form>

    </div>

</div>


<script>
    // URL untuk mengambil data wilayah
    const provinsiUrl = @json(route(Auth::user()->role . '.wilayah.provinsi'));

    const kotaUrl = @json(route(Auth::user()->role . '.wilayah.kota', ['id_provinsi' => '__ID__']));
    const kecamatanUrl = @json(route(Auth::user()->role . '.wilayah.kecamatan', ['id_kota' => '__ID__']));
    const desaUrl = @json(route(Auth::user()->role . '.wilayah.desa', ['id_kecamatan' => '__ID__']));

    // URL pencarian RajaOngkir
    const searchUrl = @json(route(Auth::user()->role . '.rajaongkir.search'));

    // Elemen dropdown wilayah
    const provinsiSelect = document.getElementById('provinsi');
    const kotaSelect = document.getElementById('kota');
    const kecamatanSelect = document.getElementById('kecamatan');
    const desaSelect = document.getElementById('desa');

    // Elemen pencarian RajaOngkir
    const searchInput = document.getElementById('search_lokasi');
    const searchButton = document.getElementById('btnCari');
    const searchResults = document.getElementById('searchResults');
    const searchMessage = document.getElementById('searchMessage');
    const selectedInfo = document.getElementById('selectedInfo');

    const originInput = document.getElementById('origin_id');
    const addressInput = document.getElementById('alamat_asal');

    // Fungsi memuat data wilayah
    async function loadWilayah(url, select, idField, nameField, placeholder) {
        select.innerHTML = `<option value="">Memuat data...</option>`;
        select.disabled = true;

        try {
            const response = await fetch(url, {
                headers: {
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) {
                throw new Error('Gagal mengambil data wilayah.');
            }

            const data = await response.json();

            select.innerHTML = `<option value="">${placeholder}</option>`;

            data.forEach(function(item) {
                const option = document.createElement('option');

                option.value = item[idField];
                option.textContent = item[nameField];

                select.appendChild(option);
            });

            select.disabled = false;

        } catch (error) {
            console.error('Kesalahan memuat wilayah:', error);

            select.innerHTML = `<option value="">Gagal memuat data</option>`;
            select.disabled = true;
        }
    }

    // Mengosongkan dropdown berikutnya
    function resetSelect(select, placeholder) {
        select.innerHTML = `<option value="">${placeholder}</option>`;
        select.disabled = true;
    }

    // Memuat provinsi saat halaman dibuka
    loadWilayah(
        provinsiUrl,
        provinsiSelect,
        'id_provinsi',
        'nama_provinsi',
        'Pilih Provinsi'
    );

    // Ketika provinsi dipilih
    provinsiSelect.addEventListener('change', function() {
        const idProvinsi = this.value;

        resetSelect(kotaSelect, 'Pilih Kabupaten/Kota terlebih dahulu');
        resetSelect(kecamatanSelect, 'Pilih Kecamatan terlebih dahulu');
        resetSelect(desaSelect, 'Pilih Desa/Kelurahan');

        if (!idProvinsi) return;

        loadWilayah(
            kotaUrl.replace('__ID__', idProvinsi),
            kotaSelect,
            'id_kota',
            'nama_kota',
            'Pilih Kabupaten/Kota'
        );
    });

    // Ketika kabupaten/kota dipilih
    kotaSelect.addEventListener('change', function() {
        const idKota = this.value;

        resetSelect(kecamatanSelect, 'Pilih Kecamatan terlebih dahulu');
        resetSelect(desaSelect, 'Pilih Desa/Kelurahan');

        if (!idKota) return;

        loadWilayah(
            kecamatanUrl.replace('__ID__', idKota),
            kecamatanSelect,
            'id_kecamatan',
            'nama_kecamatan',
            'Pilih Kecamatan'
        );
    });

    // Ketika kecamatan dipilih
    kecamatanSelect.addEventListener('change', function() {
        const idKecamatan = this.value;

        resetSelect(desaSelect, 'Pilih Desa/Kelurahan');

        if (!idKecamatan) return;

        loadWilayah(
            desaUrl.replace('__ID__', idKecamatan),
            desaSelect,
            'id_desa',
            'nama_desa',
            'Pilih Desa/Kelurahan'
        );
    });

    // Ketika desa dipilih
    desaSelect.addEventListener('change', function() {
        if (!this.value) return;

        const namaDesa = desaSelect.options[desaSelect.selectedIndex].text;
        const namaKecamatan = kecamatanSelect.options[kecamatanSelect.selectedIndex].text;
        const namaKota = kotaSelect.options[kotaSelect.selectedIndex].text;
        const namaProvinsi = provinsiSelect.options[provinsiSelect.selectedIndex].text;

        // Mengisi kolom pencarian RajaOngkir
        searchInput.value = [
            namaDesa,
            namaKecamatan,
            namaKota,
            namaProvinsi
        ].join(', ');

        searchMessage.textContent =
            'Wilayah dipilih. Klik Cari Lokasi untuk mencari ID RajaOngkir.';
    });

    // ==================================
    // PENCARIAN LOKASI RAJAONGKIR
    // ==================================

    searchButton.addEventListener('click', searchLocation);

    searchInput.addEventListener('keydown', function(event) {
        if (event.key === 'Enter') {
            event.preventDefault();
            searchLocation();
        }
    });

    async function searchLocation() {
        const keyword = searchInput.value.trim();

        if (keyword.length < 2) {
            searchMessage.textContent = 'Masukkan minimal 2 karakter.';
            searchResults.style.display = 'none';
            return;
        }

        searchButton.disabled = true;
        searchButton.textContent = 'Mencari...';

        searchMessage.textContent = 'Sedang mengambil data lokasi...';
        searchResults.style.display = 'none';
        selectedInfo.style.display = 'none';

        try {
            const response = await fetch(
                searchUrl + '?search=' + encodeURIComponent(keyword),
                {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                }
            );

            const result = await response.json();

            if (!response.ok) {
                throw new Error(
                    result.message || 'Gagal mengambil data lokasi.'
                );
            }

            const locations = result.data || [];

            searchResults.innerHTML = '';

            if (locations.length === 0) {
                searchMessage.textContent = 'Lokasi tidak ditemukan.';
                return;
            }

            locations.forEach(function(location) {
                const item = document.createElement('button');

                item.type = 'button';
                item.className = 'result-item';
                item.textContent = location.label;

                item.addEventListener('click', function() {
                    selectLocation(location);
                });

                searchResults.appendChild(item);
            });

            searchResults.style.display = 'block';

            searchMessage.textContent =
                'Ditemukan ' + locations.length +
                ' lokasi. Klik lokasi yang sesuai.';

        } catch (error) {
            searchMessage.textContent = error.message;
            searchResults.style.display = 'none';

        } finally {
            searchButton.disabled = false;
            searchButton.textContent = 'Cari Lokasi';
        }
    }

    // Memilih hasil pencarian RajaOngkir
    function selectLocation(location) {
        originInput.value = location.id;
        addressInput.value = location.label;

        selectedInfo.textContent =
            'Lokasi terpilih: ' + location.label +
            ' | Origin ID: ' + location.id;

        selectedInfo.style.display = 'block';

        searchMessage.textContent = 'Lokasi berhasil dipilih.';
        searchResults.style.display = 'none';
    }
</script>


</body>
</html>
