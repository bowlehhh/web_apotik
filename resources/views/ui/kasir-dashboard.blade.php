@extends('layouts.ui-shell')

@section('title', 'APOTEK SUMBER SEHAT - Kasir Dashboard')
@section('body_class', 'bg-surface text-on-surface overflow-hidden')

@section('content')
<div class="flex min-h-screen">
    <aside class="w-64 fixed left-0 top-0 h-screen bg-slate-50 border-r border-slate-100 p-4 flex flex-col">
        <div class="flex items-center gap-3 mb-8 px-2">
            <div class="w-10 h-10 bg-primary-container rounded-xl flex items-center justify-center text-white">
                <span class="material-symbols-outlined" style="font-variation-settings:'FILL' 1">medical_services</span>
            </div>
            <div>
                <h1 class="text-lg font-black text-blue-900">APOTEK SUMBER SEHAT</h1>
                <p class="text-[10px] uppercase tracking-widest text-slate-500 font-bold">Clinical Curator</p>
            </div>
        </div>

        <nav class="flex-1 space-y-1">
            <a href="{{ route('kasir.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-slate-600 hover:bg-slate-200/60">
                <span class="material-symbols-outlined">dashboard</span>
                <span class="text-sm font-medium">Dashboard</span>
            </a>
            <a href="{{ route('kasir.transaksi') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg bg-blue-50 text-blue-700 font-semibold">
                <span class="material-symbols-outlined">point_of_sale</span>
                <span class="text-sm">Transaksi Penjualan</span>
            </a>
            <a href="#" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-slate-600 hover:bg-slate-200/60">
                <span class="material-symbols-outlined">shopping_basket</span>
                <span class="text-sm font-medium">Keranjang</span>
            </a>
            <a href="#" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-slate-600 hover:bg-slate-200/60">
                <span class="material-symbols-outlined">history</span>
                <span class="text-sm font-medium">Riwayat</span>
            </a>
        </nav>

        <div class="pt-4 border-t border-slate-200 space-y-1">
            <a href="{{ route('logout.get') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-slate-600 hover:bg-slate-200/60">
                <span class="material-symbols-outlined">logout</span>
                <span class="text-sm">Logout</span>
            </a>
        </div>
    </aside>

    <main class="ml-64 flex-1 h-screen flex flex-col">
        <header class="h-20 px-8 flex items-center gap-6 border-b border-slate-100 bg-surface-container-low">
            <div class="flex-1 relative">
                <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-outline">search</span>
                <input type="text" placeholder="Cari Obat atau Scan Barcode" class="w-full pl-12 pr-4 py-3.5 rounded-xl bg-white border-none focus:ring-2 focus:ring-primary/20" />
            </div>
            <div class="flex items-center gap-3">
                <p class="text-sm font-bold text-primary hidden lg:block">Budi Darmawan</p>
                <img class="w-10 h-10 rounded-full object-cover" src="https://lh3.googleusercontent.com/aida-public/AB6AXuB4rXjYgekcXOTPVP6LeZdTYY8e6gcf3CWVldBnR7aAyu_JokQ7VKP1nNF0jijWcHnkp5YxWJFY2vKYQfCqXUlJgHMm6T8zyt6wPMsb3GImhHhPBWIAlXuhMD8acxuHzWfTkQUO8PacuLcOBbPwmFmuT5yBS8F-N8MRJCc4dwL67PYIaDarVM9ibv6tXKJnRAGLOawFykmyHRImm8zN4AM8gbSDLpQvW6f5eE3yhBlADKto57Pra1H_eePqSBWiI0uctGeorDmOivw" alt="Kasir" />
            </div>
        </header>

        <div class="flex-1 flex overflow-hidden">
            <section class="flex-1 p-8 overflow-y-auto custom-scrollbar">
                <div class="flex justify-between items-end mb-8">
                    <div>
                        <h2 class="text-2xl font-extrabold">Katalog Obat</h2>
                        <p class="text-sm text-outline">Menampilkan 42 produk tersedia</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                    @foreach ([
                        ['name' => 'Paracetamol 500mg', 'stock' => '124 box', 'price' => 'Rp 12.500', 'tag' => 'Tablet', 'img' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuCC35sjLc3OqQ74YOqOZCLaaOHPhCluCxjatfEAZiMdbL4xnjUrzgaPXG4iY7xSox5KTx3cNi9V5PiDFmL7jDmHIQv1rLHAzRAi144xyeW-NzIr1GjRkrnsrdJJqWbJtZ-BSuvdXlvbdDjpGS_PVquO0zxhCkLxPjgmaRGAUtFQ4VY_a_a6ZTEoasX4mjgS3hbSR8l5E49uSSCGK-k26WbWiQbb5EdxvbjYR_20R8hLcLqbN3dupWqL7styg_eAMgxPAa6nRbXdm9E'],
                        ['name' => 'Amoxicillin Syrup', 'stock' => '18 botol', 'price' => 'Rp 45.000', 'tag' => 'Cair', 'img' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuBIpO0mPk_Wv83YUb0pFna9zNcpJT-yBNH_EKh1G93as8kYZCuQ-MFKir4axs0iVI_mupW14xVN-yohCwny6e6bxrgrVaM8wKHiNNnGxhadHxi1IOEp-1b3LW4JHcSijYb3AgwI4VZcXEoYww3IxMVucNqVJiyVC-Z10RxMrO_NNS9EeKbrRy1-xIW7cK_lzlf9UyNfcdbjZi9CvTCLOzInmUuEoukUsodjL86WZFytUjOOAzDyBKGo1vNXmcCXTr6T0MoMYw8DZz0'],
                        ['name' => 'Hydrocortisone 1%', 'stock' => '4 unit', 'price' => 'Rp 32.200', 'tag' => 'Salep', 'img' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuB-BE9-gYtWUIEyyB-hBgZlIcO85QSzmKW46rhVB1AHOte-HsGHplh8jGpDRhNyBL600t5IulS0fLI8oW8xSWZn4Ouf0UJxhAVgSIgrHJzphhSavzu9lPac1vNPAEgPAx5kPJZdilbWqVQyD-gcpiYEFhzaI67AoE_8lW5g8JL54favm_gUIIzlYWma2H7tTDt-NOSILNVipdslRpFTgVlJugjhQ4O-hJ2bVdP4ItWVaNcDJH6oIVuAMDlkOtxggNBs7H1S3HdWiaA'],
                    ] as $item)
                        <article class="bg-white rounded-2xl p-5 shadow-sm hover:shadow-lg transition-all">
                            <div class="aspect-square rounded-xl overflow-hidden mb-4 bg-surface-container-low">
                                <img src="{{ $item['img'] }}" alt="{{ $item['name'] }}" class="w-full h-full object-cover" />
                            </div>
                            <span class="text-[10px] font-bold uppercase tracking-widest text-primary bg-primary/10 rounded-full px-2 py-1">{{ $item['tag'] }}</span>
                            <h3 class="text-lg font-bold mt-2">{{ $item['name'] }}</h3>
                            <div class="flex items-center justify-between mt-4">
                                <div>
                                    <p class="text-xs text-outline">Stok: {{ $item['stock'] }}</p>
                                    <p class="text-lg font-black text-primary">{{ $item['price'] }}</p>
                                </div>
                                <button class="w-10 h-10 rounded-full bg-primary-container text-white flex items-center justify-center">
                                    <span class="material-symbols-outlined" style="font-variation-settings:'FILL' 1">add</span>
                                </button>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>

            <aside class="w-[390px] border-l border-slate-200 bg-white flex flex-col">
                <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-extrabold">Keranjang</h2>
                        <p class="text-xs text-outline">ID Transaksi: <span class="text-primary font-bold">#TRX-99212</span></p>
                    </div>
                    <button class="text-outline"><span class="material-symbols-outlined">delete_sweep</span></button>
                </div>

                <div class="flex-1 p-6 space-y-5 overflow-y-auto custom-scrollbar">
                    @foreach ([
                        ['name' => 'Paracetamol 500mg', 'qty' => 2, 'price' => 'Rp 25.000'],
                        ['name' => 'Amoxicillin Syrup', 'qty' => 1, 'price' => 'Rp 45.000'],
                    ] as $cart)
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="font-bold text-sm">{{ $cart['name'] }}</p>
                                <p class="text-xs text-outline">{{ $cart['qty'] }} item</p>
                            </div>
                            <p class="font-black text-sm">{{ $cart['price'] }}</p>
                        </div>
                    @endforeach
                </div>

                <div class="p-6 border-t border-slate-100 bg-surface-container-low space-y-3">
                    <div class="flex justify-between text-sm"><span>Subtotal</span><span>Rp 70.000</span></div>
                    <div class="flex justify-between text-sm"><span>Diskon</span><span>- Rp 5.000</span></div>
                    <div class="flex justify-between text-sm"><span>PPN 11%</span><span>Rp 7.150</span></div>
                    <div class="flex justify-between items-center pt-3 border-t border-slate-200">
                        <span class="font-bold">Total Tagihan</span>
                        <span class="text-2xl font-black text-primary">Rp 72.150</span>
                    </div>
                    <button class="w-full py-4 rounded-2xl bg-primary text-white font-extrabold text-lg flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined" style="font-variation-settings:'FILL' 1">payments</span>
                        Bayar
                    </button>
                </div>
            </aside>
        </div>
    </main>
</div>
@endsection
