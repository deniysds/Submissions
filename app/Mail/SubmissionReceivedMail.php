<?php

namespace Modules\Submissions\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Modules\Submissions\Models\Submission;
use Modules\Usermanagement\Models\User;

class SubmissionReceivedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Submission $submission,
        public User $author
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $shortName = $this->submission->journal?->short_name ?: 'IGNITE';

        return new Envelope(
            subject: "[{$shortName}] Bukti Tanda Terima Pengajuan Naskah Ilmiah: {$this->submission->title}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'submissions::emails.submission_received',
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
