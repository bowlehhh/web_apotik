@extends('ui.master.layout')

@section('master_title', 'APOTEK SUMBER SEHAT - Reset Password User')
@section('master_heading', 'Reset Password User')
@section('master_subheading', 'Halaman khusus reset password akun user selain akun master yang sedang login.')

@section('master_content')
    <section class="rounded-[2.5rem] bg-white p-8 shadow-sm">
        <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
            <div>
                <h3 class="text-xl font-extrabold text-blue-900">Daftar User</h3>
                <p class="text-sm text-slate-500">Fungsi halaman ini hanya untuk reset password user.</p>
            </div>
            <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-bold text-blue-700">
                Total: {{ number_format((int) $users->total()) }} user
            </span>
        </div>

        <form method="GET" action="{{ route('master-admin.password.reset') }}" class="mb-5 grid grid-cols-1 gap-3 md:grid-cols-4">
            <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Cari nama, email, role" class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm md:col-span-2" />
            <select name="per_page" class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm">
                @foreach (($perPageOptions ?? [10, 25, 50]) as $option)
                    <option value="{{ $option }}" @selected((int) ($filters['per_page'] ?? 25) === (int) $option)>
                        {{ number_format((int) $option) }} / halaman
                    </option>
                @endforeach
            </select>
            <button type="submit" class="rounded-xl bg-slate-100 px-4 py-3 text-sm font-bold text-slate-700 hover:bg-slate-200">
                Terapkan
            </button>
        </form>

        <div class="overflow-x-auto rounded-xl border border-slate-100">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Nama</th>
                        <th class="px-4 py-3">Email</th>
                        <th class="px-4 py-3">Role</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Reset Password</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($users as $managedUser)
                        <tr>
                            <td class="px-4 py-3 font-bold text-slate-800">{{ $managedUser->name }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $managedUser->email }}</td>
                            <td class="px-4 py-3">
                                <span class="rounded-full bg-blue-50 px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-blue-700">
                                    {{ $roleLabels[$managedUser->role] ?? str_replace('_', ' ', $managedUser->role) }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex rounded-full px-3 py-1 text-[10px] font-bold {{ $managedUser->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">
                                    {{ $managedUser->is_active ? 'AKTIF' : 'NONAKTIF' }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <form method="POST" action="{{ route('master-admin.users.password.update', $managedUser) }}" class="min-w-[220px] space-y-2">
                                    @csrf
                                    @method('PATCH')
                                    <input type="password" name="password" required minlength="8" placeholder="Password baru (min 8)" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs" />
                                    <input type="password" name="password_confirmation" required minlength="8" placeholder="Konfirmasi password" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs" />
                                    <button type="submit" class="w-full rounded-xl bg-primary px-4 py-2 text-[11px] font-bold text-white hover:bg-primary-container">
                                        Reset Password
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-slate-500">Tidak ada user yang cocok dengan filter.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $users->links() }}
        </div>
    </section>
@endsection
