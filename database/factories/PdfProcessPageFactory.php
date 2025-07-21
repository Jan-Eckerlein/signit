<?php

namespace Database\Factories;

use App\Models\PdfProcess;
use App\Models\DocumentPage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PdfProcessPage>
 */
class PdfProcessPageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'pdf_process_id' => PdfProcess::factory(),
            'document_page_id' => DocumentPage::factory(),
            'pdf_original_path' => $this->faker->filePath(),
            'pdf_processed_path' => null,
            'is_up_to_date' => false,
            'tmp_order' => $this->faker->numberBetween(0, 100),
        ];
    }
}
