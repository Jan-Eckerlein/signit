<?php

namespace Database\Factories;

use App\Models\DocumentFieldValue;
use App\Models\DocumentField;
use App\Models\Sign;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DocumentFieldValue>
 */
class DocumentFieldValueFactory extends Factory
{
    protected $model = DocumentFieldValue::class;

    public function definition(): array
    {
        $value_map = [
            'value_signature_sign_id' => fn() => Sign::factory(),
            'value_initials' => fn() => $this->faker->lexify('??'),
            'value_text' => fn() => $this->faker->sentence(),
            'value_checkbox' => fn() => $this->faker->boolean(),
            'value_date' => fn() => $this->faker->date(),
        ];

        $types = array_keys($value_map);
        $selected = $this->faker->randomElement($types);

        $processed = [];
        foreach ($value_map as $key => $fn) {
            $processed[$key] = ($key === $selected) ? $fn() : null;
        }

        return [
            'document_field_id' => DocumentField::factory(),
            ...$processed,
        ];
    }
} 