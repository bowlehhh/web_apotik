@extends('ui.dokter.layout')

@section('dokter_title', 'Konsultasi Pasien')
@section('dokter_heading', 'Konsultasi Pasien')
@section('dokter_subheading', 'Input konsultasi pasien baru atau pasien lama. Setiap kunjungan akan tercatat sebagai layer riwayat.')

@section('dokter_content')
<section class="grid grid-cols-1 xl:grid-cols-5 gap-8">
    <article class="xl:col-span-3 bg-white rounded-[2.5rem] p-8 shadow-sm">
        <div class="mb-6">
            <h3 class="text-xl font-extrabold text-blue-900">Form Konsultasi</h3>
            <p class="text-sm text-slate-500">Jika pasien lama, pilih data pasien. Jika pasien baru, isi nama dan detailnya.</p>
        </div>

        <form method="POST" action="{{ route('dokter.consultations.store') }}" class="space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-bold uppercase tracking-widest text-slate-500 mb-2">Pilih Pasien (Opsional)</label>
                <select name="patient_id" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm">
                    <option value="">Pasien baru (isi data di bawah)</option>
                    @foreach ($patients as $patient)
                        @php
                            $age = $patient->age;
                            $height = $patient->height_cm !== null ? rtrim(rtrim(number_format((float) $patient->height_cm, 2, '.', ''), '0'), '.') . ' cm' : '-';
                            $weight = $patient->weight_kg !== null ? rtrim(rtrim(number_format((float) $patient->weight_kg, 2, '.', ''), '0'), '.') . ' kg' : '-';
                        @endphp
                        <option value="{{ $patient->id }}" @selected((string) old('patient_id') === (string) $patient->id)>
                            {{ $patient->medical_record_number }} - {{ $patient->name }} (Umur: {{ $age !== null ? $age.' th' : '-' }}, TB: {{ $height }}, BB: {{ $weight }})
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
                    <label class="block text-xs font-bold uppercase tracking-widest text-slate-500 mb-2">Tinggi Badan (cm)</label>
                    <input type="number" step="0.1" min="0" name="height_cm" value="{{ old('height_cm') }}" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm" placeholder="Contoh: 168.5" />
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-widest text-slate-500 mb-2">Berat Badan (kg)</label>
                    <input type="number" step="0.1" min="0" name="weight_kg" value="{{ old('weight_kg') }}" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm" placeholder="Contoh: 62.3" />
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
                <label class="block text-xs font-bold uppercase tracking-widest text-slate-500 mb-2">Catatan Pasien</label>
                <textarea name="patient_notes" rows="2" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm">{{ old('patient_notes') }}</textarea>
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
                <label class="block text-xs font-bold uppercase tracking-widest text-slate-500 mb-2">Catatan Kunjungan</label>
                <textarea name="visit_notes" rows="2" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm">{{ old('visit_notes') }}</textarea>
            </div>

            <button type="submit" class="inline-flex items-center justify-center px-6 py-3 rounded-xl bg-primary text-white text-sm font-bold hover:bg-primary-container transition-colors">
                Simpan Konsultasi Pasien
            </button>
        </form>
    </article>

    <article class="xl:col-span-2 bg-white rounded-[2.5rem] p-8 shadow-sm">
        <div class="mb-6">
            <h3 class="text-xl font-extrabold text-blue-900">Kunjungan Terbaru</h3>
            <p class="text-sm text-slate-500">Rekam konsultasi terakhir untuk referensi cepat.</p>
        </div>

        <div class="space-y-3 max-h-[760px] overflow-y-auto pr-1">
            @forelse ($recentVisits as $visit)
                <div class="rounded-2xl border border-slate-100 bg-slate-50/70 p-4">
                    @php
                        $age = $visit->patient?->age;
                        $height = $visit->patient?->height_cm !== null ? rtrim(rtrim(number_format((float) $visit->patient->height_cm, 2, '.', ''), '0'), '.') . ' cm' : '-';
                        $weight = $visit->patient?->weight_kg !== null ? rtrim(rtrim(number_format((float) $visit->patient->weight_kg, 2, '.', ''), '0'), '.') . ' kg' : '-';
                    @endphp
                    <p class="font-bold text-slate-800">{{ $visit->patient?->name ?? '-' }}</p>
                    <p class="text-xs text-slate-500">{{ $visit->patient?->medical_record_number ?? '-' }}</p>
                    <p class="text-xs text-slate-500 mt-1">Umur: {{ $age !== null ? $age.' tahun' : '-' }} | TB: {{ $height }} | BB: {{ $weight }}</p>
                    <p class="text-sm text-slate-600 mt-2">Keluhan: {{ $visit->complaint }}</p>
                    <p class="text-xs text-slate-500 mt-1">{{ optional($visit->visit_date)->format('d M Y H:i') }}</p>
                </div>
            @empty
                <p class="text-sm text-slate-500">Belum ada data kunjungan pasien.</p>
            @endforelse
        </div>
    </article>
</section>
@endsection
