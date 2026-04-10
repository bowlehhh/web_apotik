@extends('layouts.ui-shell')

@section('title', 'Profil Pengguna | APOTEK SUMBER SEHAT')
@section('body_class', 'bg-surface text-on-surface')

@section('content')
@php
    $user = auth()->user();
@endphp

<div class="flex min-h-screen">
    <aside class="w-64 fixed left-0 top-0 h-screen bg-slate-50 border-r border-slate-100 p-4 flex flex-col">
        <div class="flex items-center gap-3 px-2 mb-10">
            <div class="w-10 h-10 rounded-xl bg-primary text-white flex items-center justify-center">
                <span class="material-symbols-outlined">health_and_safety</span>
            </div>
            <div>
                <h1 class="text-lg font-black text-blue-900 tracking-tight">APOTEK SUMBER SEHAT</h1>
                <p class="text-[10px] uppercase tracking-[0.05em] text-slate-500">Clinical Curator</p>
            </div>
        </div>

        <nav class="flex-1 space-y-1">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-4 py-3 text-slate-600 hover:bg-slate-200/50 rounded-lg">
                <span class="material-symbols-outlined">dashboard</span>
                <span class="text-sm uppercase tracking-wider">Dashboard</span>
            </a>
            @if (in_array($user->role, ['admin', 'master_admin'], true))
                <a href="{{ route('admin.data-obat') }}" class="flex items-center gap-3 px-4 py-3 text-slate-600 hover:bg-slate-200/50 rounded-lg">
                    <span class="material-symbols-outlined">inventory_2</span>
                    <span class="text-sm uppercase tracking-wider">Inventory</span>
                </a>
            @endif
            <a href="{{ route('profile') }}" class="flex items-center gap-3 px-4 py-3 bg-blue-50 text-blue-700 font-semibold rounded-lg">
                <span class="material-symbols-outlined">badge</span>
                <span class="text-sm uppercase tracking-wider">Profile</span>
            </a>
        </nav>

        <div class="mt-auto pt-4 space-y-1 border-t border-slate-100">
            <a href="{{ route('logout.get') }}" class="flex items-center gap-3 px-4 py-2 text-slate-500 hover:text-error">
                <span class="material-symbols-outlined">logout</span>
                <span class="text-xs uppercase tracking-wider">Logout</span>
            </a>
        </div>
    </aside>

    <main class="ml-64 flex-1 p-8 lg:p-12 bg-surface">
        <header class="mb-8">
            <h2 class="text-4xl font-extrabold tracking-tight text-on-surface mb-1">Profil Saya</h2>
            <p class="text-on-surface-variant">Data ini berasal dari akun pengguna yang login.</p>
        </header>

        @if (session('status'))
            <div class="mb-6 rounded-xl bg-emerald-50 text-emerald-700 px-4 py-3 text-sm font-semibold">
                {{ session('status') }}
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            <section class="lg:col-span-4 bg-white rounded-[2rem] p-8 shadow-sm border border-slate-100">
                <div class="flex flex-col items-center text-center">
                    <img class="w-32 h-32 rounded-3xl object-cover ring-4 ring-surface-container-low shadow-xl" src="{{ $user->avatarUrl() }}" alt="Foto profil {{ $user->name }}" />
                    <h3 class="text-2xl font-bold mt-5">{{ $user->name }}</h3>
                    <div class="mt-2 inline-flex items-center px-4 py-1 rounded-full bg-secondary-fixed text-on-secondary-fixed text-xs uppercase tracking-widest">
                        {{ $user->roleLabel() }}
                    </div>

                    <div class="mt-8 w-full grid grid-cols-2 gap-4 text-left">
                        <div class="p-4 bg-surface-container-low rounded-2xl">
                            <span class="block text-[10px] uppercase text-on-surface-variant mb-1">Joined</span>
                            <span class="text-sm font-semibold">{{ optional($user->created_at)->format('d M Y') }}</span>
                        </div>
                        <div class="p-4 bg-surface-container-low rounded-2xl">
                            <span class="block text-[10px] uppercase text-on-surface-variant mb-1">Status</span>
                            <span class="text-sm font-semibold text-primary">Active</span>
                        </div>
                    </div>
                </div>
            </section>

            <section class="lg:col-span-8 bg-white rounded-[2rem] p-8 shadow-sm border border-slate-100">
                <h4 class="text-xl font-bold mb-6">Biodata</h4>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-on-surface-variant mb-1">Nama Lengkap</p>
                        <div class="bg-surface-container-low rounded-xl px-4 py-3 text-sm font-semibold">{{ $user->name }}</div>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-on-surface-variant mb-1">Role</p>
                        <div class="bg-surface-container-low rounded-xl px-4 py-3 text-sm font-semibold">{{ $user->roleLabel() }}</div>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-on-surface-variant mb-1">Email</p>
                        <div class="bg-surface-container-low rounded-xl px-4 py-3 text-sm font-semibold">{{ $user->email }}</div>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-on-surface-variant mb-1">No HP</p>
                        <div class="bg-surface-container-low rounded-xl px-4 py-3 text-sm font-semibold">{{ $user->phone ?: '-' }}</div>
                    </div>
                    <div class="md:col-span-2">
                        <p class="text-[10px] font-bold uppercase tracking-widest text-on-surface-variant mb-1">Alamat</p>
                        <div class="bg-surface-container-low rounded-xl px-4 py-3 text-sm font-semibold">{{ $user->address ?: '-' }}</div>
                    </div>
                    <div class="md:col-span-2">
                        <p class="text-[10px] font-bold uppercase tracking-widest text-on-surface-variant mb-1">Biodata Singkat</p>
                        <div class="bg-surface-container-low rounded-xl px-4 py-3 text-sm leading-relaxed">{{ $user->bio ?: '-' }}</div>
                    </div>
                </div>
            </section>
        </div>
    </main>
</div>
@endsection
