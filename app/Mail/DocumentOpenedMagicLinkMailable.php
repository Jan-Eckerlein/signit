<?php

namespace App\Mail;

use App\Enums\QueueEnum;
use App\Models\Document;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DocumentOpenedMagicLinkMailable extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Document $document,
        public User $recipient,
        public string $magicLinkToken
    ) {
        $this->onQueue(QueueEnum::EMAIL);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Sign document '{$this->document->title}'",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.documents.document-opened-magic-link',
            with: [
                'document' => $this->document,
                'recipient' => $this->recipient,
                'magicLinkToken' => $this->magicLinkToken,
            ],
        );
    }
} 