<?php

namespace Modules\Submissions\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Modules\Submissions\Events\SubmissionStatusChanged;
use Modules\Submissions\Mail\SubmissionStatusUpdatedMail;

class SendSubmissionStatusNotification implements ShouldQueue
{
    /**
     * Handle the event.
     */
    public function handle(SubmissionStatusChanged $event): void
    {
        try {
            $submission = $event->submission;
            
            // Prioritas penerima: User login yang membuat naskah / corresponding author
            $recipientEmail = $submission->author?->email;

            if (!$recipientEmail) {
                $primaryAuthor = $submission->authors->where('is_corresponding_author', true)->first();
                $recipientEmail = $primaryAuthor?->email;
            }

            if ($recipientEmail) {
                Mail::to($recipientEmail)->send(
                    new SubmissionStatusUpdatedMail(
                        $submission,
                        $event->fromStatus,
                        $event->toStatus,
                        $event->actor,
                        $event->notes
                    )
                );
            }

            // In-App Notification untuk Penulis
            $authorUser = $submission->author;
            if ($authorUser) {
                try {
                    $statusName = strtoupper(str_replace('_', ' ', $event->toStatus));
                    $type = in_array($event->toStatus, ['accepted', 'published']) ? 'success' : ($event->toStatus === 'revision_required' ? 'warning' : ($event->toStatus === 'rejected' ? 'danger' : 'info'));
                    $authorUser->notify(new \App\Notifications\EditorialNotification(
                        title: "Status Naskah: {$statusName}",
                        message: "Naskah '{$submission->title}' diperbarui ke status {$statusName}.",
                        actionUrl: route('submissions.show', $submission->id),
                        type: $type,
                        icon: 'ki-document'
                    ));
                } catch (\Throwable $notifEx) {
                    Log::error('Gagal mengirim in-app notification: ' . $notifEx->getMessage());
                }
            }
        } catch (\Throwable $e) {
            Log::error('Gagal mengirimkan email notifikasi status naskah: ' . $e->getMessage(), [
                'submission_id' => $event->submission->id ?? null,
                'target_status' => $event->toStatus ?? null,
            ]);
        }
    }
}
