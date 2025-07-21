<?php

namespace App\Listeners;

use App\Enums\Icon;
use App\Events\SignatureCompletedEvent;
use App\Models\DocumentLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class LogSignatureCompletionListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(SignatureCompletedEvent $event): void
    {
        
        $document = $event->documentSigner->document;
        $user = $event->documentSigner->user;
        
        DocumentLog::create([
            'document_id' => $document->id,
            'document_signer_id' => $event->documentSigner->id,
            'ip' => $event->userAgent->ip,
            'date' => now(),
            'icon' => Icon::CHECKMARK,
            'text' => "Signature completed by {$user->name}",
        ]);

        Log::info('Signature completed', [
            'document_id' => $document->id,
            'document_signer_id' => $event->documentSigner->id,
            'completed_by_user_id' => $event->userAgent->user->id,
        ]);
    }
}
