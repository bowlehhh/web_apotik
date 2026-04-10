@extends('layouts.ui-shell')

@section('title', 'APOTEK SUMBER SEHAT - Transaksi POS')
@section('body_class', 'bg-surface overflow-hidden')

@section('content')
<div class="flex min-h-screen">
    <aside class="w-64 fixed left-0 top-0 h-screen bg-slate-50 border-r border-slate-100 p-4 flex flex-col">
        <h1 class="text-lg font-black text-blue-900 px-2 mb-8">APOTEK SUMBER SEHAT</h1>
        <nav class="flex-1 space-y-1">
            <a href="{{ route('kasir.dashboard') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg text-slate-600 hover:bg-slate-200/60"><span class="material-symbols-outlined">dashboard</span><span class="text-sm font-semibold uppercase tracking-wider">Dashboard</span></a>
            <a href="{{ route('ui.data-obat') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg text-slate-600 hover:bg-slate-200/60"><span class="material-symbols-outlined">inventory_2</span><span class="text-sm font-semibold uppercase tracking-wider">Inventory</span></a>
            <a href="{{ route('kasir.transaksi') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg bg-blue-50 text-blue-700 font-semibold"><span class="material-symbols-outlined">point_of_sale</span><span class="text-sm font-semibold uppercase tracking-wider">Transactions</span></a>
        </nav>
        <button class="w-full mt-auto bg-primary py-4 text-white rounded-xl font-bold flex items-center justify-center gap-2">
            <span class="material-symbols-outlined">add_circle</span>
            New Prescription
        </button>
    </aside>

    <main class="ml-64 flex-1 h-screen flex flex-col">
        <header class="h-20 bg-white/90 backdrop-blur-md border-b border-slate-100 px-8 flex items-center justify-between">
            <h2 class="text-2xl font-extrabold text-blue-900">Transaksi (POS)</h2>
            <div class="flex items-center gap-3">
                <button class="p-2 rounded-full hover:bg-slate-100"><span class="material-symbols-outlined">notifications</span></button>
                <img class="w-9 h-9 rounded-full object-cover" src="https://lh3.googleusercontent.com/aida-public/AB6AXuAkUXRGhI-c7Y4zkG0m58ogEuOrGodPQwprwOpY3m4cbm67ENSW-OjlaHf9rgfRQdJI7fdi0hpvUcdp92c8xdpjFN5y3aTDD-o66-NHTn7CS8S1bQG_gtd1B9gbzh-9PFeT2YHaMvHa3O6PGlgXUyvRbJY4VrtEhrv4MT-Bv5X3oevolyaI1bSLameos0bPm5vJ-yeEgeKKRsH7zFHnsG4WT9yZNbxCNyZB_rSDtVBeyeYd3UyNK7LEJgceyAy8l25eqbNWENtyCfI" alt="Pharmacist" />
            </div>
        </header>

        <div class="flex-1 grid grid-cols-12 overflow-hidden">
            <section class="col-span-8 p-8 overflow-y-auto custom-scrollbar space-y-8">
                <div class="flex gap-4">
                    <div class="relative flex-1">
                        <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-outline">search</span>
                        <input type="text" placeholder="Search medicine, supplement, or medical devices..." class="w-full pl-12 pr-4 py-4 rounded-xl bg-surface-container-low border-none focus:ring-2 focus:ring-primary/20" />
                    </div>
                    <button class="px-6 rounded-xl bg-surface-container-high font-bold text-sm uppercase tracking-wider">Category</button>
                </div>

                <div class="grid grid-cols-3 gap-6">
                    @foreach ([
                        ['name' => 'Paracetamol 500mg', 'cat' => 'Analgesic', 'price' => 'Rp 12.500', 'status' => 'In Stock', 'img' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuD_Ty5h-pokei0vnI_ZcqF3W4IEtJZjwDoSexr6wF9NwXmeOIWGBesBbllHzA8313QJyGGn144RPJDwMXT2ooG_O8-V0EAP57vfTbgT7dPpD3HtkXOtzKyei3idFATt0hVMV2wQ3QhMOPbv66tmhYV3bWukTUtmPGznJz-MViISPz_AO-MJX1gS1dDfp8vNx2GEkuDa8MsgJNjOC5nS__wqnkEhPVJDuZu0ub9bHfE8rMc7Ha4SwqDPzXUTH1PPuVCcP9CVn75ua1Y'],
                        ['name' => 'Vitamin C 1000mg', 'cat' => 'Supplement', 'price' => 'Rp 45.000', 'status' => 'In Stock', 'img' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuAwrBqBZt2u0Y_EXp0oet9o-YtxDcLBTvO-sTNFm-CoCtGSIpUBbnlMNYiA4ozSsqinsgkuO3czi6PbrHnl0emhq4hnuy-qrMpJtdAOYEHIj59MU3GIUy-w7HjYUeyEgSDIU6iWNhagX3s3zrZZv6JhSse-3aS3DIlJxgNona3v_GcM9BNfUxk25mmKFZ-p94rMzhq_aiyJ-ElLS9nxPQM9In-Rll91vdqq-yN-nwwQn77wy8SVVd9mS_mmbudbIddYkbt-4tfvtlU'],
                        ['name' => 'Amoxicillin 250mg', 'cat' => 'Antibiotic', 'price' => 'Rp 28.900', 'status' => 'Limited', 'img' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuC8xEn3G54kY3rOfeTokEo9KvYtfpCRiCKnJQSVab_wbviQbr2AuLEMUHv6tdVJYYLfXHDEa4X_fnjE_5cbey7jKMonSVezlKOmXPJQgkNx8MChMKLj0GyRJo1M0gP6-YfD5D_vG6M0vUXOtciO9GHere_3z38H0FlpyYpZYKh947SSQtGjJfl4eAL0IKFsJXcusHJL10vUpJl3ol8v9jmIj6h-AKeDNVE8FJHrIehZq8KMB2qorLIK7y3vX2_kzxd024dP8fI1YAU'],
                    ] as $product)
                        <article class="bg-white p-5 rounded-2xl shadow-sm hover:shadow-lg hover:scale-[1.01] transition-all">
                            <div class="aspect-square rounded-xl overflow-hidden bg-surface-container-low mb-4">
                                <img src="{{ $product['img'] }}" alt="{{ $product['name'] }}" class="w-full h-full object-cover" />
                            </div>
                            <h3 class="font-bold leading-tight">{{ $product['name'] }}</h3>
                            <div class="flex justify-between items-end mt-3">
                                <div>
                                    <p class="text-[10px] uppercase tracking-widest text-slate-400 font-black">{{ $product['cat'] }}</p>
                                    <p class="text-lg font-black text-primary">{{ $product['price'] }}</p>
                                </div>
                                <span class="text-[10px] font-bold px-3 py-1 rounded-full {{ $product['status'] === 'Limited' ? 'bg-tertiary-fixed text-on-tertiary-fixed-variant' : 'bg-secondary-fixed text-on-secondary-fixed' }}">{{ $product['status'] }}</span>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>

            <section class="col-span-4 border-l border-slate-200 bg-surface-container-low p-8 flex flex-col">
                <div class="space-y-6 flex-1 overflow-y-auto custom-scrollbar pr-2">
                    <div class="bg-white p-5 rounded-2xl">
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-3">Customer Details</p>
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-xl bg-blue-50 text-primary flex items-center justify-center font-bold">AY</div>
                            <div>
                                <h4 class="font-bold text-sm">Ananda Yudistira</h4>
                                <p class="text-xs text-on-surface-variant">Gold Member • 2.450 Points</p>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Order Summary (2)</p>
                        @foreach ([
                            ['name' => 'Paracetamol 500mg', 'qty' => 2, 'price' => 'Rp 12.500'],
                            ['name' => 'Vitamin C 1000mg', 'qty' => 1, 'price' => 'Rp 45.000'],
                        ] as $item)
                            <div class="bg-white p-4 rounded-2xl flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-bold">{{ $item['name'] }}</p>
                                    <p class="text-xs text-primary font-black">{{ $item['price'] }}</p>
                                </div>
                                <div class="px-3 py-1 bg-surface-container-low rounded-full text-sm font-bold">{{ $item['qty'] }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="pt-6 border-t border-slate-200 space-y-4">
                    <div class="flex justify-between text-sm"><span class="text-on-surface-variant">Subtotal</span><span class="font-bold">Rp 70.000</span></div>
                    <div class="flex justify-between text-sm"><span class="text-on-surface-variant">Tax (11%)</span><span class="font-bold">Rp 7.700</span></div>
                    <div class="flex justify-between items-center"><span class="text-sm font-black uppercase tracking-widest text-slate-400">Total</span><span class="text-3xl font-black text-primary">Rp 77.700</span></div>
                    <button class="w-full py-5 rounded-2xl bg-primary-container text-white font-black text-lg flex items-center justify-center gap-2">
                        Selesaikan Transaksi
                        <span class="material-symbols-outlined">chevron_right</span>
                    </button>
                </div>
            </section>
        </div>
    </main>
</div>
@endsection
