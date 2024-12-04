<?php

namespace Database\Factories;

use App\Enums\Status;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\File>
 */
class FileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->create(),
            'admin_id' => User::factory()->create()->hasRole('super_admin'),
            'title' => fake()->title,
            'description' => fake()->text(),
            'document_word' => fake()->file,
            'document_pdf' => fake()->file,
            'status' => Status::cases(),
        ];
    }
}
