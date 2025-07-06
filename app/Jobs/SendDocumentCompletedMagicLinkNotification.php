<?php

namespace App\Jobs;

use App\Mail\DocumentCompletedMagicLinkNotification;
use App\Models\Document;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendDocumentCompletedMagicLinkNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Document $document,
        public User $recipient,
        public string $magicLinkToken
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Mail::to($this->recipient->email)
            ->send(new DocumentCompletedMagicLinkNotification($this->document, $this->recipient, $this->magicLinkToken));
    }
} 