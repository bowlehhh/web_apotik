@extends('ui.dokter.layout')

@section('dokter_title', 'Data Obat dan Stok')
@section('dokter_heading', 'Data Obat & Stok')
@section('dokter_subheading', 'Pantau status tersedia/habis, stok, dosis, kategori, dan merek dagang obat.')

@section('dokter_content')
<section class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <article class="bg-white rounded-[2rem] p-6 shadow-sm">
        <p class="text-[10px] uppercase tracking-widest font-bold text-slate-400">Total Data Obat</p>
        <h3 class="text-3xl font-black mt-2">{{ $medicines->count() }}</h3>
    </article>
    <article class="bg-white rounded-[2rem] p-6 shadow-sm">
        <p class="text-[10px] uppercase tracking-widest font-bold text-slate-400">Obat Tersedia</p>
        <h3 class="text-3xl font-black mt-2 text-emerald-600">{{ $stats['ready_medicines'] }}</h3>
    </article>
    <article class="bg-white rounded-[2rem] p-6 shadow-sm">
        <p class="text-[10px] uppercase tracking-widest font-bold text-slate-400">Obat Habis</p>
        <h3 class="text-3xl font-black mt-2 text-red-600">{{ $stats['not_ready_medicines'] }}</h3>
    </article>
</section>

<section class="bg-white rounded-[2.5rem] p-8 shadow-sm">
    <div class="mb-6">
        <h3 class="text-xl font-extrabold text-blue-900">Daftar Obat</h3>
        <p class="text-sm text-slate-500">Dokter dapat melihat data obat dan memperbarui stok langsung dari halaman ini.</p>
    </div>

    <div class="space-y-4">
        @forelse ($medicines as $medicine)
            <article class="rounded-2xl border border-slate-100 bg-slate-50/60 p-5">
                <div class="flex flex-wrap items-start justify-between gap-3 mb-4">
                    <div>
                        <h4 class="font-bold text-slate-800 text-lg">{{ $medicine->name }}</h4>
                        <p class="text-sm text-slate-500">Merek dagang: {{ $medicine->trade_name ?: '-' }}</p>
                        <p class="text-sm text-slate-500">Dosis: {{ $medicine->dosage ?: '-' }} | Kategori: {{ $medicine->category ?: '-' }}</p>
                    </div>
                    <span class="px-3 py-1 rounded-full text-[10px] font-bold {{ $medicine->stock > 0 ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">
                        {{ $medicine->stock > 0 ? 'READY' : 'NOT READY' }}
                    </span>
                </div>

                <form method="POST" action="{{ route('dokter.medicines.update', $medicine) }}" class="grid grid-cols-1 md:grid-cols-5 gap-3">
                    @csrf
                    @method('PATCH')
                    <input type="text" name="trade_name" value="{{ old('trade_name', $medicine->trade_name) }}" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm" placeholder="Merek dagang" />
                    <input type="text" name="dosage" value="{{ old('dosage', $medicine->dosage) }}" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm" placeholder="Dosis" />
                    <input type="text" name="category" value="{{ old('category', $medicine->category) }}" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm" placeholder="Kategori" />
                    <input type="number" min="0" name="stock" value="{{ old('stock', $medicine->stock) }}" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm" placeholder="Stok" required />
                    <button type="submit" class="rounded-lg bg-primary text-white text-sm font-bold px-4 py-2 hover:bg-primary-container transition-colors">
                        Update Obat
                    </button>
                </form>
            </article>
        @empty
            <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center">
                <p class="text-sm text-slate-500">Belum ada data obat. Jalankan seeder obat terlebih dahulu.</p>
            </div>
        @endforelse
    </div>
</section>
@endsection
