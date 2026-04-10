<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cetak Riwayat Transaksi Kasir</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 24px;
            color: #111827;
        }
        .sheet {
            max-width: 1120px;
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
        .meta {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
            margin-bottom: 14px;
            font-size: 12px;
        }
        .summary {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 10px;
            margin-bottom: 16px;
        }
        .summary-card {
            border: 1px solid #dbeafe;
            background: #eff6ff;
            border-radius: 8px;
            padding: 10px;
        }
        .summary-card p {
            margin: 0;
        }
        .summary-label {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.09em;
            color: #1d4ed8;
            font-weight: 700;
        }
        .summary-value {
            margin-top: 6px !important;
            font-size: 20px;
            font-weight: 800;
            color: #1e3a8a;
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
            vertical-align: top;
        }
        th {
            background: #eff6ff;
            color: #1e3a8a;
            font-weight: 700;
        }
        .text-right {
            text-align: right;
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
            <h1 class="title">Riwayat Transaksi Kasir</h1>
            <p class="subtitle">APOTEK SUMBER SEHAT</p>
        </div>

        <div class="meta">
            <p><strong>Dicetak pada:</strong> {{ optional($printedAt)->format('d M Y H:i') }}</p>
            <p><strong>Pencarian:</strong> {{ $historyFilters['q'] !== '' ? $historyFilters['q'] : '-' }}</p>
            <p><strong>Jenis transaksi:</strong>
                @if ($historyFilters['type'] === 'prescription')
                    Resep Dokter
                @elseif ($historyFilters['type'] === 'non_prescription')
                    Tanpa Resep
                @else
                    Semua Transaksi
                @endif
            </p>
            <p><strong>Periode:</strong>
                @if ($historyFilters['from'] || $historyFilters['to'])
                    {{ $historyFilters['from'] ? \Illuminate\Support\Carbon::parse($historyFilters['from'])->format('d M Y') : 'Awal' }}
                    s/d
                    {{ $historyFilters['to'] ? \Illuminate\Support\Carbon::parse($historyFilters['to'])->format('d M Y') : 'Sekarang' }}
                @else
                    Semua tanggal
                @endif
            </p>
        </div>

        <div class="summary">
            <div class="summary-card">
                <p class="summary-label">Total Transaksi</p>
                <p class="summary-value">{{ number_format((int) $historySummary['total_transactions']) }}</p>
            </div>
            <div class="summary-card">
                <p class="summary-label">Total Item</p>
                <p class="summary-value">{{ number_format((int) $historySummary['total_items']) }}</p>
            </div>
            <div class="summary-card">
                <p class="summary-label">Total Uang Masuk</p>
                <p class="summary-value">Rp {{ number_format((float) $historySummary['total_amount'], 0, ',', '.') }}</p>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width: 48px;">No</th>
                    <th style="width: 170px;">Nomor Nota</th>
                    <th style="width: 140px;">Tipe</th>
                    <th>Pasien/Pembeli</th>
                    <th style="width: 150px;">Kasir</th>
                    <th style="width: 100px;">Item</th>
                    <th style="width: 170px;">Total</th>
                    <th style="width: 170px;">Waktu</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($sales as $index => $sale)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $sale->invoice_number }}</td>
                        <td>{{ $sale->sale_type === 'prescription' ? 'Resep Dokter' : 'Tanpa Resep' }}</td>
                        <td>{{ $sale->patient_name ?: '-' }}</td>
                        <td>{{ $sale->cashier?->name ?: '-' }}</td>
                        <td>{{ number_format((int) $sale->total_items) }}</td>
                        <td class="text-right">Rp {{ number_format((float) $sale->total_amount, 0, ',', '.') }}</td>
                        <td>{{ optional($sale->sold_at)->format('d M Y H:i') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8">Belum ada transaksi yang sesuai filter.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="actions">
            <button class="btn btn-primary" onclick="window.print()">Cetak Sekarang</button>
            <button class="btn btn-muted" onclick="window.close()">Tutup</button>
        </div>
    </div>
</body>
</html>
