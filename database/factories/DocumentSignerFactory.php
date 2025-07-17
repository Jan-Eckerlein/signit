<?php

namespace Database\Factories;

use App\Models\DocumentSigner;
use App\Models\Document;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DocumentSigner>
 */
class DocumentSignerFactory extends Factory
{
    protected $model = DocumentSigner::class;

    public function definition(): array
    {
        return [
            'document_id' => Document::factory(),
            'user_id' => User::factory(),
            'name' => $this->faker->name(),
            'description' => $this->faker->optional()->sentence(),
        ];
    }
} 