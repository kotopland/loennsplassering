<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SimpleEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $subject;

    public $body;

    public $filePath;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($subject, $body, $filePath = null)
    {
        $this->subject = $subject;
        $this->body = $body;
        $this->filePath = $filePath;
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {
        return new Envelope(
            subject: $this->subject,
        );
    }

    /**
     * Get the message content definition.
     *
     * @return \Illuminate\Mail\Mailables\Content
     */
    public function content()
    {

        return new Content(
            markdown: 'emails.simple-email',
            with: [
                'body' => $this->body,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array
     */
    public function attachments()
    {
        if (! $this->filePath) {
            return [];
        }
        $attachments = [storage_path('app/public/'.$this->filePath)];

        return $attachments;
    }
}
