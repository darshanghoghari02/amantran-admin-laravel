<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $now = now()->toISOString();
        $email    = env('ADMIN_EMAIL', 'superadmin@amantran.com');
        $password = env('ADMIN_PASSWORD', 'AmantranAdmin_2026_Secure!');
        $id       = 'admin_super';

        DB::table('users')->updateOrInsert(
            ['id' => $id],
            [
                'id'   => $id,
                'data' => json_encode([
                    'email'       => $email,
                    'name'        => 'Super Admin',
                    'displayName' => 'Super Admin',
                    'password'    => Hash::make($password),
                    'roleId'      => 'super_admin',
                    'role'        => 'super_admin',
                    'isBlocked'   => false,
                    'status'      => 'active',
                    'permissions' => ['*'],
                    'createdAt'   => $now,
                    'updatedAt'   => $now,
                ], JSON_UNESCAPED_UNICODE)
            ]
        );

        $this->command->info("Super admin seeded: {$email}");
    }
}
