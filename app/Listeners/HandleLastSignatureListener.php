<?php

namespace App\Listeners;

use App\Enums\DocumentStatus;
use App\Enums\Icon;
use App\Events\DocumentCompletedEvent;
use App\Events\SignatureCompletedEvent;
use App\Models\Document;
use App\Models\DocumentLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class HandleLastSignatureListener
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
        if (!$this->allSignersSigned($event->documentSigner->document)) return;

        $event->documentSigner->document->update(['status' => DocumentStatus::COMPLETED]);
        DocumentCompletedEvent::dispatch($event->documentSigner->document, $event->userAgent);
    }


    /**
     * Check if all signers have completed their signatures
     */
    private function allSignersSigned(Document $document): bool
    {
        return $document->documentSigners()
            ->whereNull('signature_completed_at')
            ->doesntExist();
    }
}
