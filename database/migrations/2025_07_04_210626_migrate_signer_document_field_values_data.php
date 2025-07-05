<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Migrate existing data from signer_document_fields to signer_document_field_values
        $fields = DB::table('signer_document_fields')->get();
        
        foreach ($fields as $field) {
            // Only create a value record if there's actual value data
            $hasValue = !is_null($field->value_signature_sign_id) ||
                       !is_null($field->value_initials) ||
                       !is_null($field->value_text) ||
                       !is_null($field->value_checkbox) ||
                       !is_null($field->value_date);
            
            if ($hasValue) {
                DB::table('signer_document_field_values')->insert([
                    'signer_document_field_id' => $field->id,
                    'value_signature_sign_id' => $field->value_signature_sign_id,
                    'value_initials' => $field->value_initials,
                    'value_text' => $field->value_text,
                    'value_checkbox' => $field->value_checkbox,
                    'value_date' => $field->value_date,
                    'created_at' => $field->created_at,
                    'updated_at' => $field->updated_at,
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Move data back from signer_document_field_values to signer_document_fields
        $values = DB::table('signer_document_field_values')->get();
        
        foreach ($values as $value) {
            DB::table('signer_document_fields')
                ->where('id', $value->signer_document_field_id)
                ->update([
                    'value_signature_sign_id' => $value->value_signature_sign_id,
                    'value_initials' => $value->value_initials,
                    'value_text' => $value->value_text,
                    'value_checkbox' => $value->value_checkbox,
                    'value_date' => $value->value_date,
                ]);
        }
    }
};
