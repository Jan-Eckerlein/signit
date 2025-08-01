<?php

namespace App\Listeners;

use App\Events\DocumentOpenedEvent;
use App\Mail\DocumentOpenedMagicLinkMailable;
use App\Mail\DocumentOpenedMailable;
use App\Services\MagicLinkService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class SendDocumentOpenedNotificationListener
{
    /**
     * Create the event listener.
     */
    public function __construct(
        private MagicLinkService $magicLinkService
    ){ }

    /**
     * Handle the event.
     */
    public function handle(DocumentOpenedEvent $event): void
    {
        foreach ($event->document->documentSigners as $documentSigner) {
            if (!$documentSigner->user) {
                continue; // Skip if no user associated
            }
            
            if ($documentSigner->user->isAnonymous()) {
                // For anonymous users, create magic link and send notification

                $token = $this->magicLinkService->createMagicLink($documentSigner->user, $event->document);
                Mail::to($documentSigner->user->email)
                    ->queue(new DocumentOpenedMagicLinkMailable($event->document, $documentSigner->user, $token));
            } else {
                // For regular users, send standard email notification

                Mail::to($documentSigner->user->email)
                    ->queue(new DocumentOpenedMailable($event->document, $documentSigner->user));
            }
        }
    }
}
