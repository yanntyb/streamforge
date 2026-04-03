<?php

namespace Database\Factories;

use App\Models\Clip;
use App\Models\ClipPlatformUpload;
use App\Models\TikTokCredential;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ClipPlatformUpload>
 */
class ClipPlatformUploadFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'clip_id' => Clip::factory(),
            'platform_type' => TikTokCredential::class,
            'platform_id' => TikTokCredential::factory(),
            'external_id' => $this->faker->uuid(),
            'status' => 'processing',
        ];
    }

    public function published(): static
    {
        return $this->state(fn (): array => ['status' => 'published']);
    }

    public function failed(): static
    {
        return $this->state(fn (): array => ['status' => 'failed']);
    }
}
