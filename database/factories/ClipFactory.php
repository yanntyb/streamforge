<?php

namespace Database\Factories;

use App\Models\Clip;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Clip>
 */
class ClipFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => $this->faker->sentence(4),
            'original_filename' => $this->faker->word().'.mp4',
            'thumbnail_path' => null,
        ];
    }

    /**
     * Indicate that the clip has a thumbnail.
     */
    public function withThumbnail(): static
    {
        return $this->state(fn (array $attributes): array => [
            'thumbnail_path' => 'thumbnails/fake-thumb.jpg',
        ]);
    }
}
