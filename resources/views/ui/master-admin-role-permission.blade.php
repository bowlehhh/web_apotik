@extends('ui.master.layout')

@section('master_title', 'APOTEK SUMBER SEHAT - Role & Permission')
@section('master_heading', 'Role & Permission')
@section('master_subheading', 'Matriks akses per role untuk kontrol kewenangan sistem.')

@section('master_content')
    @php
        $matrixColumns = [
            'dashboard.view.global' => 'Dashboard Global',
            'medicines.view' => 'Data Obat',
            'medicines.update' => 'Update Obat',
            'barcode.input' => 'Input Barcode',
            'purchases.create' => 'Pembelian',
            'documentation.upload' => 'Dokumentasi',
            'reports.view' => 'Laporan',
            'users.update' => 'Kelola User',
            'users.reset_password' => 'Reset Password',
            'roles_permissions.view' => 'Lihat Role',
            'roles_permissions.manage' => 'Kelola Role',
            'audit_logs.view' => 'Audit Log',
        ];
    @endphp

    <section class="rounded-[2.5rem] bg-white p-8 shadow-sm">
        <div class="mb-6">
            <h3 class="text-xl font-extrabold text-blue-900">Role Permission Matrix</h3>
            <p class="text-sm text-slate-500">Tabel ini membedakan hak akses antar role secara jelas.</p>
        </div>

        <div class="overflow-x-auto rounded-xl border border-slate-100">
            <table class="w-full text-left">
                <thead>
                    <tr class="border-b border-slate-100 text-[10px] font-black uppercase tracking-widest text-slate-400">
                        <th class="px-3 py-3">Role</th>
                        @foreach ($matrixColumns as $columnLabel)
                            <th class="px-3 py-3">{{ $columnLabel }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 text-sm">
                    @foreach ($roleLabels as $roleKey => $roleLabel)
                        @php
                            $grants = (array) ($permissionMatrix[$roleKey] ?? []);
                            $isAllGranted = in_array('*', $grants, true);
                        @endphp
                        <tr>
                            <td class="px-3 py-3 font-bold text-slate-800">{{ $roleLabel }}</td>
                            @foreach ($matrixColumns as $permissionKey => $columnLabel)
                                @php
                                    $granted = $isAllGranted || in_array($permissionKey, $grants, true);
                                @endphp
                                <td class="px-3 py-3">
                                    <span class="inline-flex items-center rounded-full px-2 py-1 text-[10px] font-bold {{ $granted ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                                        {{ $granted ? 'YA' : 'TIDAK' }}
                                    </span>
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
@endsection
