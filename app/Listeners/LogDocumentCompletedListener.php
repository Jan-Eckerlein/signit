<?php

namespace App\Listeners;

use App\Enums\Icon;
use App\Events\DocumentCompletedEvent;
use App\Models\DocumentLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class LogDocumentCompletedListener
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
    public function handle(DocumentCompletedEvent $event): void
    {
        DocumentLog::create([
            'document_id' => $event->document->id,
            'document_signer_id' => null,
            'ip' => $event->userAgent->ip,
            'date' => now(),
            'icon' => Icon::CHECKMARK,
            'text' => "Document completed and signed by all signers",
        ]);
    }
}
