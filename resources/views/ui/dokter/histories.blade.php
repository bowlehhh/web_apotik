@extends('ui.dokter.layout')

@section('dokter_title', 'Riwayat Pasien')
@section('dokter_heading', 'Riwayat Pasien')
@section('dokter_subheading', 'Riwayat kunjungan berlapis, update data kunjungan, dan pemberian resep obat pasien.')

@section('dokter_content')
<section class="bg-white rounded-[2.5rem] p-8 shadow-sm">
    <div class="mb-6">
        <h3 class="text-xl font-extrabold text-blue-900">Riwayat Pasien (Layer / Menumpuk)</h3>
        <p class="text-sm text-slate-500">Setiap kunjungan disimpan sebagai layer riwayat baru. Bisa update riwayat dan tambah resep obat.</p>
    </div>

    <div class="space-y-6">
        @forelse ($visits as $visit)
            <article class="rounded-2xl border border-slate-100 bg-slate-50/60 p-5">
                <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                    <div>
                        @php
                            $age = $visit->patient?->age;
                            $height = $visit->patient?->height_cm !== null ? rtrim(rtrim(number_format((float) $visit->patient->height_cm, 2, '.', ''), '0'), '.') . ' cm' : '-';
                            $weight = $visit->patient?->weight_kg !== null ? rtrim(rtrim(number_format((float) $visit->patient->weight_kg, 2, '.', ''), '0'), '.') . ' kg' : '-';
                        @endphp
                        <h4 class="text-lg font-bold text-slate-800">
                            {{ $visit->patient?->name ?? 'Pasien tidak ditemukan' }}
                        </h4>
                        <p class="text-xs text-slate-500">
                            {{ $visit->patient?->medical_record_number ?? '-' }} |
                            {{ optional($visit->visit_date)->format('d M Y H:i') }} |
                            Dokter: {{ $visit->doctor?->name ?? '-' }}
                        </p>
                        <p class="text-xs text-slate-500 mt-1">
                            Umur: {{ $age !== null ? $age.' tahun' : '-' }} | Tinggi: {{ $height }} | Berat: {{ $weight }}
                        </p>
                    </div>
                    <span class="px-3 py-1 rounded-full text-[10px] font-bold {{ $visit->status === 'lanjutan' ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700' }}">
                        {{ strtoupper($visit->status) }}
                    </span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-5">
                    <div class="rounded-xl bg-white border border-slate-100 p-3">
                        <p class="text-[10px] uppercase tracking-widest font-bold text-slate-400 mb-1">Keluhan</p>
                        <p class="text-sm text-slate-700">{{ $visit->complaint }}</p>
                    </div>
                    <div class="rounded-xl bg-white border border-slate-100 p-3">
                        <p class="text-[10px] uppercase tracking-widest font-bold text-slate-400 mb-1">Diagnosis</p>
                        <p class="text-sm text-slate-700">{{ $visit->diagnosis ?: '-' }}</p>
                    </div>
                    <div class="rounded-xl bg-white border border-slate-100 p-3">
                        <p class="text-[10px] uppercase tracking-widest font-bold text-slate-400 mb-1">Tindakan</p>
                        <p class="text-sm text-slate-700">{{ $visit->action_taken ?: '-' }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
                    <div class="rounded-xl bg-white border border-slate-100 p-4">
                        <h5 class="text-sm font-bold text-slate-800 mb-3">Update Riwayat Kunjungan</h5>
                        <form method="POST" action="{{ route('dokter.visits.update', $visit) }}" class="space-y-3">
                            @csrf
                            @method('PATCH')
                            <input type="datetime-local" name="visit_date" value="{{ optional($visit->visit_date)->format('Y-m-d\TH:i') }}" class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm" required />
                            <textarea name="complaint" rows="2" class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm" required>{{ $visit->complaint }}</textarea>
                            <textarea name="diagnosis" rows="2" class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm">{{ $visit->diagnosis }}</textarea>
                            <textarea name="action_taken" rows="2" class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm">{{ $visit->action_taken }}</textarea>
                            <textarea name="notes" rows="2" class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm">{{ $visit->notes }}</textarea>
                            <select name="status" class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm" required>
                                <option value="selesai" @selected($visit->status === 'selesai')>Selesai</option>
                                <option value="lanjutan" @selected($visit->status === 'lanjutan')>Lanjutan</option>
                            </select>
                            <button type="submit" class="px-4 py-2 rounded-lg bg-blue-600 text-white text-xs font-bold hover:bg-blue-700 transition-colors">
                                Update Riwayat
                            </button>
                        </form>
                    </div>

                    <div class="rounded-xl bg-white border border-slate-100 p-4">
                        <h5 class="text-sm font-bold text-slate-800 mb-3">Resep Obat Pasien</h5>
                        <div class="mb-3 space-y-2 max-h-40 overflow-y-auto pr-1">
                            @php
                                $items = $visit->prescriptions->flatMap->items;
                            @endphp
                            @forelse ($items as $item)
                                <div class="rounded-lg bg-slate-50 border border-slate-100 p-2">
                                    <p class="text-xs font-bold text-slate-700">
                                        {{ $item->medicine?->name }} ({{ $item->quantity }})
                                    </p>
                                    <p class="text-[11px] text-slate-500">
                                        {{ $item->dosage_instructions }} | Stok saat ini: {{ $item->medicine?->stock ?? 0 }}
                                    </p>
                                </div>
                            @empty
                                <p class="text-xs text-slate-500">Belum ada resep untuk kunjungan ini.</p>
                            @endforelse
                        </div>

                        <form method="POST" action="{{ route('dokter.prescriptions.store', $visit) }}" class="space-y-2">
                            @csrf
                            <select name="medicine_id" class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm" required>
                                <option value="">Pilih Obat</option>
                                @foreach ($medicines as $medicine)
                                    <option value="{{ $medicine->id }}">
                                        {{ $medicine->name }} | {{ $medicine->trade_name ?: '-' }} | stok: {{ $medicine->stock }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="grid grid-cols-2 gap-2">
                                <input type="number" min="1" name="quantity" value="1" class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm" required />
                                <input type="text" name="dosage_instructions" class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm" placeholder="Aturan pakai, contoh 3x1" required />
                            </div>
                            <input type="text" name="item_note" class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm" placeholder="Catatan item resep (opsional)" />
                            <textarea name="prescription_notes" rows="2" class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm" placeholder="Catatan resep (opsional)"></textarea>
                            <button type="submit" class="px-4 py-2 rounded-lg bg-primary text-white text-xs font-bold hover:bg-primary-container transition-colors">
                                Simpan Resep
                            </button>
                        </form>
                    </div>
                </div>
            </article>
        @empty
            <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center">
                <p class="text-sm text-slate-500">Belum ada riwayat kunjungan pasien. Tambahkan konsultasi pertama dari halaman konsultasi.</p>
            </div>
        @endforelse
    </div>
</section>
@endsection
