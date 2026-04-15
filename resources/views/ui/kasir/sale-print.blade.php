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
        .table-large {
            display: table;
        }
        .table-receipt {
            display: none;
        }
        .receipt-divider {
            display: none;
        }
        .receipt-total {
            display: none;
        }
        .receipt-footer {
            display: none;
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
            flex-wrap: wrap;
            align-items: center;
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
        .size-label {
            font-size: 12px;
            color: #374151;
            font-weight: 700;
        }
        .size-select {
            border: 1px solid #d1d5db;
            border-radius: 8px;
            padding: 9px 10px;
            font-size: 12px;
            color: #111827;
            background: #fff;
        }
        .btn-secondary {
            background: #0f766e;
            color: #fff;
        }
        .print-hint {
            width: 100%;
            margin: 2px 0 0;
            font-size: 11px;
            color: #4b5563;
        }

        body.print-size-receipt {
            margin: 8px;
        }
        body.print-size-receipt .sheet {
            max-width: 80mm;
            border: 0;
            border-radius: 0;
            padding: 6px 8px 10px;
            box-shadow: none;
        }
        body.print-size-receipt .title {
            font-size: 14px;
            text-align: center;
            letter-spacing: 0.02em;
        }
        body.print-size-receipt .subtitle {
            font-size: 10px;
            text-align: center;
        }
        body.print-size-receipt .header {
            border-bottom: 0;
            margin-bottom: 8px;
            padding-bottom: 4px;
        }
        body.print-size-receipt .grid {
            grid-template-columns: 1fr;
            gap: 3px;
            font-size: 11px;
            margin-bottom: 8px;
            line-height: 1.3;
        }
        body.print-size-receipt th,
        body.print-size-receipt td {
            font-size: 10px;
            padding: 5px 4px;
            word-break: break-word;
            overflow-wrap: anywhere;
        }
        body.print-size-receipt .table-large {
            display: none;
        }
        body.print-size-receipt .table-receipt {
            display: table;
            table-layout: fixed;
            margin-top: 4px;
            margin-bottom: 6px;
            border-top: 1px dashed #9ca3af;
            border-bottom: 1px dashed #9ca3af;
        }
        body.print-size-receipt .table-receipt th {
            background: #fff;
            border-top: 0;
            border-left: 0;
            border-right: 0;
            border-bottom: 1px solid #d1d5db;
            font-size: 9.5px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #374151;
            padding-top: 6px;
            padding-bottom: 6px;
        }
        body.print-size-receipt .table-receipt td {
            border-left: 0;
            border-right: 0;
            border-top: 0;
            border-bottom: 1px dashed #d1d5db;
        }
        body.print-size-receipt .table-receipt tbody tr:last-child td {
            border-bottom: 0;
        }
        body.print-size-receipt .table-receipt th:nth-child(1),
        body.print-size-receipt .table-receipt td:nth-child(1) {
            width: 42%;
        }
        body.print-size-receipt .table-receipt th:nth-child(2),
        body.print-size-receipt .table-receipt td:nth-child(2) {
            width: 10%;
            text-align: center;
        }
        body.print-size-receipt .table-receipt th:nth-child(3),
        body.print-size-receipt .table-receipt td:nth-child(3) {
            width: 24%;
            text-align: right;
        }
        body.print-size-receipt .table-receipt th:nth-child(4),
        body.print-size-receipt .table-receipt td:nth-child(4) {
            width: 24%;
            text-align: right;
        }
        body.print-size-receipt .note {
            font-size: 10px;
            padding: 6px 7px;
            margin-bottom: 6px;
            border-radius: 6px;
            background: #fff;
            border-style: dashed;
        }
        body.print-size-receipt .receipt-divider {
            display: block;
            border-top: 1px dashed #9ca3af;
            margin: 6px 0;
        }
        body.print-size-receipt .receipt-total {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 6px 0 4px;
            padding: 6px 4px;
            border: 1px solid #111827;
            border-radius: 6px;
            background: #f9fafb;
        }
        body.print-size-receipt .receipt-total-label {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }
        body.print-size-receipt .receipt-total-value {
            font-size: 12px;
            font-weight: 800;
        }
        body.print-size-receipt .receipt-footer {
            display: block;
            text-align: center;
            margin-top: 8px;
            font-size: 9px;
            color: #4b5563;
            line-height: 1.35;
        }
        body.print-size-a4 .table-large {
            display: table;
        }
        body.print-size-a4 .table-receipt {
            display: none;
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
        <div class="receipt-divider"></div>

        <table class="table-large">
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
        <table class="table-receipt">
            <thead>
                <tr>
                    <th>Obat</th>
                    <th>Qty</th>
                    <th>Harga</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($sale->items as $item)
                    <tr>
                        <td>{{ $item->medicine_name_snapshot }}</td>
                        <td>{{ number_format((int) $item->quantity) }}</td>
                        <td>Rp {{ number_format((float) $item->unit_price, 0, ',', '.') }}</td>
                        <td>Rp {{ number_format((float) $item->line_total, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4">Item transaksi tidak ditemukan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="note">
            <strong>Catatan:</strong><br>
            {{ $sale->notes ?: '-' }}
        </div>
        <div class="receipt-total">
            <span class="receipt-total-label">Total Bayar</span>
            <span class="receipt-total-value">Rp {{ number_format((float) $sale->total_amount, 0, ',', '.') }}</span>
        </div>
        <p class="receipt-footer">
            Terima kasih telah berbelanja.<br>
            Simpan struk ini sebagai bukti transaksi.
        </p>

        <div class="actions">
            <label class="size-label" for="print_size_mode">Ukuran Cetak</label>
            <select id="print_size_mode" class="size-select">
                <option value="receipt" selected>Struk Kecil (80mm)</option>
                <option value="a4">Besar (A4)</option>
            </select>
            <button class="btn btn-secondary" id="btn_print_receipt" type="button">Cetak Struk Kecil</button>
            <button class="btn btn-primary" id="btn_print_large" type="button">Cetak Besar</button>
            <button class="btn btn-muted" onclick="window.close()">Tutup</button>
            <p class="print-hint">Pilih ukuran sesuai printer: thermal pakai struk kecil, printer biasa pakai besar (A4).</p>
        </div>
    </div>
    <script>
        (function () {
            const sizeSelect = document.getElementById('print_size_mode');
            const receiptButton = document.getElementById('btn_print_receipt');
            const largeButton = document.getElementById('btn_print_large');
            const pageStyleId = 'dynamic-print-page-size';

            const applyPrintSize = (mode) => {
                document.body.classList.remove('print-size-receipt', 'print-size-a4');
                document.body.classList.add(mode === 'receipt' ? 'print-size-receipt' : 'print-size-a4');

                let styleElement = document.getElementById(pageStyleId);
                if (!styleElement) {
                    styleElement = document.createElement('style');
                    styleElement.id = pageStyleId;
                    document.head.appendChild(styleElement);
                }

                styleElement.textContent = mode === 'receipt'
                    ? '@page { size: 80mm auto; margin: 4mm; }'
                    : '@page { size: A4 portrait; margin: 12mm; }';

                if (sizeSelect) {
                    sizeSelect.value = mode;
                }
            };

            const printWithMode = (mode) => {
                applyPrintSize(mode);
                setTimeout(() => {
                    window.print();
                }, 50);
            };

            sizeSelect?.addEventListener('change', () => {
                const selectedMode = sizeSelect.value === 'receipt' ? 'receipt' : 'a4';
                applyPrintSize(selectedMode);
            });

            receiptButton?.addEventListener('click', () => {
                printWithMode('receipt');
            });

            largeButton?.addEventListener('click', () => {
                printWithMode('a4');
            });

            applyPrintSize('receipt');
        })();
    </script>
</body>
</html>
