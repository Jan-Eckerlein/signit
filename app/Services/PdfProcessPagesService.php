<?php

namespace App\Services;

use App\Models\PdfProcess;
use App\Models\PdfProcessPage;

class PdfProcessPagesService
{
	public function commitPages(PdfProcess $pdfProcess): void
	{
		$documentId = $pdfProcess->document->id;
		$this->rebalanceTmpOrder($pdfProcess);
		$pdfProcess->pages()->whereNotNull('tmp_order')->orderBy('tmp_order')->get()->each(function (PdfProcessPage $processPage) use ($documentId) {
			$processPage->pages()->create([
				'page_number' => $processPage->tmp_order,
				'document_id' => $documentId,
			]);
			$processPage->update([
				'tmp_order' => null,
			]);
		});
	}

	public function rebalanceTmpOrder(PdfProcess $pdfProcess): void
	{
		$highestPageNumber = $pdfProcess->document->documentPages()->max('page_number');
		$pdfProcess->pages()->whereNotNull('tmp_order')->orderBy('tmp_order')->get()->each(function (PdfProcessPage $page, int $index) use ($highestPageNumber) {
			$page->update([
				'tmp_order' => $index + $highestPageNumber + 1,
			]);
		});
	}
}