@extends('layouts.app')

@section('title', 'Kelola Produk – Admin Elektronik Modern')

@section('head')
@endsection

@section('header')
@endsection

@section('content')
    <div class="flex flex-col md:flex-row min-h-screen bg-g50">
        @include('partials.admin-sidebar')

        <div class="flex-1 w-full min-w-0 flex flex-col p-6 md:p-8 overflow-y-auto h-screen relative">
            <div class="flex justify-between items-center mb-8 pt-12 md:pt-0">
                <h1 class="font-heading text-[24px] font-extrabold text-g900">Kelola Produk</h1>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-g100 mb-8">
                <div class="p-6 border-b border-g100">
                    <h3 class="font-heading text-[16px] font-extrabold text-g900 flex items-center gap-2">
                        <i class="fi fi-rr-layer-plus text-primary"></i> Tambah Produk
                    </h3>
                </div>
                <form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data" class="p-6">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-[11px] font-extrabold text-g700 tracking-widest uppercase mb-1.5">Nama Produk</label>
                            <input name="nama_produk" value="{{ old('nama_produk') }}" required
                                class="w-full py-2.5 px-3 border-[1.5px] {{ $errors->has('nama_produk') ? 'border-red-500' : 'border-g200' }} rounded-lg outline-none text-[13px] font-semibold text-g800 bg-g50 transition-all focus:border-primary focus:bg-white focus:ring-2 focus:ring-primary/10">
                            @error('nama_produk')
                                <div class="mt-1 text-[11px] font-bold text-red-500">{{ $message }}</div>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-[11px] font-extrabold text-g700 tracking-widest uppercase mb-1.5">Kategori</label>
                            <select name="id_kategori" required
                                class="w-full py-2.5 px-3 border-[1.5px] {{ $errors->has('id_kategori') ? 'border-red-500' : 'border-g200' }} rounded-lg outline-none text-[13px] font-semibold text-g800 bg-g50 transition-all focus:border-primary focus:bg-white focus:ring-2 focus:ring-primary/10 cursor-pointer">
                                <option value="">Pilih kategori</option>
                                @foreach ($kategoris as $kategori)
                                    <option value="{{ $kategori->id_kategori }}" @selected(old('id_kategori') == $kategori->id_kategori)>
                                        {{ $kategori->nama_kategori }}
                                    </option>
                                @endforeach
                            </select>
                            @error('id_kategori')
                                <div class="mt-1 text-[11px] font-bold text-red-500">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                        <div>
                            <label class="block text-[11px] font-extrabold text-g700 tracking-widest uppercase mb-1.5">Harga</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-[13px] font-bold text-g500">Rp</div>
                                <input type="number" name="harga" min="0" value="{{ old('harga') }}" required
                                    class="w-full py-2.5 pl-9 pr-3 border-[1.5px] {{ $errors->has('harga') ? 'border-red-500' : 'border-g200' }} rounded-lg outline-none text-[13px] font-semibold text-g800 bg-g50 transition-all focus:border-primary focus:bg-white focus:ring-2 focus:ring-primary/10">
                            </div>
                            @error('harga')
                                <div class="mt-1 text-[11px] font-bold text-red-500">{{ $message }}</div>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-[11px] font-extrabold text-g700 tracking-widest uppercase mb-1.5">Stok</label>
                            <input type="number" name="stok" min="0" value="{{ old('stok') }}" required
                                class="w-full py-2.5 px-3 border-[1.5px] {{ $errors->has('stok') ? 'border-red-500' : 'border-g200' }} rounded-lg outline-none text-[13px] font-semibold text-g800 bg-g50 transition-all focus:border-primary focus:bg-white focus:ring-2 focus:ring-primary/10">
                            @error('stok')
                                <div class="mt-1 text-[11px] font-bold text-red-500">{{ $message }}</div>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-[11px] font-extrabold text-g700 tracking-widest uppercase mb-1.5">Gambar Produk</label>
                            <input type="file" name="gambar" accept="image/*" required
                                class="w-full py-2 px-3 border-[1.5px] {{ $errors->has('gambar') ? 'border-red-500' : 'border-g200' }} rounded-lg outline-none text-[12px] font-semibold text-g800 bg-g50 transition-all focus:border-primary focus:bg-white focus:ring-2 focus:ring-primary/10 file:mr-3 file:py-1 file:px-3 file:rounded-md file:border-0 file:text-[11px] file:font-bold file:bg-primary/10 file:text-primary hover:file:bg-primary/20">
                            @error('gambar')
                                <div class="mt-1 text-[11px] font-bold text-red-500">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="mb-5">
                        <label class="block text-[11px] font-extrabold text-g700 tracking-widest uppercase mb-1.5">Deskripsi</label>
                        <textarea name="deskripsi" rows="3" required
                            class="w-full py-2.5 px-3 border-[1.5px] {{ $errors->has('deskripsi') ? 'border-red-500' : 'border-g200' }} rounded-lg outline-none text-[13px] font-semibold text-g800 bg-g50 transition-all focus:border-primary focus:bg-white focus:ring-2 focus:ring-primary/10">{{ old('deskripsi') }}</textarea>
                        @error('deskripsi')
                            <div class="mt-1 text-[11px] font-bold text-red-500">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div>
                        <button type="submit" class="inline-flex py-2.5 px-5 bg-primary text-white rounded-lg font-bold text-[13px] shadow-sm hover:bg-primary-dark hover:-translate-y-px transition-all items-center gap-2">
                            <i class="fi fi-rr-disk"></i> Simpan Produk
                        </button>
                    </div>
                </form>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-g100">
                <div class="p-6 border-b border-g100 flex justify-between items-center">
                    <h3 class="font-heading text-[16px] font-extrabold text-g900 flex items-center gap-2">
                        <i class="fi fi-rr-box text-primary"></i> Daftar Produk
                    </h3>
                </div>
                <div class="overflow-x-auto w-full">
                    <table class="w-full min-w-[800px] text-left border-collapse">
                        <thead>
                            <tr>
                                <th class="bg-g50 py-3.5 px-6 text-xs font-extrabold text-g500 uppercase tracking-widest border-b border-g200">ID</th>
                                <th class="bg-g50 py-3.5 px-6 text-xs font-extrabold text-g500 uppercase tracking-widest border-b border-g200">Produk</th>
                                <th class="bg-g50 py-3.5 px-6 text-xs font-extrabold text-g500 uppercase tracking-widest border-b border-g200">Kategori</th>
                                <th class="bg-g50 py-3.5 px-6 text-xs font-extrabold text-g500 uppercase tracking-widest border-b border-g200">Harga</th>
                                <th class="bg-g50 py-3.5 px-6 text-xs font-extrabold text-g500 uppercase tracking-widest border-b border-g200">Stok</th>
                                <th class="bg-g50 py-3.5 px-6 text-xs font-extrabold text-g500 uppercase tracking-widest border-b border-g200">Status</th>
                                <th class="bg-g50 py-3.5 px-6 text-xs font-extrabold text-g500 uppercase tracking-widest border-b border-g200 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($produks as $produk)
                                <tr class="group hover:bg-g50/50 transition-colors">
                                    <td class="py-4 px-6 border-b border-g100 group-last:border-none">
                                        <div class="font-bold text-g400 text-[12px]">#P{{ str_pad((string) $produk->id_produk, 3, '0', STR_PAD_LEFT) }}</div>
                                    </td>
                                    <td class="py-4 px-6 border-b border-g100 group-last:border-none">
                                        <div class="flex items-center gap-3">
                                            <div class="w-11 h-11 rounded-lg bg-white border border-g100 p-1 flex items-center justify-center shrink-0">
                                                @if($produk->gambar)
                                                    <img src="{{ asset('storage/products/' . $produk->gambar) }}" alt="{{ $produk->nama_produk }}" loading="lazy" decoding="async" class="w-full h-full object-contain mix-blend-multiply">
                                                @else
                                                    <i class="fi fi-rr-picture text-g300"></i>
                                                @endif
                                            </div>
                                            <div class="font-bold text-g800 text-[13px] line-clamp-2 max-w-[200px]">{{ $produk->nama_produk }}</div>
                                        </div>
                                    </td>
                                    <td class="py-4 px-6 border-b border-g100 group-last:border-none">
                                        <span class="inline-flex items-center bg-blue-50 text-blue-600 border border-blue-200 py-1 px-2.5 rounded text-[10px] font-extrabold tracking-widest uppercase">
                                            {{ $produk->kategori->nama_kategori ?? '-' }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-6 border-b border-g100 group-last:border-none">
                                        <div class="font-heading font-extrabold text-g900 text-[14px] whitespace-nowrap">
                                            Rp {{ number_format($produk->harga, 0, ',', '.') }}
                                        </div>
                                    </td>
                                    <td class="py-4 px-6 border-b border-g100 group-last:border-none">
                                        @php
                                            $stokClass = $produk->stok > 10 ? 'bg-green-50 text-green-600 border-green-200' : ($produk->stok > 0 ? 'bg-orange-50 text-orange-600 border-orange-200' : 'bg-red-50 text-red-600 border-red-200');
                                        @endphp
                                        <span class="inline-flex items-center {{ $stokClass }} border py-1 px-2.5 rounded text-[11px] font-extrabold tracking-widest">
                                            {{ $produk->stok }} unit
                                        </span>
                                    </td>
                                    <td class="py-4 px-6 border-b border-g100 group-last:border-none">
                                        <span class="inline-flex items-center gap-1.5 {{ $produk->stok < 5 ? 'text-red-500' : 'text-green-600' }} text-[12px] font-bold">
                                            <span class="w-2 h-2 rounded-full {{ $produk->stok < 5 ? 'bg-red-500' : 'bg-green-500' }}"></span>
                                            {{ $produk->stok < 5 ? 'Lakukan Stok Ulang' : 'Stok Mencukupi' }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-6 border-b border-g100 group-last:border-none text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="{{ route('admin.products.edit', $produk) }}" class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 border border-blue-100 flex items-center justify-center hover:bg-blue-600 hover:text-white transition-colors" title="Edit">
                                                <i class="fi fi-rr-edit"></i>
                                            </a>
                                            <form method="POST" action="{{ route('admin.products.destroy', $produk) }}" onsubmit="event.preventDefault(); window.confirmDelete(this, 'Produk {{ $produk->nama_produk }} akan dihapus secara permanen.');" class="m-0">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="w-8 h-8 rounded-lg bg-red-50 text-red-500 border border-red-100 flex items-center justify-center hover:bg-red-600 hover:text-white transition-colors" title="Hapus">
                                                    <i class="fi fi-rr-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-8 px-6 text-center text-g500 font-semibold text-[13px]">
                                        Belum ada produk terdaftar.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($produks->hasPages())
                <div class="p-6 border-t border-g100 flex justify-center w-full overflow-hidden">
                    <div class="inline-flex max-w-full bg-white rounded-xl shadow-sm border border-g200 p-1">
                        {{ $produks->links('pagination::tailwind') }}
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@section('footer')
@endsection
