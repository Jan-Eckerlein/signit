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
            'path' => $file->store('uploads', 'local'),
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

			$singlePagePath = 'uploads/pages/' . uniqid() . "_page_{$pageNo}.pdf";
			Storage::put($singlePagePath, $pdf->Output('S'));

			$pdfProcessUpload->pdfProcess->pages()->create([
				'pdf_original_path' => $singlePagePath,
				'pdf_processed_path' => null,
				'is_up_to_date' => false,
			]);
		}
	}
}
