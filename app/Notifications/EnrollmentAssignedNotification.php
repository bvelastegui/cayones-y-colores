<?php

namespace App\Notifications;

use App\Models\Enrollment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class EnrollmentAssignedNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(private Enrollment $enrollment) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'enrollment_assigned',
            'enrollment_id' => $this->enrollment->id,
            'student_id' => $this->enrollment->student_id,
            'student_name' => $this->enrollment->student->full_name,
            'level' => $this->enrollment->level->name,
            'parallel' => $this->enrollment->course->parallel,
            'message' => "Se asignó el paralelo {$this->enrollment->course->parallel} para {$this->enrollment->student->full_name}.",
        ];
    }
}
