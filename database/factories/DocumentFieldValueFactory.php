<?php

namespace Database\Factories;

use App\Enums\DocumentFieldType;
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
        return [
            // Don't set value fields here
        ];
    }

    public function as(DocumentFieldType $type, $value = null): self
    {
        return $this
            ->for(DocumentField::factory()->as($type), 'documentField')
            ->state(function (array $attributes) use ($type, $value) {
                return $this->getValues($type, $value);
            });
    }

    protected function getValues(DocumentFieldType $type, $value = null): array
    {
        // Add this for debugging
        if (!($type instanceof DocumentFieldType)) {
            throw new \Exception('Expected DocumentFieldType enum, got: ' . gettype($type) . ' - ' . print_r($type, true));
        }
        $type_key_map = [
            DocumentFieldType::SIGNATURE->value => 'value_signature_sign_id',
            DocumentFieldType::INITIALS->value => 'value_initials',
            DocumentFieldType::TEXT->value => 'value_text',
            DocumentFieldType::CHECKBOX->value => 'value_checkbox',
            DocumentFieldType::DATE->value => 'value_date',
        ];

        $value_map = [
            'value_signature_sign_id' => fn() => $value ?? Sign::factory(),
            'value_initials' => fn() => $value ?? $this->faker->lexify('??'),
            'value_text' => fn() => $value ?? $this->faker->sentence(),
            'value_checkbox' => fn() => $value ?? $this->faker->boolean(),
            'value_date' => fn() => $value ?? $this->faker->date(),
        ];

        $types = array_keys($value_map);
        $selected = $type_key_map[$type->value];

        $processed = [];
        foreach ($value_map as $key => $fn) {
            $processed[$key] = ($key === $selected) ? $fn() : null;
        }

        return $processed;
    }
} 