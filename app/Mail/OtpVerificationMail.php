<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OtpVerificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $otp;
    public string $fullName;
    public string $purpose;

    /**
     * Create a new message instance.
     */
    public function __construct(string $otp, string $fullName = 'Tour Explorer', string $purpose = 'verification')
    {
        $this->otp = $otp;
        $this->fullName = $fullName;
        $this->purpose = $purpose;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = ($this->purpose === 'password_reset')
            ? "Your Safar Fly Password Reset Code: {$this->otp}"
            : "Your Safar Fly Verification Code: {$this->otp}";

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
            view: 'emails.otp_verification',
            with: [
                'otp' => $this->otp,
                'fullName' => $this->fullName,
                'purpose' => $this->purpose,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }
}
