<?php

namespace Database\Seeders;

use App\Models\TikTokCredential;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@streamforge.com',
        ]);

        TikTokCredential::factory()->create(['user_id' => $user->id]);
        TikTokCredential::factory()->expired()->create(['user_id' => $user->id]);
    }
}
