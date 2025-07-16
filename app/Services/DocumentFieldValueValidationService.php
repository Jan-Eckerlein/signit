<?php

namespace App\Services;

use App\Enums\DocumentFieldType;
use Illuminate\Validation\Validator;

class DocumentFieldValueValidationService
{
    /**
     * Validate that exactly one value field is filled and return the field name.
     * 
     * @param array<string, mixed> $data
     * @return string
     */
    public static function validateExactlyOneValue(array $data): string
    {
        $valueFields = [
            'value_signature_sign_id',
            'value_initials',
            'value_text',
            'value_checkbox',
            'value_date'
        ];
        
        $filledFields = [];
        
        foreach ($valueFields as $field) {
            if (isset($data[$field])) {
                $filledFields[] = $field;
            }
        }
        
        if (empty($filledFields)) {
            throw new \InvalidArgumentException('At least one value field must be filled.');
        } elseif (count($filledFields) > 1) {
            throw new \InvalidArgumentException('Only one value field can be filled. Found: ' . implode(', ', $filledFields));
        }
        
        return $filledFields[0];
    }
    
    /**
     * Validate that the value type matches the field type.
     * 
     * @param array<string, mixed> $data
     * @param DocumentFieldType $fieldType
     */
    public static function validateValueMatchesFieldType(array $data, DocumentFieldType $fieldType): void
    {
        $expectedValueField = self::getExpectedValueField($fieldType);
        $providedValueField = self::validateExactlyOneValue($data);
        
        if ($providedValueField !== $expectedValueField) {
            throw new \InvalidArgumentException(
                "Field type '{$fieldType->value}' requires '{$expectedValueField}' but received '{$providedValueField}'."
            );
        }
    }
    
    /**
     * Get the expected value field based on field type.
     */
    private static function getExpectedValueField(DocumentFieldType $fieldType): string
    {
        return match ($fieldType) {
            DocumentFieldType::SIGNATURE => 'value_signature_sign_id',
            DocumentFieldType::INITIALS => 'value_initials',
            DocumentFieldType::TEXT => 'value_text',
            DocumentFieldType::CHECKBOX => 'value_checkbox',
            DocumentFieldType::DATE => 'value_date',
        };
    }
    
    /**
     * Add validation errors to a Laravel validator.
     * 
     * @param array<string, mixed> $data
     * @param DocumentFieldType|null $fieldType
     */
    public static function addValidationErrors(Validator $validator, array $data, ?DocumentFieldType $fieldType = null): void
    {
        try {
            $providedField = self::validateExactlyOneValue($data);
            
            if ($fieldType) {
                $expectedField = self::getExpectedValueField($fieldType);
                if ($providedField !== $expectedField) {
                    $validator->errors()->add('value_type', "Field type '{$fieldType->value}' requires '{$expectedField}' but received '{$providedField}'.");
                }
            }
        } catch (\InvalidArgumentException $e) {
            $validator->errors()->add('value_fields', $e->getMessage());
        }
    }
} 