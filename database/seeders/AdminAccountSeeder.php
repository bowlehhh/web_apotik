<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class AdminAccountSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'admin@apotik.test'],
            [
                'name' => 'Admin Apotek',
                'role' => User::ROLE_ADMIN,
                'password' => 'rahasia123',
                'email_verified_at' => Carbon::now(),
            ]
        );

        User::query()
            ->where('email', '1')
            ->delete();
    }
}
