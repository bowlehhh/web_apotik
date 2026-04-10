<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ROLE_DOKTER = 'dokter';
    public const ROLE_ADMIN = 'admin';
    public const ROLE_ADMIN_GUDANG = 'admin_gudang';
    public const ROLE_ADMIN_APOTEK = 'admin_apotek';
    public const ROLE_KASIR = 'kasir';
    public const ROLE_APOTEKER = 'apoteker';
    public const ROLE_STAF_PURCHASING = 'staf_purchasing';
    public const ROLE_STAF_DOKUMENTASI = 'staf_dokumentasi';
    public const ROLE_OWNER_VIEWER = 'owner_viewer';
    public const ROLE_STAF = 'staf';
    public const ROLE_MASTER_ADMIN = 'master_admin';

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
        'deactivated_at',
        'deactivation_reason',
        'phone',
        'address',
        'bio',
        'avatar_path',
    ];

    protected $hidden = ['password', 'remember_token'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'deactivated_at' => 'datetime',
        ];
    }

    public static function roles(): array
    {
        return [
            self::ROLE_DOKTER,
            self::ROLE_ADMIN,
            self::ROLE_KASIR,
            self::ROLE_MASTER_ADMIN,
        ];
    }

    public static function roleLabels(): array
    {
        return [
            self::ROLE_DOKTER => 'Dokter',
            self::ROLE_ADMIN => 'Admin',
            self::ROLE_KASIR => 'Kasir',
            self::ROLE_MASTER_ADMIN => 'Master Admin',
        ];
    }

    public function roleLabel(): string
    {
        return self::roleLabels()[$this->role] ?? 'User';
    }

    public function permissions(): array
    {
        $matrix = (array) config('rbac.role_permissions', []);
        $permissions = (array) ($matrix[$this->role] ?? []);

        if (in_array('*', $permissions, true)) {
            return array_values(array_unique((array) config('rbac.permissions', [])));
        }

        return array_values(array_unique($permissions));
    }

    public function hasPermission(string $permission): bool
    {
        $matrix = (array) config('rbac.role_permissions', []);
        $permissions = (array) ($matrix[$this->role] ?? []);

        if (in_array('*', $permissions, true)) {
            return true;
        }

        return in_array($permission, $permissions, true);
    }

    public function hasAnyPermission(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($this->hasPermission((string) $permission)) {
                return true;
            }
        }

        return false;
    }

    public function isMasterAdmin(): bool
    {
        return $this->role === self::ROLE_MASTER_ADMIN;
    }

    public function canBypassApproval(): bool
    {
        return $this->isMasterAdmin();
    }

    public function avatarUrl(): string
    {
        if ($this->avatar_path) {
            return Storage::url($this->avatar_path);
        }

        return 'https://ui-avatars.com/api/?name='.urlencode($this->name).'&background=0052cc&color=ffffff&bold=true';
    }

    public function dispensedPrescriptions(): HasMany
    {
        return $this->hasMany(Prescription::class, 'dispensed_by');
    }

    public function salesHandled(): HasMany
    {
        return $this->hasMany(Sale::class, 'cashier_id');
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class, 'actor_id');
    }
}
