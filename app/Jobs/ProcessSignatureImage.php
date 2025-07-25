<?php

namespace App\Jobs;

use App\Enums\QueueEnum;
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
        protected UploadedFile $uploadedFile,
        protected int $signId,
    )
    {
        $this->onQueue(QueueEnum::IMAGE_PROCESSING);
    }

    /**
     * Execute the job.
     */
    public function handle(SignService $signService): void
    {
        $sign = Sign::findOrFail($this->signId);
        $imagePath = $signService->processAndStoreSignature($this->uploadedFile);
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
