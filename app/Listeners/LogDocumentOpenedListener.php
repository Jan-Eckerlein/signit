<?php

namespace App\Listeners;

use App\Enums\Icon;
use App\Events\DocumentOpenedEvent;
use App\Models\DocumentLog;
use Illuminate\Http\Request;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class LogDocumentOpenedListener
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
    public function handle(DocumentOpenedEvent $event): void
    {
        DocumentLog::create([
            'document_id' => $event->document->id,
            'document_signer_id' => null, // System action
            'ip' => $event->userAgent->ip,
            'date' => now(),
            'icon' => Icon::SEND,
            'text' => "Document set to in progress by {$event->userAgent->user->name}",
        ]);

        Log::info('Document opened', [
            'document_id' => $event->document->id,
            'opened_by_user_id' => $event->userAgent->user->id,
        ]);
    }
}
