@extends('layouts.app')

@section('title', 'Profil Saya – Elektronik Modern')

@section('content')
    <section class="py-8 md:py-[72px] bg-g50 min-h-screen px-4 md:px-8">
        <div class="max-w-[1024px] mx-auto">
            <div class="flex items-center gap-1.5 mb-6 text-[13px]">
                <a href="{{ route('index') }}" class="text-g500 hover:text-primary transition-colors flex items-center gap-1.5"><i class="fi fi-rr-home"></i> Beranda</a> 
                <i class="fi fi-rr-angle-small-right text-g400"></i> 
                <span class="text-g800 font-semibold">Profil Saya</span>
            </div>
            
            <div class="mb-8">
                <h1 class="font-heading text-[28px] md:text-[32px] font-extrabold text-g900 mb-2 flex items-center gap-3">
                    <i class="fi fi-rr-user text-primary"></i> Profil Saya
                </h1>
                <p class="text-g500 text-[15px]">Kelola informasi akun Anda</p>
            </div>

            <x-error :messages="$errors->all()" class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl" />

            <div class="flex flex-col md:flex-row gap-8 items-start">
                <!-- Sidebar -->
                <x-card class="w-full md:w-[300px] shrink-0 p-8 text-center">
                    <div class="w-20 h-20 rounded-full bg-gradient-to-br from-primary to-primary-light flex items-center justify-center text-[28px] font-extrabold text-white mx-auto mb-4 shadow-[0_8px_24px_rgba(26,92,255,0.3)]">
                        {{ strtoupper(substr($user->nama, 0, 2)) }}
                    </div>
                    <div class="font-heading text-xl font-extrabold text-g900 mb-1">{{ $user->nama }}</div>
                    <div class="text-[13px] text-g500 mb-4">{{ $user->email }}</div>
                    <x-badge variant="success" class="uppercase tracking-widest gap-1.5 py-1.5 px-3.5">
                        <i class="fi fi-rr-user-tag text-sm mt-0.5"></i> Customer
                    </x-badge>

                    <div class="flex flex-col gap-1 mt-6 pt-5 border-t border-g100 profile-nav">
                        <a href="#" class="flex items-center gap-2.5 py-2.5 px-4 rounded-xl text-[14px] font-bold transition-all bg-primary-light text-primary" onclick="switchTab('edit-profile', this, event)">
                            <i class="fi fi-rr-edit text-sm mt-0.5"></i> Edit Profil
                        </a>
                        <a href="#" class="flex items-center gap-2.5 py-2.5 px-4 rounded-xl text-[14px] font-bold transition-all text-g600 hover:bg-g50" onclick="switchTab('alamat', this, event)">
                            <i class="fi fi-rr-map-marker text-sm mt-0.5"></i> Alamat Pengiriman
                        </a>
                        <a href="{{ route('customer.orders') }}" class="flex items-center gap-2.5 py-2.5 px-4 rounded-xl text-[14px] font-bold transition-all text-g600 hover:bg-g50">
                            <i class="fi fi-rr-box text-sm mt-0.5"></i> Pesanan Saya
                        </a>
                    </div>
                </x-card>

                <!-- Container Panel -->
                <div class="flex-1 w-full min-w-0">
                    <!-- Edit Form -->
                    <div id="edit-profile" class="tab-panel block">
                        <x-card class="p-6 md:p-8">
                            <h2 class="font-heading text-[22px] font-extrabold text-g900 mb-1.5">Edit Profil</h2>
                            <p class="text-[14px] text-g500 mb-7">Perbarui informasi akun Anda di bawah ini</p>

                            <form method="POST" action="{{ route('customer.profile.update') }}">
                                @csrf
                                @method('PUT')

                                <div class="mb-4">
                                    <x-label for="nama">Nama Lengkap</x-label>
                                    <x-input type="text" id="nama" name="nama" :value="old('nama', $user->nama)" required />
                                </div>

                                <div class="mb-4">
                                    <x-label for="email">Email</x-label>
                                    <x-input type="email" id="email" name="email" :value="old('email', $user->email)" required />
                                </div>

                                <div class="border-t border-g200 pt-5 mt-2 mb-4">
                                    <div class="text-[13px] font-bold text-g800 mb-1 flex items-center gap-1.5"><i class="fi fi-rr-lock"></i> Ubah Password</div>
                                    <div class="text-[12px] text-g400 mb-4 font-medium">Kosongkan jika tidak ingin mengubah password</div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                                    <div>
                                        <x-label for="password">Password Baru</x-label>
                                        <x-input type="password" id="password" name="password" placeholder="Min. 8 karakter" />
                                    </div>
                                    <div>
                                        <x-label for="password_confirmation">Konfirmasi Password</x-label>
                                        <x-input type="password" id="password_confirmation" name="password_confirmation" placeholder="Ulangi password" />
                                    </div>
                                </div>

                                <x-button type="submit" class="py-3.5 px-7 rounded-full">
                                    <i class="fi fi-rr-disk"></i> Simpan Profil
                                </x-button>
                            </form>
                        </x-card>
                    </div>
                    
                    <!-- Alamat Pengiriman -->
                    <div id="alamat" class="tab-panel hidden">
                        <x-card class="p-6 md:p-8">
                            <h2 class="font-heading text-[22px] font-extrabold text-g900 mb-1.5">Alamat Pengiriman</h2>
                            <p class="text-[14px] text-g500 mb-7">Kelola alamat untuk keperluan checkout pesanan Anda</p>
                            
                            <div class="grid gap-3 mb-6">
                                @forelse($user->alamatUsers as $alamat)
                                    <div class="border-[1.5px] border-g200 rounded-xl p-5 {{ $alamat->is_utama ? 'bg-primary-light/30 border-primary/30' : 'bg-white' }}">
                                        <div class="flex justify-between items-start mb-2 gap-4">
                                            <div class="font-bold text-[15px] text-g900 flex items-center flex-wrap gap-2">
                                                {{ $alamat->label_alamat }} 
                                                @if($alamat->is_utama)
                                                    <x-badge variant="primary" class="uppercase tracking-widest text-[10px] py-0.5 px-2.5">Utama</x-badge>
                                                @endif
                                            </div>
                                            <div class="text-[13px] font-bold text-g800 flex items-center gap-1.5 whitespace-nowrap"><i class="fi fi-rr-phone-call text-g400"></i> {{ $alamat->nomor_telepon }}</div>
                                        </div>
                                        <div class="text-[13px] text-g700 font-semibold mb-1">Penerima: {{ $alamat->nama_penerima }}</div>
                                        <div class="text-[13px] text-g600 leading-relaxed font-medium">
                                            {{ $alamat->detail_alamat }}<br>
                                            <span class="text-g500">{{ $alamat->desa->nama_desa }}, {{ $alamat->desa->kecamatan->nama_kecamatan }}, {{ $alamat->desa->kecamatan->kota->nama_kota }}, {{ $alamat->desa->kecamatan->kota->provinsi->nama_provinsi }}</span>
                                        </div>
                                        <details class="mt-4 group">
                                            <summary class="cursor-pointer text-[13px] font-bold text-primary hover:text-primary-dark transition-colors list-none flex items-center gap-1.5">
                                                <i class="fi fi-rr-edit text-xs"></i> Edit alamat
                                            </summary>
                                            <div class="mt-4 pt-4 border-t border-g200">
                                                <form method="POST" action="{{ route('customer.alamat.update', $alamat) }}" class="grid gap-3 mb-3 location-form">
                                                    @csrf @method('PUT')
                                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                                        <div>
                                                            <x-label>Label Alamat</x-label>
                                                            <x-input name="label_alamat" :value="old('label_alamat', $alamat->label_alamat)" required />
                                                        </div>
                                                        <div>
                                                            <x-label>Nama Penerima</x-label>
                                                            <x-input name="nama_penerima" :value="old('nama_penerima', $alamat->nama_penerima)" required />
                                                        </div>
                                                    </div>
                                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                                        <div>
                                                            <x-label>Nomor Telepon</x-label>
                                                            <x-input name="nomor_telepon" :value="old('nomor_telepon', $alamat->nomor_telepon)" required />
                                                        </div>
                                                    </div>
                                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                                        <div>
                                                            <x-label>Provinsi</x-label>
                                                            <x-select name="id_provinsi" class="provinsi-select" required>
                                                                <option value="">-- Pilih Provinsi --</option>
                                                                @foreach($provinsis as $p)
                                                                    <option value="{{ $p->id_provinsi }}" @selected($alamat->desa->kecamatan->kota->id_provinsi == $p->id_provinsi)>
                                                                        {{ $p->nama_provinsi }}
                                                                    </option>
                                                                @endforeach
                                                            </x-select>
                                                        </div>
                                                        <div>
                                                            <x-label>Kota/Kabupaten</x-label>
                                                            <x-select name="id_kota" class="kota-select" required>
                                                                <option value="{{ $alamat->desa->kecamatan->id_kota }}">
                                                                    {{ $alamat->desa->kecamatan->kota->nama_kota }}
                                                                </option>
                                                            </x-select>
                                                        </div>
                                                    </div>
                                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                                        <div>
                                                            <x-label>Kecamatan</x-label>
                                                            <x-select name="id_kecamatan" class="kecamatan-select" required>
                                                                <option value="{{ $alamat->desa->id_kecamatan }}">
                                                                    {{ $alamat->desa->kecamatan->nama_kecamatan }}
                                                                </option>
                                                            </x-select>
                                                        </div>
                                                        <div>
                                                            <x-label>Desa/Kelurahan</x-label>
                                                            <x-select name="id_desa" class="desa-select" required>
                                                                <option value="{{ $alamat->id_desa }}">
                                                                    {{ $alamat->desa->nama_desa }}
                                                                </option>
                                                            </x-select>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <x-label>Detail Alamat</x-label>
                                                        <x-textarea name="detail_alamat" rows="2" required>{{ old('detail_alamat', $alamat->detail_alamat) }}</x-textarea>
                                                    </div>
                                                    <label class="flex items-center gap-2 text-[13px] font-bold text-g800 cursor-pointer w-fit mt-1">
                                                        <input type="checkbox" name="is_utama" value="1" @checked($alamat->is_utama) class="w-4 h-4 accent-primary rounded text-primary focus:ring-primary">
                                                        Jadikan sebagai alamat utama
                                                    </label>
                                                    <div class="flex flex-wrap gap-2 mt-2">
                                                        <x-button type="submit" class="py-2.5 px-5 text-[13px]">Simpan Perubahan</x-button>
                                                    </div>
                                                </form>
                                                <form method="POST" action="{{ route('customer.alamat.destroy', $alamat) }}" onsubmit="event.preventDefault(); window.confirmDelete(this, 'Alamat ini akan dihapus secara permanen.');">
                                                    @csrf @method('DELETE')
                                                    <x-button type="submit" variant="danger" class="py-2.5 px-5 text-[13px] bg-red-600 text-white border border-red-500 hover:bg-red-500 hover:text-white shadow-none">Hapus Alamat</x-button>
                                                </form>
                                            </div>
                                        </details>
                                    </div>
                                @empty
                                    <div class="text-[13px] font-semibold text-g500 text-center py-6 border-2 border-dashed border-g200 rounded-xl bg-g50/50">
                                        <i class="fi fi-rr-map-marker text-2xl text-g300 block mb-2"></i> Belum ada alamat tersimpan.
                                    </div>
                                @endforelse
                            </div>

                            <div class="border-t border-g200 pt-6 mt-6">
                                <h3 class="font-heading text-[18px] font-extrabold text-g900 mb-5 flex items-center gap-2"><i class="fi fi-rr-layer-plus text-primary"></i> Tambah Alamat Baru</h3>
                                <form method="POST" action="{{ route('customer.alamat.store') }}" class="location-form">
                                    @csrf
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                        <div>
                                            <x-label>Label Alamat (Cth: Rumah, Kantor)</x-label>
                                            <x-input type="text" name="label_alamat" required placeholder="Rumah" />
                                        </div>
                                        <div>
                                            <x-label>Nama Penerima</x-label>
                                            <x-input type="text" name="nama_penerima" required placeholder="Nama penerima" />
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                        <div>
                                            <x-label>Nomor Telepon</x-label>
                                            <x-input type="text" name="nomor_telepon" required placeholder="08..." />
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                        <div>
                                            <x-label>Provinsi</x-label>
                                            <x-select name="id_provinsi" class="provinsi-select" required>
                                                <option value="">-- Pilih Provinsi --</option>
                                                @foreach($provinsis as $p)
                                                    <option value="{{ $p->id_provinsi }}">{{ $p->nama_provinsi }}</option>
                                                @endforeach
                                            </x-select>
                                        </div>
                                        <div>
                                            <x-label>Kota/Kabupaten</x-label>
                                            <x-select name="id_kota" class="kota-select" required disabled>
                                                <option value="">-- Pilih Kota --</option>
                                            </x-select>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                        <div>
                                            <x-label>Kecamatan</x-label>
                                            <x-select name="id_kecamatan" class="kecamatan-select" required disabled>
                                                <option value="">-- Pilih Kecamatan --</option>
                                            </x-select>
                                        </div>
                                        <div>
                                            <x-label>Desa/Kelurahan</x-label>
                                            <x-select name="id_desa" class="desa-select" required disabled>
                                                <option value="">-- Pilih Desa --</option>
                                            </x-select>
                                        </div>
                                    </div>
                                    <div class="mb-4">
                                        <x-label>Detail Alamat (Jalan, RT/RW, Patokan)</x-label>
                                        <x-textarea name="detail_alamat" rows="3" required placeholder="Jl. Mawar No 1..."></x-textarea>
                                    </div>
                                    <div class="mb-6">
                                        <label class="flex items-center gap-2 cursor-pointer w-fit">
                                            <input type="checkbox" name="is_utama" value="1" class="w-4 h-4 accent-primary rounded text-primary focus:ring-primary">
                                            <span class="text-[13px] font-bold text-g800">Jadikan sebagai alamat utama</span>
                                        </label>
                                    </div>
                                    <x-button type="submit" class="py-3.5 px-7 rounded-full bg-g800 shadow-[0_4px_12px_rgba(0,0,0,0.15)] hover:bg-g900">
                                        <i class="fi fi-rr-plus"></i> Tambah Alamat
                                    </x-button>
                                </form>
                            </div>
                        </x-card>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const forms = document.querySelectorAll('.location-form');

            forms.forEach(form => {
                const provSelect = form.querySelector('.provinsi-select');
                const kotaSelect = form.querySelector('.kota-select');
                const kecSelect  = form.querySelector('.kecamatan-select');
                const desaSelect = form.querySelector('.desa-select');

                const resetSelect = (select, placeholder) => {
                    select.innerHTML = `<option value="">-- Pilih ${placeholder} --</option>`;
                    select.disabled = true;
                };

                const fillSelect = (select, data, idKey, nameKey, placeholder) => {
                    select.innerHTML = `<option value="">-- Pilih ${placeholder} --</option>`;
                    data.forEach(item => {
                        select.innerHTML += `<option value="${item[idKey]}">${item[nameKey]}</option>`;
                    });
                    select.disabled = false;
                };

                provSelect.addEventListener('change', function() {
                    const id = this.value;
                    resetSelect(kotaSelect, 'Kota');
                    resetSelect(kecSelect, 'Kecamatan');
                    resetSelect(desaSelect, 'Desa');

                    if (id) {
                        fetch(`/locations/kotas/${id}`)
                            .then(res => res.json())
                            .then(data => fillSelect(kotaSelect, data, 'id_kota', 'nama_kota', 'Kota'));
                    }
                });

                kotaSelect.addEventListener('change', function() {
                    const id = this.value;
                    resetSelect(kecSelect, 'Kecamatan');
                    resetSelect(desaSelect, 'Desa');

                    if (id) {
                        fetch(`/locations/kecamatans/${id}`)
                            .then(res => res.json())
                            .then(data => fillSelect(kecSelect, data, 'id_kecamatan', 'nama_kecamatan', 'Kecamatan'));
                    }
                });

                kecSelect.addEventListener('change', function() {
                    const id = this.value;
                    resetSelect(desaSelect, 'Desa');

                    if (id) {
                        fetch(`/locations/desas/${id}`)
                            .then(res => res.json())
                            .then(data => fillSelect(desaSelect, data, 'id_desa', 'nama_desa', 'Desa'));
                    }
                });
            });

            // Keep tab open if coming from form submit
            @if((session('status') && str_contains(session('status'), 'Alamat')) || session('error') || request('tab') === 'alamat')
                const alamatTab = document.querySelector('a[onclick*="switchTab(\'alamat\'"]');
                if(alamatTab) alamatTab.click();
            @endif
        });
    </script>
    @endpush
@endsection
