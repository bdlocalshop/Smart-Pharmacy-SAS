<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Pharmacy;
use App\Models\PharmacyLicense;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PharmacyLicenseCredentialsMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public Pharmacy $pharmacy,
        public PharmacyLicense $license,
        public string $plainSecret,
        public bool $isRegenerated = false,
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = $this->isRegenerated
            ? "Important: Your Smart Pharmacy License Key & Secret Have Been Regenerated"
            : "Welcome to Smart Pharmacy — Your License Key & Secret Credentials";

        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.pharmacy-license-credentials',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}

