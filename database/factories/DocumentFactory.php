<?php

namespace Database\Factories;

use App\Enums\DocumentStatus;
use App\Models\Document;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Document>
 */
class DocumentFactory extends Factory
{
    protected $model = Document::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(3),
            'owner_user_id' => User::factory(),
            'description' => $this->faker->paragraph(),
            'status' => DocumentStatus::DRAFT,
            'template_document_id' => null,
        ];
    }
} 