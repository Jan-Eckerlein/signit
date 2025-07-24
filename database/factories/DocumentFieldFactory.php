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
            'document_page_id' => DocumentPage::factory(),
            'document_signer_id' => function (array $attributes) {
                if (isset($this->recycle) && $this->recycle->has(\App\Models\DocumentSigner::class)) {
                    $signer = $this->getRandomRecycledModel(\App\Models\DocumentSigner::class);
                    if ($signer) {
                        return $signer->id;
                    }
                }
                $documentPage = DocumentPage::find($attributes['document_page_id']);
                return DocumentSigner::factory()->create([
                    'document_id' => $documentPage->document_id,
                ])->id;
            },
            'x' => $this->faker->numberBetween(10, 100),
            'y' => $this->faker->numberBetween(10, 180),
            'width' => $this->faker->numberBetween(20, 60),
            'height' => $this->faker->numberBetween(8, 20),
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