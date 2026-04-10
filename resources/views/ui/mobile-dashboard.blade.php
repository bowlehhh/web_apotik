@extends('layouts.ui-shell')

@section('title', 'APOTEK SUMBER SEHAT - Dashboard Manajemen Dokter')
@section('body_class', 'bg-surface text-on-surface')

@section('content')
@php
    $patients = $patients ?? collect();
    $visits = $visits ?? collect();
    $medicines = $medicines ?? collect();
    $stats = $stats ?? [
        'total_patients' => 0,
        'today_visits' => 0,
        'ready_medicines' => 0,
        'not_ready_medicines' => 0,
    ];
@endphp

<div class="flex min-h-screen">
    <aside class="w-64 fixed left-0 top-0 h-screen bg-slate-50 border-r border-slate-100 p-4 flex flex-col">
        <div class="flex items-center gap-3 mb-8 px-2">
            <div class="w-10 h-10 bg-primary-container rounded-xl text-white flex items-center justify-center">
                <span class="material-symbols-outlined" style="font-variation-settings:'FILL' 1">medical_services</span>
            </div>
            <div>
                <h1 class="text-lg font-black text-blue-900">APOTEK SUMBER SEHAT</h1>
                <p class="text-[10px] uppercase tracking-widest text-slate-500 font-bold">Manajemen Dokter</p>
            </div>
        </div>

        <nav class="flex-1 space-y-1 overflow-y-auto pr-1">
            <a href="{{ route('dokter.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg bg-blue-50 text-blue-700 font-semibold">
                <span class="material-symbols-outlined">dashboard</span>
                <span class="text-sm">Dashboard Dokter</span>
            </a>
            <a href="#konsultasi" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-slate-600 hover:bg-slate-200/60">
                <span class="material-symbols-outlined">clinical_notes</span>
                <span class="text-sm">Konsultasi Pasien</span>
            </a>
            <a href="#riwayat" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-slate-600 hover:bg-slate-200/60">
                <span class="material-symbols-outlined">history</span>
                <span class="text-sm">Riwayat Pasien</span>
            </a>
            <a href="#obat" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-slate-600 hover:bg-slate-200/60">
                <span class="material-symbols-outlined">inventory_2</span>
                <span class="text-sm">Data Obat & Stok</span>
            </a>
        </nav>

        <div class="pt-4 border-t border-slate-200">
            <a href="{{ route('logout.get') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-error hover:bg-red-50">
                <span class="material-symbols-outlined">logout</span>
                <span class="text-sm">Logout</span>
            </a>
        </div>
    </aside>

    <main class="ml-64 flex-1 min-h-screen">
        <header class="sticky top-0 z-40 bg-white/85 backdrop-blur-md border-b border-slate-100 px-8 py-4 flex items-center justify-between">
            <div>
                <h2 class="text-xl font-extrabold text-blue-900">Dashboard Manajemen Dokter</h2>
                <p class="text-xs text-slate-500 font-medium">Konsultasi pasien, riwayat berlapis, tindakan, resep, dan kontrol stok obat.</p>
            </div>
            <div class="text-right">
                <p class="text-sm font-semibold text-slate-700">{{ auth()->user()->name ?? 'Dokter' }}</p>
                <p class="text-xs text-slate-500 uppercase tracking-wider">Role: Dokter</p>
            </div>
        </header>

        <div class="px-8 py-8 space-y-8">
            @if (session('status'))
                <div class="rounded-2xl bg-emerald-50 border border-emerald-100 text-emerald-700 px-5 py-4 text-sm font-semibold">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="rounded-2xl bg-red-50 border border-red-100 text-red-700 px-5 py-4 text-sm font-semibold">
                    {{ $errors->first() }}
                </div>
            @endif

            <section class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
                <article class="bg-white rounded-[2rem] p-6 shadow-sm">
                    <p class="text-[10px] uppercase tracking-widest font-bold text-slate-400">Total Pasien</p>
                    <h3 class="text-3xl font-black mt-2">{{ $stats['total_patients'] }}</h3>
                </article>
                <article class="bg-white rounded-[2rem] p-6 shadow-sm">
                    <p class="text-[10px] uppercase tracking-widest font-bold text-slate-400">Kunjungan Hari Ini</p>
                    <h3 class="text-3xl font-black mt-2">{{ $stats['today_visits'] }}</h3>
                </article>
                <article class="bg-white rounded-[2rem] p-6 shadow-sm">
                    <p class="text-[10px] uppercase tracking-widest font-bold text-slate-400">Obat Ready</p>
                    <h3 class="text-3xl font-black mt-2 text-emerald-600">{{ $stats['ready_medicines'] }}</h3>
                </article>
                <article class="bg-white rounded-[2rem] p-6 shadow-sm">
                    <p class="text-[10px] uppercase tracking-widest font-bold text-slate-400">Obat Not Ready</p>
                    <h3 class="text-3xl font-black mt-2 text-red-600">{{ $stats['not_ready_medicines'] }}</h3>
                </article>
            </section>

            <section class="grid grid-cols-1 xl:grid-cols-5 gap-8">
                <article id="konsultasi" class="xl:col-span-3 bg-white rounded-[2.5rem] p-8 shadow-sm">
                    <div class="mb-6">
                        <h3 class="text-xl font-extrabold text-blue-900">Terima Konsultasi Pasien</h3>
                        <p class="text-sm text-slate-500">Input pasien baru atau pilih pasien lama. Setiap kunjungan akan masuk riwayat berlapis.</p>
                    </div>

                    <form method="POST" action="{{ route('dokter.consultations.store') }}" class="space-y-4">
                        @csrf

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-widest text-slate-500 mb-2">Pilih Pasien (Opsional)</label>
                            <select name="patient_id" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm">
                                <option value="">Pasien baru (isi data di bawah)</option>
                                @foreach ($patients as $patient)
                                    <option value="{{ $patient->id }}" @selected((string) old('patient_id') === (string) $patient->id)>
                                        {{ $patient->medical_record_number }} - {{ $patient->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-widest text-slate-500 mb-2">Nama Pasien Baru</label>
                                <input type="text" name="patient_name" value="{{ old('patient_name') }}" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm" placeholder="Contoh: Budi Santoso" />
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-widest text-slate-500 mb-2">Jenis Kelamin</label>
                                <select name="gender" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm">
                                    <option value="">- Pilih -</option>
                                    <option value="laki_laki" @selected(old('gender') === 'laki_laki')>Laki-laki</option>
                                    <option value="perempuan" @selected(old('gender') === 'perempuan')>Perempuan</option>
                                    <option value="lainnya" @selected(old('gender') === 'lainnya')>Lainnya</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-widest text-slate-500 mb-2">Tanggal Lahir</label>
                                <input type="date" name="date_of_birth" value="{{ old('date_of_birth') }}" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm" />
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-widest text-slate-500 mb-2">No HP</label>
                                <input type="text" name="phone" value="{{ old('phone') }}" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm" placeholder="08xxxxxxxxxx" />
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-widest text-slate-500 mb-2">Alamat Pasien</label>
                            <input type="text" name="address" value="{{ old('address') }}" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm" placeholder="Alamat lengkap pasien" />
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-widest text-slate-500 mb-2">Tanggal/Jam Kunjungan</label>
                            <input type="datetime-local" name="visit_date" value="{{ old('visit_date', now()->format('Y-m-d\TH:i')) }}" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm" />
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-widest text-slate-500 mb-2">Keluhan Pasien</label>
                            <textarea name="complaint" rows="3" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm" required>{{ old('complaint') }}</textarea>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-widest text-slate-500 mb-2">Diagnosis</label>
                                <textarea name="diagnosis" rows="3" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm">{{ old('diagnosis') }}</textarea>
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-widest text-slate-500 mb-2">Tindakan Dokter</label>
                                <textarea name="action_taken" rows="3" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm">{{ old('action_taken') }}</textarea>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-widest text-slate-500 mb-2">Catatan Tambahan</label>
                            <textarea name="visit_notes" rows="2" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm">{{ old('visit_notes') }}</textarea>
                        </div>

                        <button type="submit" class="inline-flex items-center justify-center px-6 py-3 rounded-xl bg-primary text-white text-sm font-bold hover:bg-primary-container transition-colors">
                            Simpan Konsultasi Pasien
                        </button>
                    </form>
                </article>

                <article id="obat" class="xl:col-span-2 bg-white rounded-[2.5rem] p-8 shadow-sm">
                    <div class="mb-6">
                        <h3 class="text-xl font-extrabold text-blue-900">Akses Data Obat & Stok</h3>
                        <p class="text-sm text-slate-500">Lihat status ready/not ready. Dokter juga bisa update stok obat.</p>
                    </div>

                    <div class="space-y-4 max-h-[760px] overflow-y-auto pr-1">
                        @forelse ($medicines as $medicine)
                            <div class="rounded-2xl border border-slate-100 bg-slate-50/60 p-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="font-bold text-slate-800">{{ $medicine->name }}</p>
                                        <p class="text-xs text-slate-500">
                                            Merek: {{ $medicine->trade_name ?: '-' }} | Dosis: {{ $medicine->dosage ?: '-' }} | Kategori: {{ $medicine->category ?: '-' }}
                                        </p>
                                    </div>
                                    <span class="px-3 py-1 rounded-full text-[10px] font-bold {{ $medicine->stock > 0 ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">
                                        {{ $medicine->stock > 0 ? 'READY' : 'NOT READY' }}
                                    </span>
                                </div>

                                <div class="mt-3 flex items-center justify-between gap-3">
                                    <p class="text-sm font-semibold">Stok: {{ $medicine->stock }} {{ $medicine->unit }}</p>
                                    <form method="POST" action="{{ route('dokter.medicines.update', $medicine) }}" class="flex items-center gap-2">
                                        @csrf
                                        @method('PATCH')
                                        <input type="number" min="0" name="stock" value="{{ $medicine->stock }}" class="w-24 rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-sm" />
                                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-primary text-white text-xs font-bold hover:bg-primary-container transition-colors">
                                            Update
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-slate-500">Belum ada data obat. Jalankan seeder untuk data awal.</p>
                        @endforelse
                    </div>
                </article>
            </section>

            <section id="riwayat" class="bg-white rounded-[2.5rem] p-8 shadow-sm">
                <div class="mb-6">
                    <h3 class="text-xl font-extrabold text-blue-900">Riwayat Pasien (Layer / Menumpuk)</h3>
                    <p class="text-sm text-slate-500">Setiap kunjungan disimpan sebagai layer riwayat baru. Bisa update riwayat dan tambah resep obat.</p>
                </div>

                <div class="space-y-6">
                    @forelse ($visits as $visit)
                        <article class="rounded-2xl border border-slate-100 bg-slate-50/60 p-5">
                            <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                                <div>
                                    <h4 class="text-lg font-bold text-slate-800">
                                        {{ $visit->patient?->name ?? 'Pasien tidak ditemukan' }}
                                    </h4>
                                    <p class="text-xs text-slate-500">
                                        {{ $visit->patient?->medical_record_number ?? '-' }} |
                                        {{ optional($visit->visit_date)->format('d M Y H:i') }} |
                                        Dokter: {{ $visit->doctor?->name ?? '-' }}
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
                            <p class="text-sm text-slate-500">Belum ada riwayat kunjungan pasien. Tambahkan konsultasi pertama dari form di atas.</p>
                        </div>
                    @endforelse
                </div>
            </section>

            <section class="bg-white rounded-[2.5rem] p-8 shadow-sm">
                <div class="mb-6">
                    <h3 class="text-xl font-extrabold text-blue-900">Track Record Pasien</h3>
                    <p class="text-sm text-slate-500">Saat pasien datang lagi, rekam jejak kunjungan sebelumnya akan tetap tersimpan dan bisa ditelusuri.</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="text-[10px] uppercase tracking-widest text-slate-400 font-black border-b border-slate-100">
                                <th class="py-3">No. RM</th>
                                <th class="py-3">Nama Pasien</th>
                                <th class="py-3">Jenis Kelamin</th>
                                <th class="py-3">No HP</th>
                                <th class="py-3">Total Kunjungan</th>
                                <th class="py-3">Kunjungan Terakhir</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50 text-sm">
                            @forelse ($patients as $patient)
                                <tr>
                                    <td class="py-4 font-bold">{{ $patient->medical_record_number }}</td>
                                    <td class="py-4">{{ $patient->name }}</td>
                                    <td class="py-4">{{ $patient->gender ? ucfirst(str_replace('_', ' ', $patient->gender)) : '-' }}</td>
                                    <td class="py-4">{{ $patient->phone ?: '-' }}</td>
                                    <td class="py-4">{{ $patient->visits_count }}</td>
                                    <td class="py-4">
                                        {{ $patient->visits_max_visit_date ? \Illuminate\Support\Carbon::parse($patient->visits_max_visit_date)->format('d M Y H:i') : '-' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-8 text-center text-slate-500">
                                        Belum ada data pasien.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </main>
</div>
@endsection
