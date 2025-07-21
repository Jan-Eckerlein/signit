<?php

namespace App\Listeners;

use App\Enums\DocumentStatus;
use App\Enums\Icon;
use App\Events\SignatureCompletedEvent;
use App\Mail\DocumentSetInProgessMailable;
use App\Models\DocumentLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class HandleFirstSignatureListener
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
        // dd($event->documentSigner->document->documentSigners()->get());
        $isFirstSignature = $event->documentSigner->document->documentSigners()
            ->whereNotNull('signature_completed_at')
            ->count() === 1;
        
        if (!$isFirstSignature) return;

        $event->documentSigner->document->update(['status' => DocumentStatus::IN_PROGRESS]);

        DocumentLog::create([
            'document_id' => $event->documentSigner->document->id,
            'document_signer_id' => $event->documentSigner->id,
            'ip' => $event->userAgent->ip,
            'date' => now(),
            'icon' => Icon::SEND,
            'text' => "First signature completed by {$event->documentSigner->user->name}",
        ]);

        Mail::to($event->documentSigner->document->ownerUser->email)
            ->queue(new DocumentSetInProgessMailable($event->documentSigner->document));
    }
}
