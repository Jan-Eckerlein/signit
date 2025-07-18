<?php

namespace Database\Factories;

use App\Models\DocumentLog;
use App\Models\Contact;
use App\Models\DocumentSigner;
use App\Models\Document;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Enums\Icon;

/**
 * @extends Factory<DocumentLog>
 */
class DocumentLogFactory extends Factory
{
    protected $model = DocumentLog::class;

    public function definition(): array
    {
        return [
            'document_signer_id' => DocumentSigner::factory(),
            'document_id' => Document::factory(),
            'ip' => $this->faker->ipv4(),
            'date' => $this->faker->dateTime(),
            'icon' => $this->faker->randomElement(Icon::cases()),
            'text' => $this->faker->sentence(),
        ];
    }
} 