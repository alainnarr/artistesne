<?php

declare(strict_types=1);

namespace App\Actions;

use App\Database\Models\Registration;
use App\Database\Models\User;
use App\Enums\RegistrationStatus;
use App\Notifications\RegistrationRejectedNotification;
use Illuminate\Support\Facades\Notification;

/**
 * Rejects a new-style Registration and notifies the applicant.
 */
class RejectRegistration
{
    public function __invoke(Registration $registration, User $reviewer, ?string $notes = null): void
    {
        $registration->update([
            'enum_status' => RegistrationStatus::REJECTED,
            'reviewed_at' => now(),
            'reviewed_by' => $reviewer->id,
            'review_notes' => $notes,
        ]);

        // The applicant may not have an account yet (rejected registrations never
        // provision a User), so we can't notify() an Eloquent model instance here:
        // RegistrationRejectedNotification implements ShouldQueue, and queuing an
        // unsaved/unpersisted model (no primary key) fails silently in the queue
        // worker when it tries to re-fetch the model by id — the email is never sent.
        // Routing directly to the email address sidesteps model (de)serialization.
        Notification::route('mail', $registration->email)
            ->notify(new RegistrationRejectedNotification($registration->artist_name, $notes));
    }
}
