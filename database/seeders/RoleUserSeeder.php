<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class RoleUserSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $users = [
            [
                'name' => 'Dokter Apotik Sumber Sehat',
                'email' => 'dokter@apotik.test',
                'role' => User::ROLE_DOKTER,
                'password' => 'dokter123',
            ],
            [
                'name' => 'Admin Apotik Sumber Sehat',
                'email' => 'admin@apotik.test',
                'role' => User::ROLE_ADMIN,
                'password' => 'admin123',
            ],
            [
                'name' => 'Kasir Apotik Sumber Sehat',
                'email' => 'kasir@apotik.test',
                'role' => User::ROLE_KASIR,
                'password' => 'kasir123',
            ],
            [
                'name' => 'Master Admin Apotik Sumber Sehat',
                'email' => 'masteradmin@apotik.test',
                'role' => User::ROLE_MASTER_ADMIN,
                'password' => 'masteradmin123',
            ],
        ];

        DB::transaction(function () use ($users, $now): void {
            foreach ($users as $user) {
                User::query()->updateOrCreate(
                    ['email' => $user['email']],
                    [
                        'name' => $user['name'],
                        'role' => $user['role'],
                        'password' => $user['password'],
                        'is_active' => true,
                        'deactivated_at' => null,
                        'deactivation_reason' => null,
                        'email_verified_at' => $now,
                    ]
                );
            }

            // Bersihkan akun dummy lama agar login role tidak membingungkan.
            User::query()
                ->whereIn('email', [
                    '1',
                    'staff@apotik.test',
                    'admingudang@apotik.test',
                    'adminapotek@apotik.test',
                    'apoteker@apotik.test',
                    'purchasing@apotik.test',
                    'dokumentasi@apotik.test',
                    'owner@apotik.test',
                    'staf@apotik.test',
                ])
                ->delete();
        });
    }
}
