<?php

namespace App\Services;

use App\Models\PdfProcess;
use App\Models\PdfProcessUpload;
use Illuminate\Http\UploadedFile;
use setasign\Fpdi\Fpdi;
use Illuminate\Support\Facades\Storage;

class PdfProcessUploadService
{
    public function upload(PdfProcess $pdfProcess, UploadedFile $file, int $order): void
    {
        $pdfProcessUpload = $pdfProcess->uploads()->create([
            'name' => $file->getClientOriginalName(),
            'path' => $file->store('pdf_process/uploads', 'local'),
            'size' => $file->getSize(),
            'order' => $order,
        ]);

		$this->splitStoredPdf($pdfProcessUpload);
    }

	public function splitStoredPdf(PdfProcessUpload $pdfProcessUpload): void
	{
		$pdfPath = Storage::path($pdfProcessUpload->path);
		$pdf = new Fpdi();
		$pageCount = $pdf->setSourceFile($pdfPath);

		for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
			$pdf = new Fpdi();
			$pdf->setSourceFile($pdfPath);
			$pdf->AddPage();
			$tplIdx = $pdf->importPage($pageNo);
			$pdf->useTemplate($tplIdx);

			$singlePagePath = 'pdf_process/pages/' . uniqid() . "_page_{$pageNo}.pdf";
			Storage::put($singlePagePath, $pdf->Output('S'));

			//TODO: This is a hack to get the order of the pages. It's not a good solution.
			$tmp_order_base = $pdfProcessUpload->order;
			$tmp_order = (float)((int)$tmp_order_base + (float)($pageNo / 10000));

			$pdfProcessUpload->pdfProcess->pages()->create([
				'pdf_original_path' => $singlePagePath,
				'pdf_processed_path' => null,
				'is_up_to_date' => false,
				'tmp_order' => $tmp_order,
			]);
		}
	}
}
