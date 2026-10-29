@extends('layouts.app')

@section('title', 'Edit Produk – Admin Elektronik Modern')

@section('head')
@endsection

@section('header')
@endsection

@section('content')
    <div class="flex flex-col md:flex-row min-h-screen bg-g50">
        @include('partials.admin-sidebar')

        <div class="flex-1 w-full min-w-0 flex flex-col p-6 md:p-8 overflow-y-auto h-screen relative">
            <div class="flex items-center gap-1.5 mb-6 text-[13px] pt-12 md:pt-0">
                <a href="{{ route('admin.index') }}" class="text-g500 hover:text-primary transition-colors flex items-center gap-1.5"><i class="fi fi-rr-apps"></i> Dashboard</a> 
                <i class="fi fi-rr-angle-small-right text-g400"></i> 
                <a href="{{ route('admin.products.index') }}" class="text-g500 hover:text-primary transition-colors">Kelola Produk</a>
                <i class="fi fi-rr-angle-small-right text-g400"></i>
                <span class="text-g800 font-semibold">Edit Produk</span>
            </div>

            <div class="flex justify-between items-center mb-8">
                <h1 class="font-heading text-[24px] font-extrabold text-g900">Edit Produk</h1>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-g100 max-w-4xl">
                <div class="p-6 border-b border-g100">
                    <h3 class="font-heading text-[16px] font-extrabold text-g900 flex items-center gap-2">
                        <i class="fi fi-rr-edit text-primary"></i> Form Edit Produk
                    </h3>
                </div>
                <form method="POST" action="{{ route('admin.products.update', $produk) }}" enctype="multipart/form-data" class="p-6">
                    @csrf
                    @method('PUT')
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-[11px] font-extrabold text-g700 tracking-widest uppercase mb-1.5">Nama Produk</label>
                            <input name="nama_produk" value="{{ old('nama_produk', $produk->nama_produk) }}" required
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
                                    <option value="{{ $kategori->id_kategori }}"
                                        @selected(old('id_kategori', $produk->id_kategori) == $kategori->id_kategori)>
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
                                <input type="number" name="harga" min="0" value="{{ old('harga', $produk->harga) }}" required
                                    class="w-full py-2.5 pl-9 pr-3 border-[1.5px] {{ $errors->has('harga') ? 'border-red-500' : 'border-g200' }} rounded-lg outline-none text-[13px] font-semibold text-g800 bg-g50 transition-all focus:border-primary focus:bg-white focus:ring-2 focus:ring-primary/10">
                            </div>
                            @error('harga')
                                <div class="mt-1 text-[11px] font-bold text-red-500">{{ $message }}</div>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-[11px] font-extrabold text-g700 tracking-widest uppercase mb-1.5">Stok</label>
                            <input type="number" name="stok" min="0" value="{{ old('stok', $produk->stok) }}" required
                                class="w-full py-2.5 px-3 border-[1.5px] {{ $errors->has('stok') ? 'border-red-500' : 'border-g200' }} rounded-lg outline-none text-[13px] font-semibold text-g800 bg-g50 transition-all focus:border-primary focus:bg-white focus:ring-2 focus:ring-primary/10">
                            @error('stok')
                                <div class="mt-1 text-[11px] font-bold text-red-500">{{ $message }}</div>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-[11px] font-extrabold text-g700 tracking-widest uppercase mb-1.5">Gambar Produk (opsional)</label>
                            <input type="file" name="gambar" accept="image/*"
                                class="w-full py-2 px-3 border-[1.5px] {{ $errors->has('gambar') ? 'border-red-500' : 'border-g200' }} rounded-lg outline-none text-[12px] font-semibold text-g800 bg-g50 transition-all focus:border-primary focus:bg-white focus:ring-2 focus:ring-primary/10 file:mr-3 file:py-1 file:px-3 file:rounded-md file:border-0 file:text-[11px] file:font-bold file:bg-primary/10 file:text-primary hover:file:bg-primary/20">
                            @error('gambar')
                                <div class="mt-1 text-[11px] font-bold text-red-500">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div>
                        <label>Berat Produk (gram)</label>
                        <input
                            type="number"
                            name="berat_gram"
                            min="1"
                            value="{{ old('berat_gram', $produk->berat_gram) }}"
                            required
                        >
                    </div>
                    <div class="mb-6">
                        <label class="block text-[11px] font-extrabold text-g700 tracking-widest uppercase mb-1.5">Deskripsi</label>
                        <textarea name="deskripsi" rows="3" required
                            class="w-full py-2.5 px-3 border-[1.5px] {{ $errors->has('deskripsi') ? 'border-red-500' : 'border-g200' }} rounded-lg outline-none text-[13px] font-semibold text-g800 bg-g50 transition-all focus:border-primary focus:bg-white focus:ring-2 focus:ring-primary/10">{{ old('deskripsi', $produk->deskripsi) }}</textarea>
                        @error('deskripsi')
                            <div class="mt-1 text-[11px] font-bold text-red-500">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="flex gap-3">
                        <button type="submit" class="inline-flex py-2.5 px-5 bg-primary text-white rounded-lg font-bold text-[13px] shadow-sm hover:bg-primary-dark hover:-translate-y-px transition-all items-center gap-2">
                            <i class="fi fi-rr-disk"></i> Simpan Perubahan
                        </button>
                        <a href="{{ route('admin.products.index') }}" class="inline-flex py-2.5 px-5 bg-white text-g700 border-[1.5px] border-g200 rounded-lg font-bold text-[13px] hover:border-primary hover:text-primary transition-all items-center gap-2">
                            Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('footer')
@endsection
