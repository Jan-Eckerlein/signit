<?php

namespace Database\Factories;

use App\Models\DocumentField;
use App\Models\DocumentPage;
use App\Models\DocumentSigner;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Enums\DocumentFieldType;

/**
 * @extends Factory<DocumentField>
 */
class DocumentFieldFactory extends Factory
{
    protected $model = DocumentField::class;

    public function definition(): array
    {
        return [
            'document_page_id' => DocumentPage::factory(),
            'document_signer_id' => DocumentSigner::factory(),
            'x' => $this->faker->random_int(0, 1000),
            'y' => $this->faker->random_int(0, 1000),
            'width' => $this->faker->random_int(1, 300),
            'height' => $this->faker->random_int(1, 300),
            'type' => $this->faker->randomElement(DocumentFieldType::cases()),
            'label' => $this->faker->word(),
            'description' => $this->faker->optional()->sentence(),
            'required' => $this->faker->boolean(),
        ];
    }
} 