@extends('ui.master.layout')

@section('master_title', 'APOTEK SUMBER SEHAT - Ubah Password')
@section('master_heading', 'Ubah Password')
@section('master_subheading', 'Ganti password akun master admin yang sedang login.')

@section('master_content')
    <section class="mx-auto w-full max-w-2xl rounded-[2.5rem] bg-white p-8 shadow-sm">
        <div class="mb-6">
            <h3 class="text-xl font-extrabold text-blue-900">Form Ubah Password</h3>
            <p class="text-sm text-slate-500">Akun: <span class="font-semibold text-slate-700">{{ $currentUser?->email }}</span></p>
        </div>

        <form method="POST" action="{{ route('master-admin.password.change.update') }}" class="space-y-4">
            @csrf
            @method('PATCH')
            <div>
                <label class="mb-1 block text-xs font-bold uppercase tracking-widest text-slate-500">Password Saat Ini</label>
                <input type="password" name="current_password" required class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm" />
            </div>
            <div>
                <label class="mb-1 block text-xs font-bold uppercase tracking-widest text-slate-500">Password Baru</label>
                <input type="password" name="password" required minlength="8" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm" />
            </div>
            <div>
                <label class="mb-1 block text-xs font-bold uppercase tracking-widest text-slate-500">Konfirmasi Password Baru</label>
                <input type="password" name="password_confirmation" required minlength="8" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm" />
            </div>
            <button type="submit" class="rounded-xl bg-primary px-5 py-3 text-sm font-bold text-white hover:bg-primary-container">
                Simpan Password Baru
            </button>
        </form>
    </section>
@endsection
