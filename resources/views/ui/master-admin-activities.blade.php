@extends('ui.master.layout')

@section('master_title', 'APOTEK SUMBER SEHAT - Aktivitas Role')
@section('master_heading', 'Aktivitas Role')
@section('master_subheading', 'Audit aktivitas seluruh role dalam satu halaman khusus.')

@section('master_content')
    <section class="rounded-[2.5rem] bg-white p-8 shadow-sm">
        <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
            <div>
                <h3 class="text-xl font-extrabold text-blue-900">Pantauan Aktivitas Semua Role</h3>
                <p class="text-sm text-slate-500">Seluruh jejak aktivitas operasional tercatat otomatis.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                @foreach ($roleLabels as $roleKey => $roleLabel)
                    <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-700">
                        {{ $roleLabel }}: {{ number_format((int) ($activityRoleStats[$roleKey] ?? 0)) }}
                    </span>
                @endforeach
            </div>
        </div>

        <form method="GET" action="{{ route('master-admin.activities.index') }}" class="mb-5 flex flex-wrap items-center gap-3">
            <select name="per_page" class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-2 text-sm">
                @foreach (($perPageOptions ?? [10, 25, 50, 100]) as $option)
                    <option value="{{ $option }}" @selected((int) ($perPage ?? 50) === (int) $option)>
                        {{ number_format((int) $option) }} baris
                    </option>
                @endforeach
            </select>
            <button type="submit" class="rounded-xl bg-slate-100 px-4 py-2 text-sm font-bold text-slate-700 hover:bg-slate-200">
                Tampilkan
            </button>
        </form>

        <div class="overflow-x-auto rounded-xl border border-slate-100">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Waktu</th>
                        <th class="px-4 py-3">Role</th>
                        <th class="px-4 py-3">Pengguna</th>
                        <th class="px-4 py-3">Modul</th>
                        <th class="px-4 py-3">Aksi</th>
                        <th class="px-4 py-3">Detail</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($recentActivities as $activity)
                        <tr>
                            <td class="px-4 py-3 text-slate-700">
                                <div class="font-semibold">{{ optional($activity->created_at)->format('d M Y') ?: '-' }}</div>
                                <div class="mt-1 text-xs text-slate-500">{{ optional($activity->created_at)->format('H:i:s') ?: '-' }}</div>
                            </td>
                            <td class="px-4 py-3">
                                @php
                                    $roleClass = match ($activity->actor_role) {
                                        'dokter' => 'bg-indigo-50 text-indigo-700',
                                        'kasir' => 'bg-emerald-50 text-emerald-700',
                                        'master_admin' => 'bg-fuchsia-50 text-fuchsia-700',
                                        default => 'bg-blue-50 text-blue-700',
                                    };
                                @endphp
                                <span class="inline-flex rounded-full px-3 py-1 text-xs font-bold {{ $roleClass }}">
                                    {{ strtoupper(str_replace('_', ' ', $activity->actor_role ?: 'unknown')) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 font-semibold text-slate-800">{{ $activity->actor_name ?: ($activity->actor?->name ?? '-') }}</td>
                            <td class="px-4 py-3 text-slate-700">{{ $activity->module }}</td>
                            <td class="px-4 py-3 text-slate-700">{{ $activity->action }}</td>
                            <td class="px-4 py-3 text-xs text-slate-600">{{ $activity->description }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-slate-500">Belum ada aktivitas yang tercatat.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $recentActivities->links() }}
        </div>
    </section>
@endsection
