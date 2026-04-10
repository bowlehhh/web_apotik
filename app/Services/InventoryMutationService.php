<?php

namespace App\Services;

use App\Models\Medicine;
use App\Models\MedicinePurchaseLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InventoryMutationService
{
    public function createWarehousePurchase(User $actor, array $payload): Medicine
    {
        $updatedMedicine = null;

        DB::transaction(function () use ($actor, $payload, &$updatedMedicine): void {
            $medicine = Medicine::query()
                ->lockForUpdate()
                ->findOrFail((int) $payload['medicine_id']);

            $medicine->update([
                'stock' => $medicine->stock + (int) $payload['quantity'],
                'buy_price' => (float) $payload['buy_price'],
                'purchase_source' => $payload['purchase_source'],
                'expiry_date' => $payload['expiry_date'] ?? $medicine->expiry_date,
                'photo_path' => $payload['photo_path'] ?? $medicine->photo_path,
            ]);

            $updatedMedicine = $medicine;

            MedicinePurchaseLog::query()->create([
                'medicine_id' => $medicine->id,
                'created_by' => $actor->id,
                'quantity' => (int) $payload['quantity'],
                'buy_price' => (float) $payload['buy_price'],
                'purchase_source' => $payload['purchase_source'] ?? null,
                'expiry_date' => $payload['expiry_date'] ?? null,
                'photo_path' => $payload['photo_path'] ?? null,
                'purchased_at' => $payload['purchased_at'] ?? now(),
                'notes' => $payload['notes'] ?? null,
            ]);
        });

        return $updatedMedicine;
    }

    public function updateWarehousePurchase(User $actor, MedicinePurchaseLog $purchaseLog, array $payload): Medicine
    {
        $updatedMedicine = null;

        DB::transaction(function () use ($actor, $purchaseLog, $payload, &$updatedMedicine): void {
            $lockedLog = MedicinePurchaseLog::query()
                ->lockForUpdate()
                ->findOrFail($purchaseLog->id);

            $medicine = Medicine::query()
                ->lockForUpdate()
                ->findOrFail($lockedLog->medicine_id);

            $quantityDelta = (int) $payload['quantity'] - (int) $lockedLog->quantity;
            $newStock = (int) $medicine->stock + $quantityDelta;

            if ($newStock < 0) {
                throw ValidationException::withMessages([
                    'quantity' => 'Perubahan jumlah membuat stok obat menjadi minus. Cek kembali jumlah yang diinput.',
                ]);
            }

            $medicine->update([
                'stock' => $newStock,
                'buy_price' => (float) $payload['buy_price'],
                'purchase_source' => $payload['purchase_source'],
                'expiry_date' => $payload['expiry_date'],
                'photo_path' => $payload['photo_path'] ?? $medicine->photo_path,
            ]);

            $updatedMedicine = $medicine;

            $lockedLog->update([
                'quantity' => (int) $payload['quantity'],
                'buy_price' => (float) $payload['buy_price'],
                'purchase_source' => $payload['purchase_source'],
                'expiry_date' => $payload['expiry_date'],
                'photo_path' => $payload['photo_path'] ?? $lockedLog->photo_path,
                'purchased_at' => $payload['purchased_at'] ?? $lockedLog->purchased_at,
                'notes' => $payload['notes'] ?? null,
                'created_by' => $actor->id,
            ]);
        });

        return $updatedMedicine;
    }

    /**
     * @return array{medicine: Medicine, documentation_log_created: bool, before: array<string, mixed>}
     */
    public function updateMedicineMaster(User $actor, Medicine $medicine, array $payload): array
    {
        $photoPath = $payload['photo_path'] ?? $medicine->photo_path;
        $hasNewPhotoUpload = (bool) ($payload['has_new_photo_upload'] ?? false);

        $before = $this->medicineSnapshot($medicine);

        $medicine->update([
            'name' => $payload['name'],
            'barcode' => $payload['barcode'] ?? null,
            'trade_name' => $payload['trade_name'] ?? null,
            'dosage' => $payload['dosage'] ?? null,
            'category' => $payload['category'] ?? null,
            'stock' => (int) $payload['stock'],
            'buy_price' => (float) $payload['buy_price'],
            'sell_price' => array_key_exists('sell_price', $payload) ? (float) $payload['sell_price'] : 0,
            'expiry_date' => $payload['expiry_date'] ?? null,
            'photo_path' => $photoPath,
            'unit' => $payload['unit'],
            'purchase_source' => $payload['purchase_source'],
            'is_active' => (bool) ($payload['is_active'] ?? false),
        ]);

        $latestPurchaseLog = $medicine->purchaseLogs()
            ->orderByDesc('purchased_at')
            ->orderByDesc('id')
            ->first();

        if ($latestPurchaseLog) {
            $latestPurchaseLog->update([
                'purchase_source' => $payload['purchase_source'],
                'expiry_date' => $payload['expiry_date'],
            ]);
        }

        $documentationLogCreated = false;
        $documentationPhotoPath = trim((string) ($photoPath ?? ''));

        if ($documentationPhotoPath !== '') {
            $photoLogQuantity = (int) ($latestPurchaseLog?->quantity ?? 0);
            if ($photoLogQuantity < 1) {
                $photoLogQuantity = max(1, (int) $payload['stock']);
            }

            MedicinePurchaseLog::query()->create([
                'medicine_id' => $medicine->id,
                'created_by' => $actor->id,
                'quantity' => $photoLogQuantity,
                'buy_price' => (float) $payload['buy_price'],
                'purchase_source' => $payload['purchase_source'],
                'expiry_date' => $payload['expiry_date'] ?? null,
                'photo_path' => $documentationPhotoPath,
                'purchased_at' => now(),
                'notes' => $hasNewPhotoUpload
                    ? 'Dokumentasi foto ditambahkan dari update data obat.'
                    : 'Dokumentasi foto diperbarui dari update data obat.',
            ]);

            $documentationLogCreated = true;
        }

        return [
            'medicine' => $medicine->fresh(),
            'documentation_log_created' => $documentationLogCreated,
            'before' => $before,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function medicineSnapshot(Medicine $medicine): array
    {
        return [
            'name' => $medicine->name,
            'barcode' => $medicine->barcode,
            'stock' => (int) $medicine->stock,
            'buy_price' => (float) $medicine->buy_price,
            'sell_price' => (float) $medicine->sell_price,
            'purchase_source' => $medicine->purchase_source,
            'expiry_date' => optional($medicine->expiry_date)->toDateString(),
            'unit' => $medicine->unit,
            'is_active' => (bool) $medicine->is_active,
        ];
    }
}
