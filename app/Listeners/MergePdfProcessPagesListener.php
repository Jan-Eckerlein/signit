<?php

namespace App\Listeners;

use App\Events\RenderFieldsOnPageCompleted;
use App\Enums\QueueEnum;
use App\Events\PdfProcessMergeCompleted;
use App\Events\PdfProcessMergeCompletedEvent;
use App\Models\DocumentSigner;
use App\Models\PdfProcessPage;
use App\Services\PdfProcessMergeService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class MergePdfProcessPagesListener implements ShouldQueue
{
    public string $queue = QueueEnum::PDF_PROCESSING->value;

    /**
     * Create the event listener.
     */
    public function __construct(
        public PdfProcessMergeService $pdfProcessMergeService
    ) {}

    /**
     * Handle the event.
     */
    public function handle(RenderFieldsOnPageCompleted $event): void
    {
        $allProcessPagesAreUpToDate = PdfProcessPage::where('pdf_process_id', $event->pdfProcessPage->pdf_process_id)->where('is_up_to_date', false)->doesntExist();
        if (!$allProcessPagesAreUpToDate) return;

        $allSignersHaveCompleted = DocumentSigner::where('document_id', $event->pdfProcessPage->pdfProcess->document_id)->where('signature_completed_at', null)->doesntExist();
        if (!$allSignersHaveCompleted) return;

        $this->pdfProcessMergeService->mergePdfPages($event->pdfProcessPage->pdfProcess);
        PdfProcessMergeCompletedEvent::dispatch($event->pdfProcessPage->pdfProcess);
    }
}
