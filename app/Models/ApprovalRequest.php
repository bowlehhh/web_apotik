<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApprovalRequest extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    public const TYPE_PURCHASE_ITEM = 'purchase_item';
    public const TYPE_PURCHASE_DOCUMENTATION = 'purchase_documentation';
    public const TYPE_STOCK_ADJUSTMENT = 'stock_adjustment';
    public const TYPE_PRICE_CHANGE = 'price_change';
    public const TYPE_USER_CREATION = 'user_creation';
    public const TYPE_ROLE_PERMISSION_CHANGE = 'role_permission_change';

    protected $fillable = [
        'request_type',
        'module',
        'title',
        'status',
        'requested_by',
        'processed_by',
        'payload',
        'before_data',
        'request_note',
        'decision_note',
        'requested_at',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'before_data' => 'array',
            'requested_at' => 'datetime',
            'processed_at' => 'datetime',
        ];
    }

    public static function statuses(): array
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_APPROVED,
            self::STATUS_REJECTED,
        ];
    }

    public static function typeLabels(): array
    {
        return [
            self::TYPE_PURCHASE_ITEM => 'Pembelian Barang',
            self::TYPE_PURCHASE_DOCUMENTATION => 'Dokumentasi Foto Pembelian',
            self::TYPE_STOCK_ADJUSTMENT => 'Penyesuaian Stok',
            self::TYPE_PRICE_CHANGE => 'Perubahan Harga',
            self::TYPE_USER_CREATION => 'Pembuatan User Baru',
            self::TYPE_ROLE_PERMISSION_CHANGE => 'Perubahan Role/Permission',
        ];
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function typeLabel(): string
    {
        return self::typeLabels()[$this->request_type] ?? ucfirst(str_replace('_', ' ', $this->request_type));
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_APPROVED => 'Disetujui',
            self::STATUS_REJECTED => 'Ditolak',
            default => 'Menunggu Persetujuan',
        };
    }
}
