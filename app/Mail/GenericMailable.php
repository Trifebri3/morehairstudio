<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;

class GenericMailable extends Mailable
{
    use Queueable, SerializesModels;

    public string $subjectText;
    public string $bodyText;
    public ?string $attachPath;
    public ?string $attachName;

    public function __construct(string $subjectText, string $bodyText, ?string $attachPath = null, ?string $attachName = null)
    {
        $this->subjectText = $subjectText;
        $this->bodyText = $bodyText;
        $this->attachPath = $attachPath;
        $this->attachName = $attachName;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subjectText,
        );
    }

    public function content(): Content
    {
        return new Content(
            htmlString: $this->bodyText
        );
    }

    public function attachments(): array
    {
        if ($this->attachPath && file_exists($this->attachPath)) {
            return [
                Attachment::fromPath($this->attachPath)
                    ->as($this->attachName ?? basename($this->attachPath))
            ];
        }
        return [];
    }
}
