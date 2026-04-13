<?php

namespace App\Http\Controllers;

use App\Models\Medicine;
use App\Models\MedicinePurchaseLog;
use App\Support\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class AdminBarcodeController extends Controller
{
    public function index(): View
    {
        return view('ui.admin-barcode-input', [
            'barcode' => null,
            'medicine' => null,
            'searched' => false,
        ]);
    }

    public function lookup(Request $request): View
    {
        $request->merge([
            'barcode' => $this->normalizeBarcodeInput((string) $request->input('barcode')),
        ]);

        $validated = $request->validate([
            'barcode' => ['required', 'string', 'max:120'],
        ], [
            'barcode.required' => 'Kode barang/barcode wajib diisi.',
        ]);

        $barcode = trim($validated['barcode']);

        $medicine = Medicine::query()
            ->where('barcode', $barcode)
            ->first();

        return view('ui.admin-barcode-input', [
            'barcode' => $barcode,
            'medicine' => $medicine,
            'searched' => true,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->merge([
            'barcode' => $this->normalizeBarcodeInput((string) $request->input('barcode')),
        ]);

        $this->normalizeFormattedNumericInputs($request, ['stock'], ['buy_price', 'sell_price']);

        $validated = $request->validate([
            'barcode' => ['required', 'string', 'max:120'],
            'name' => ['required', 'string', 'max:255'],
            'trade_name' => ['nullable', 'string', 'max:255'],
            'dosage' => ['nullable', 'string', 'max:120'],
            'category' => ['nullable', 'string', 'max:120'],
            'stock' => ['required', 'integer', 'min:0'],
            'buy_price' => ['required', 'numeric', 'min:0'],
            'purchase_source' => ['required', 'string', 'max:255'],
            'sell_price' => ['nullable', 'numeric', 'min:0'],
            'expiry_date' => ['required', 'date'],
            'unit' => ['required', 'string', 'max:30'],
            'photo' => ['nullable', 'file', 'mimetypes:image/*', 'max:102400'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'barcode.required' => 'Barcode wajib diisi.',
            'name.required' => 'Nama barang wajib diisi.',
            'stock.required' => 'Stok awal wajib diisi.',
            'buy_price.required' => 'Harga beli wajib diisi.',
            'purchase_source.required' => 'Outlet atau tempat beli obat wajib diisi.',
            'expiry_date.required' => 'Tanggal kadaluarsa wajib diisi.',
            'photo.mimetypes' => 'File foto harus berupa gambar yang valid.',
            'photo.max' => 'Ukuran foto maksimal 100 MB.',
        ]);

        $cleanBarcode = trim($validated['barcode']);

        $photoPath = $request->hasFile('photo')
            ? $request->file('photo')->store('medicines/photos', 'public')
            : null;

        $storedMedicine = null;

        DB::transaction(function () use ($request, $validated, $photoPath, &$storedMedicine): void {
            $barcode = trim($validated['barcode']);
            $medicinePayload = [
                'barcode' => $barcode,
                'name' => $validated['name'],
                'trade_name' => $validated['trade_name'] ?? null,
                'dosage' => $validated['dosage'] ?? null,
                'category' => $validated['category'] ?? null,
                'stock' => (int) $validated['stock'],
                'buy_price' => (float) $validated['buy_price'],
                'sell_price' => array_key_exists('sell_price', $validated)
                    ? (float) $validated['sell_price']
                    : 0,
                'expiry_date' => $validated['expiry_date'] ?? null,
                'photo_path' => $photoPath,
                'unit' => $validated['unit'],
                'purchase_source' => $validated['purchase_source'],
                'is_active' => $request->boolean('is_active', true),
            ];

            if (Schema::hasColumn('medicines', 'entry_source')) {
                $medicinePayload['entry_source'] = Medicine::ENTRY_SOURCE_BARCODE;
            }

            $storedMedicine = Medicine::query()->create($medicinePayload);

            MedicinePurchaseLog::query()->create([
                'medicine_id' => $storedMedicine->id,
                'created_by' => $request->user()?->id,
                'quantity' => (int) $validated['stock'],
                'buy_price' => (float) $validated['buy_price'],
                'purchase_source' => $validated['purchase_source'] ?? 'Scan barcode admin',
                'expiry_date' => $validated['expiry_date'] ?? null,
                'photo_path' => $photoPath,
                'purchased_at' => now(),
                'notes' => $validated['notes'] ?? 'Input barang baru melalui scan barcode.',
            ]);
        });

        ActivityLogger::log(
            $request->user(),
            'admin.barcode',
            'create_medicine_from_barcode',
            'Admin menambahkan obat baru melalui input barcode.',
            $storedMedicine,
            [
                'medicine_name' => $validated['name'],
                'barcode' => $cleanBarcode,
                'stock' => (int) $validated['stock'],
                'unit' => $validated['unit'],
                'buy_price' => (float) $validated['buy_price'],
                'sell_price' => (float) ($validated['sell_price'] ?? 0),
                'purchase_source' => $validated['purchase_source'],
                'expiry_date' => $validated['expiry_date'] ?? null,
            ]
        );

        return redirect()
            ->route('admin.barcode.lookup', ['barcode' => $cleanBarcode])
            ->with('status', 'Barang baru berhasil ditambahkan dari barcode dan sudah tercatat di gudang.');
    }

    private function normalizeBarcodeInput(string $input): string
    {
        $value = trim($input);
        if ($value === '') {
            return '';
        }

        preg_match_all('/\d{8,14}/', $value, $numericMatches);
        $numericCandidates = $numericMatches[0] ?? [];
        if ($numericCandidates !== []) {
            usort($numericCandidates, static fn (string $a, string $b): int => strlen($b) <=> strlen($a));

            return $numericCandidates[0];
        }

        $compactAlphaNumeric = preg_replace('/[^A-Za-z0-9]/', '', $value) ?? '';
        if ($compactAlphaNumeric !== '' && strlen($compactAlphaNumeric) >= 6 && strlen($compactAlphaNumeric) <= 120) {
            return $compactAlphaNumeric;
        }

        return $value;
    }

    private function normalizeFormattedNumericInputs(Request $request, array $integerFields = [], array $decimalFields = []): void
    {
        $payload = [];

        foreach ($integerFields as $field) {
            if (! $request->exists($field)) {
                continue;
            }

            $payload[$field] = $this->normalizeIntegerInput($request->input($field));
        }

        foreach ($decimalFields as $field) {
            if (! $request->exists($field)) {
                continue;
            }

            $payload[$field] = $this->normalizeDecimalInput($request->input($field));
        }

        if ($payload !== []) {
            $request->merge($payload);
        }
    }

    private function normalizeIntegerInput(mixed $value): mixed
    {
        if ($value === null) {
            return null;
        }

        $normalized = preg_replace('/[^\d]/', '', trim((string) $value)) ?? '';

        return $normalized === '' ? null : $normalized;
    }

    private function normalizeDecimalInput(mixed $value): mixed
    {
        if ($value === null) {
            return null;
        }

        $normalized = trim((string) $value);
        if ($normalized === '') {
            return null;
        }

        $normalized = str_replace(' ', '', $normalized);

        if (str_contains($normalized, ',') && str_contains($normalized, '.')) {
            $normalized = str_replace('.', '', $normalized);
            $normalized = str_replace(',', '.', $normalized);
        } elseif (str_contains($normalized, ',')) {
            $normalized = str_replace('.', '', $normalized);
            $normalized = str_replace(',', '.', $normalized);
        } else {
            $normalized = str_replace('.', '', $normalized);
        }

        return $normalized === '' ? null : $normalized;
    }
}
