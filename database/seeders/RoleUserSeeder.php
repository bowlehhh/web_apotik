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
        $defaultPassword = 'rahasia123';
        $now = Carbon::now();

        $users = [
            [
                'name' => 'Dokter Apotik Sumber Sehat',
                'email' => 'dokter@apotik.test',
                'role' => User::ROLE_DOKTER,
            ],
            [
                'name' => 'Admin Apotik Sumber Sehat',
                'email' => 'admin@apotik.test',
                'role' => User::ROLE_ADMIN,
            ],
            [
                'name' => 'Kasir Apotik Sumber Sehat',
                'email' => 'kasir@apotik.test',
                'role' => User::ROLE_KASIR,
            ],
            [
                'name' => 'Master Admin Apotik Sumber Sehat',
                'email' => 'masteradmin@apotik.test',
                'role' => User::ROLE_MASTER_ADMIN,
            ],
        ];

        DB::transaction(function () use ($users, $defaultPassword, $now): void {
            foreach ($users as $user) {
                User::query()->updateOrCreate(
                    ['email' => $user['email']],
                    [
                        'name' => $user['name'],
                        'role' => $user['role'],
                        'password' => $defaultPassword,
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
