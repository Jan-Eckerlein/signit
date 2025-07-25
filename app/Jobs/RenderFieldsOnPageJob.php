<?php

namespace App\Jobs;

use App\Enums\QueueEnum;
use App\Services\PdfProcessRenderService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class RenderFieldsOnPageJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     * @param int $pdfProcessPageId
     * @param int[] $documentFieldIds
     */
    public function __construct(
        public int $pdfProcessPageId,
        public array $documentFieldIds,
    ) {
        $this->onQueue(QueueEnum::PDF_PROCESSING);
    }

    /**
     * Execute the job.
     */
    public function handle(PdfProcessRenderService $pdfProcessRenderService): void
    {
        $pdfProcessRenderService->renderFieldsOnPage($this->pdfProcessPageId, $this->documentFieldIds);
    }
}
