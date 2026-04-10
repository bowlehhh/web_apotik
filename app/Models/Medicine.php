<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Medicine extends Model
{
    use HasFactory;

    public const ENTRY_SOURCE_BARCODE = 'barcode';
    public const ENTRY_SOURCE_MANUAL = 'manual';

    protected $fillable = [
        'name',
        'barcode',
        'entry_source',
        'trade_name',
        'dosage',
        'category',
        'stock',
        'buy_price',
        'sell_price',
        'expiry_date',
        'photo_path',
        'unit',
        'purchase_source',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'buy_price' => 'decimal:2',
            'sell_price' => 'decimal:2',
            'expiry_date' => 'date',
        ];
    }

    public function entrySourceLabel(): string
    {
        return match ($this->entry_source) {
            self::ENTRY_SOURCE_BARCODE => 'Barcode',
            self::ENTRY_SOURCE_MANUAL => 'Input Biasa',
            default => 'Tidak Diketahui',
        };
    }

    public function prescriptionItems(): HasMany
    {
        return $this->hasMany(PrescriptionItem::class);
    }

    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function purchaseLogs(): HasMany
    {
        return $this->hasMany(MedicinePurchaseLog::class);
    }
}
