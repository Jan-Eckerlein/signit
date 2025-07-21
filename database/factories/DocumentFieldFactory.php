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
        // We'll leave document_page_id and document_signer_id as null by default
        return [
            'document_page_id' => null,
            'document_signer_id' => null,
            'x' => $this->faker->numberBetween(0, 1000),
            'y' => $this->faker->numberBetween(0, 1000),
            'width' => $this->faker->numberBetween(1, 300),
            'height' => $this->faker->numberBetween(1, 300),
            'type' => $this->faker->randomElement(DocumentFieldType::cases()),
            'label' => $this->faker->word(),
            'description' => $this->faker->optional()->sentence(),
            'required' => $this->faker->boolean(),
        ];
    }

    public function as(DocumentFieldType $type): self
    {
        return $this->state(function (array $attributes) use ($type) {
            return [
                'type' => $type,
            ];
        });
    }

    /**
     * Factory state for Scribe to use, ensuring valid relationships.
     */
    public function validForScribe()
    {
        $document = \App\Models\Document::factory()
            ->hasDocumentPages(1)
            ->hasDocumentSigners(1)
            ->create();
        $page = $document->documentPages->first();
        $signer = $document->documentSigners->first();
        return $this->state([
            'document_page_id' => $page->id,
            'document_signer_id' => $signer->id,
        ]);
    }
} 