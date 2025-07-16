<?php

namespace App\Http\Resources;

use App\Models\DocumentFieldValue;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;


/**
 * @mixin DocumentFieldValue
 */
class DocumentFieldValueResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = [
            'id' => $this->id,
            'signer_document_field_id' => $this->signer_document_field_id,
            'value_signature_sign_id' => $this->value_signature_sign_id,
            'value_initials' => $this->value_initials,
            'value_text' => $this->value_text,
            'value_checkbox' => $this->value_checkbox,
            'value_date' => $this->value_date,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'signature_sign' => new SignResource($this->whenLoaded('signatureSign')),
        ];

        // Add metadata if provided
        if (isset($this->additional['document_completed'])) {
            $data['document_completed'] = $this->additional['document_completed'];
            $data['document_status'] = $this->additional['document_status'];
        }

        return $data;
    }
}
