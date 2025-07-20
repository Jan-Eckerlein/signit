<?php

namespace App\Listeners;

use App\Enums\Icon;
use App\Mail\DocumentOpenedMagicLinkMailable;
use App\Mail\DocumentOpenedMailable;
use App\Models\DocumentLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Queue\InteractsWithQueue;

class LogDocumentOpenedEmailSent
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
    public function handle(MessageSent $event): void
    {
        $mailable = $event->data['mailable'] ?? null;

        if ($mailable instanceof DocumentOpenedMailable || $mailable instanceof DocumentOpenedMagicLinkMailable) {
            DocumentLog::create([
                'document_id' => $mailable->document->id,
                'document_signer_id' => null, // or set if you have it
                'date' => now(),
                'icon' => Icon::SEND,
                'text' => "Document opened email sent to {$mailable->recipient->email}",
            ]);
        }
    }
}
