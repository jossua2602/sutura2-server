<?php

namespace App\Mail;

use App\Models\ShopRegistration;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ShopRegistrationApproved extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public ShopRegistration $registration,
        public string $temporaryPassword
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Welcome to Sutura - Your Shop is Approved!',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.shops.approved',
            with: [
                'registration' => $this->registration,
                'temporaryPassword' => $this->temporaryPassword,
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
