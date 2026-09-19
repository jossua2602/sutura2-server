<?php

namespace App\Mail;

use App\Models\ShopRegistration;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ShopRegistrationRejected extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public ShopRegistration $registration
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Update on your Sutura Shop Registration',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.shops.rejected',
            with: [
                'registration' => $this->registration,
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
