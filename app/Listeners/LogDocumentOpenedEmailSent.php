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
        if (
            isset($event->data['__laravel_mailable']) &&
            in_array($event->data['__laravel_mailable'], [
                \App\Mail\DocumentOpenedMailable::class,
                \App\Mail\DocumentOpenedMagicLinkMailable::class,
            ]) &&
            isset($event->data['document'], $event->data['recipient'])
        ) {
            DocumentLog::create([
                'document_id' => $event->data['document']->id,
                'document_signer_id' => null,
                'date' => now(),
                'icon' => \App\Enums\Icon::SEND->value,
                'text' => "Document opened email sent to {$event->data['recipient']->email}",
            ]);
        }
    }
}
