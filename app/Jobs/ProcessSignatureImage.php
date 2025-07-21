<?php

namespace App\Jobs;

use App\Events\SignatureImageProcessed;
use App\Events\SignatureImageProcessingFailed;
use App\Models\Sign;
use App\Services\SignService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class ProcessSignatureImage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected int $signId,
        protected string $filePath,
        protected string $originalName,
        protected SignService $signService
    )
    {
        $this->onQueue('image_processing');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $sign = Sign::findOrFail($this->signId);
        $uploadedFile = new UploadedFile(
            $this->filePath,
            $this->originalName,
            'image/png',
            null,
            true
        );
        $imagePath = $this->signService->processAndStoreSignature($uploadedFile);
        $sign->image_path = $imagePath;
        $sign->save();
        SignatureImageProcessed::dispatch($sign->id, $imagePath);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Signature image processing failed: ' . $exception->getMessage());
        SignatureImageProcessingFailed::dispatch($this->signId, $exception->getMessage());
    }
}
