<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Seed Admin User
        $adminEmail = env('DEFAULT_ADMIN_EMAIL', 'admin@mydomain.com');
        $adminPassword = env('DEFAULT_ADMIN_PASSWORD', 'Admin123!');

        User::updateOrCreate(
            ['email' => $adminEmail],
            [
                'name' => 'Administrator',
                'password' => Hash::make($adminPassword),
            ]
        );

        // 2. Seed Default Settings
        $settings = [
            'envato_api_key' => env('ENVATO_API_KEY', ''),
            'allowed_item_ids' => env('ALLOWED_ITEM_IDS', ''),
            'allow_auto_domain_transfer' => '0', // default disabled, can be toggled to '1'
        ];

        foreach ($settings as $key => $value) {
            DB::table('settings')->updateOrInsert(
                ['key' => $key],
                [
                    'value' => $value,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
