<?php

namespace App\Jobs;

use App\Mail\DocumentInProgressNotification;
use App\Models\Document;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendDocumentInProgressNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Document $document,
        public User $recipient
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Mail::to($this->recipient->email)
            ->send(new DocumentInProgressNotification($this->document, $this->recipient));
    }
} 