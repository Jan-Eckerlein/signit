<?php

namespace App\Listeners;

use App\Enums\QueueEnum;
use App\Events\SignatureCompletedEvent;
use App\Services\DocumentFieldRenderDirectorService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class RenderDocumentFieldsListener implements ShouldQueue
{

    public string $queue = QueueEnum::DEFAULT->value;

    /**
     * Create the event listener.
     */
    public function __construct(
        public DocumentFieldRenderDirectorService $documentFieldRenderDirectorService
    ){}

    /**
     * Handle the event.
     */
    public function handle(SignatureCompletedEvent $event): void
    {
        $this->documentFieldRenderDirectorService->directRender($event->documentSigner->document->id);
    }
}
