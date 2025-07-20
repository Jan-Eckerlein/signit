<?php

namespace Tests\Unit;

use App\Mail\DocumentOpenedMailable;
use App\Mail\TestMailable;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SendingEmailFiresMessageSentEventTest extends TestCase
{
    /**
     * A basic unit test example.
     */
    public function test_event_is_fired_when_email_is_sent(): void
    {
        Event::fake();

        Mail::to('test@test.com')->queue(new TestMailable());

        Event::assertDispatched(MessageSent::class);
    }

    public function test_message_sent_event_has_mailable_class()
    {
        Event::fake();

        Mail::to('test@test.com')->queue(new TestMailable());

        Event::assertDispatched(MessageSent::class, function ($event) {
            return $event->data['__laravel_mailable'] === TestMailable::class;
        });
    }
}
