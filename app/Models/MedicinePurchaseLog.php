<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedicinePurchaseLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'medicine_id',
        'created_by',
        'quantity',
        'buy_price',
        'purchase_source',
        'expiry_date',
        'photo_path',
        'purchased_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'buy_price' => 'decimal:2',
            'expiry_date' => 'date',
            'purchased_at' => 'datetime',
        ];
    }

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
