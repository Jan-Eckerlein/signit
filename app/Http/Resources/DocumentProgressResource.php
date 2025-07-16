<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Document;
use App\Models\DocumentSigner;

/**
 * @property Document $resource
 */
class DocumentProgressResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'total_signers' => $this->resource->documentSigners()->count(),
            'completed_signers' => $this->resource->documentSigners()
                ->whereNotNull('signature_completed_at')
                ->count(),
            'signers_progress' => $this->resource->documentSigners()
                ->with(['user', 'documentFields.value'])
                ->get()
                ->map(function (DocumentSigner $signer) {
                    return [
                        'id' => $signer->id,
                        'user_name' => $signer->user->name,
                        'completed_fields' => $signer->getCompletedFieldsCount(),
                        'total_fields' => $signer->getTotalFieldsCount(),
                        'is_completed' => $signer->isSignatureCompleted(),
                        'completed_at' => $signer->signature_completed_at,
                    ];
                }),
        ];
    }
}
