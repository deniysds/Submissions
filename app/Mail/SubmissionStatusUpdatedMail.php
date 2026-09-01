<?php

namespace Modules\Submissions\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Modules\Submissions\Models\Submission;
use Modules\Usermanagement\Models\User;

class SubmissionStatusUpdatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Submission $submission,
        public string $fromStatus,
        public string $toStatus,
        public ?User $actor = null,
        public ?string $notes = null
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $statusLabels = [
            'draft'               => 'Draf Naskah',
            'submitted'           => 'Naskah Berhasil Diserahkan',
            'under_editor_review' => 'Dalam Peninjauan Redaksi',
            'under_review'        => 'Dalam Penelaahan Mitra Bestari',
            'revision_required'   => 'Pemberitahuan Revisi Naskah',
            'resubmitted'         => 'Naskah Revisi Diterima',
            'accepted'            => 'Selamat! Naskah Anda Diterima (Accepted)',
            'published'           => 'Naskah Resmi Diterbitkan (Published)',
            'rejected'            => 'Keputusan Redaksi: Naskah Belum Diterima',
        ];

        $statusSubject = $statusLabels[$this->toStatus] ?? "Status Naskah Diperbarui ({$this->toStatus})";

        return new Envelope(
            subject: "[IGNITE - {$this->submission->journal?->short_name}] {$statusSubject}: {$this->submission->title}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'submissions::emails.status_updated',
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
