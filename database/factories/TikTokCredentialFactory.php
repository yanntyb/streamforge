<?php

namespace Database\Factories;

use App\Models\TikTokCredential;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TikTokCredential>
 */
class TikTokCredentialFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'tiktok_open_id' => $this->faker->uuid(),
            'tiktok_username' => $this->faker->userName(),
            'access_token' => $this->faker->sha256(),
            'refresh_token' => $this->faker->sha256(),
            'token_expires_at' => now()->addDay(),
            'scopes' => ['user.info.basic', 'video.upload', 'video.publish', 'video.list'],
        ];
    }

    public function expired(): static
    {
        return $this->state(fn (): array => [
            'token_expires_at' => now()->subHour(),
        ]);
    }
}
