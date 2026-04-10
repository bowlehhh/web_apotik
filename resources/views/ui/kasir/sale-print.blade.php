<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cetak Transaksi {{ $sale->invoice_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 24px;
            color: #111827;
        }
        .sheet {
            max-width: 900px;
            margin: 0 auto;
            border: 1px solid #d1d5db;
            border-radius: 10px;
            padding: 24px;
        }
        .header {
            border-bottom: 2px solid #1d4ed8;
            padding-bottom: 12px;
            margin-bottom: 16px;
        }
        .title {
            font-size: 24px;
            font-weight: 800;
            margin: 0;
            color: #1e3a8a;
        }
        .subtitle {
            margin: 4px 0 0;
            font-size: 12px;
            color: #4b5563;
        }
        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 16px;
            font-size: 13px;
        }
        .grid p {
            margin: 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
            margin-bottom: 16px;
        }
        th, td {
            border: 1px solid #d1d5db;
            padding: 8px;
            text-align: left;
            font-size: 12px;
        }
        th {
            background: #eff6ff;
            color: #1e3a8a;
            font-weight: 700;
        }
        .text-right {
            text-align: right;
        }
        .note {
            background: #f8fafc;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 10px;
            font-size: 12px;
            margin-bottom: 16px;
        }
        .actions {
            margin-top: 20px;
            display: flex;
            gap: 10px;
        }
        .btn {
            border: 0;
            border-radius: 8px;
            padding: 10px 14px;
            font-weight: 700;
            cursor: pointer;
        }
        .btn-primary {
            background: #1d4ed8;
            color: #fff;
        }
        .btn-muted {
            background: #e5e7eb;
            color: #1f2937;
        }
        @media print {
            .actions {
                display: none;
            }
            body {
                margin: 0;
            }
            .sheet {
                border: 0;
            }
        }
    </style>
</head>
<body>
    <div class="sheet">
        <div class="header">
            <h1 class="title">Nota Transaksi Kasir</h1>
            <p class="subtitle">APOTEK SUMBER SEHAT</p>
        </div>

        <div class="grid">
            <p><strong>Nomor Nota:</strong> {{ $sale->invoice_number }}</p>
            <p><strong>Tanggal:</strong> {{ optional($sale->sold_at)->format('d M Y H:i') }}</p>
            <p><strong>Jenis:</strong> {{ $sale->sale_type === 'prescription' ? 'Resep Dokter' : 'Tanpa Resep' }}</p>
            <p><strong>Kasir:</strong> {{ $sale->cashier?->name ?? '-' }}</p>
            <p><strong>Pasien/Pembeli:</strong> {{ $sale->patient_name ?: '-' }}</p>
            <p><strong>Total Item:</strong> {{ number_format((int) $sale->total_items) }}</p>
            <p><strong>Total Bayar:</strong> Rp {{ number_format((float) $sale->total_amount, 0, ',', '.') }}</p>
            <p><strong>ID Resep:</strong> {{ $sale->prescription_id ? ('RX-'.str_pad((string) $sale->prescription_id, 6, '0', STR_PAD_LEFT)) : '-' }}</p>
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width: 40px;">No</th>
                    <th>Nama Obat</th>
                    <th style="width: 90px;">Qty</th>
                    <th style="width: 160px;">Harga Satuan</th>
                    <th style="width: 170px;">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($sale->items as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $item->medicine_name_snapshot }}</td>
                        <td>{{ number_format((int) $item->quantity) }}</td>
                        <td class="text-right">Rp {{ number_format((float) $item->unit_price, 0, ',', '.') }}</td>
                        <td class="text-right">Rp {{ number_format((float) $item->line_total, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">Item transaksi tidak ditemukan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="note">
            <strong>Catatan:</strong><br>
            {{ $sale->notes ?: '-' }}
        </div>

        <div class="actions">
            <button class="btn btn-primary" onclick="window.print()">Cetak Sekarang</button>
            <button class="btn btn-muted" onclick="window.close()">Tutup</button>
        </div>
    </div>
</body>
</html>
