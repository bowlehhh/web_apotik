<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cetak Resep {{ $prescription->id }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 24px;
            color: #111827;
        }
        .sheet {
            max-width: 820px;
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
            <h1 class="title">Resep Dokter</h1>
            <p class="subtitle">APOTEK SUMBER SEHAT</p>
        </div>

        <div class="grid">
            <p><strong>ID Resep:</strong> RX-{{ str_pad((string) $prescription->id, 6, '0', STR_PAD_LEFT) }}</p>
            <p><strong>Tanggal:</strong> {{ optional($prescription->prescribed_at)->format('d M Y H:i') }}</p>
            <p><strong>Pasien:</strong> {{ $prescription->patient?->name ?? '-' }}</p>
            <p><strong>No. RM:</strong> {{ $prescription->patient?->medical_record_number ?? '-' }}</p>
            <p><strong>Dokter:</strong> {{ $prescription->doctor?->name ?? '-' }}</p>
            <p><strong>Status Tebus:</strong> {{ $prescription->is_dispensed ? 'Sudah ditebus' : 'Belum ditebus' }}</p>
            <p><strong>Kasir Tebus:</strong> {{ $prescription->dispensedBy?->name ?? '-' }}</p>
            <p><strong>Waktu Tebus:</strong> {{ optional($prescription->dispensed_at)->format('d M Y H:i') ?: '-' }}</p>
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width: 40px;">No</th>
                    <th>Obat</th>
                    <th style="width: 80px;">Qty</th>
                    <th>Aturan Pakai</th>
                    <th>Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($prescription->items as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $item->medicine?->name ?? '-' }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ $item->dosage_instructions }}</td>
                        <td>{{ $item->note ?: '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">Tidak ada item obat pada resep ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="note">
            <strong>Catatan Resep:</strong><br>
            {{ $prescription->notes ?: '-' }}
        </div>

        @if ($prescription->sale)
            <div class="note">
                <strong>Nomor Nota Penebusan:</strong> {{ $prescription->sale->invoice_number }}<br>
                <strong>Total:</strong> Rp {{ number_format((float) $prescription->sale->total_amount, 0, ',', '.') }}
            </div>
        @endif

        <div class="actions">
            <button class="btn btn-primary" onclick="window.print()">Cetak Sekarang</button>
            <button class="btn btn-muted" onclick="window.close()">Tutup</button>
        </div>
    </div>
</body>
</html>
