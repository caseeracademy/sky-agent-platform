<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'email' => 'superadmin@sky.com',
                'attributes' => [
                    'name' => 'Super Admin',
                    'password' => Hash::make('password'),
                    'role' => 'super_admin',
                    'is_active' => true,
                    'email_verified_at' => now(),
                ],
                'create_wallet' => false,
            ],
            [
                'email' => 'admin@sky.com',
                'attributes' => [
                    'name' => 'Admin Staff',
                    'password' => Hash::make('password'),
                    'role' => 'admin_staff',
                    'is_active' => true,
                    'email_verified_at' => now(),
                ],
                'create_wallet' => false,
            ],
            [
                'email' => 'agent.owner@sky.com',
                'attributes' => [
                    'name' => 'Agent Owner',
                    'password' => Hash::make('password'),
                    'role' => 'agent_owner',
                    'is_active' => true,
                    'email_verified_at' => now(),
                ],
                'create_wallet' => true,
            ],
            [
                'email' => 'agent.staff@sky.com',
                'attributes' => [
                    'name' => 'Agent Staff',
                    'password' => Hash::make('password'),
                    'role' => 'agent_staff',
                    'is_active' => true,
                    'email_verified_at' => now(),
                ],
                'create_wallet' => true,
            ],
        ];

        $agentOwnerId = null;

        foreach ($users as $userData) {
            $user = User::updateOrCreate(['email' => $userData['email']], $userData['attributes']);

            if ($user->role === 'agent_owner') {
                $agentOwnerId = $user->id;
            }

            if ($userData['create_wallet']) {
                Wallet::firstOrCreate(['agent_id' => $user->id], [
                    'available_balance' => 0,
                    'pending_balance' => 0,
                ]);
            }
        }

        if ($agentOwnerId) {
            User::where('email', 'agent.staff@sky.com')->update(['parent_agent_id' => $agentOwnerId]);
        }
    }
}
