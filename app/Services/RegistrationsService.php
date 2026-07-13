<?php

namespace App\Services;

use App\Database\Models\Registration;
use App\Enums\RegistrationStatus;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class RegistrationsService
{

    public function __construct(
        private readonly ActivitiesService $activitiesService,
        private readonly RepositoriesService $repositoryService,
        private readonly LinksService $linksService,
    ) {}

    public function create(array $data): Registration
    {
        Validator::make($data, Registration::getRules())->validate();

        return DB::transaction(function () use ($data) {
            $registrationData = Arr::except($data, ['activities', 'files', 'links',]);
            $registration = Registration::create($registrationData);

            //It is not possible to use `attach` directly, as it is not captured by Audit.
            $this->activitiesService->attachMultiple($registration, $data['activities'] ?? []);

            if (!empty($data['files'])) {
                $this->repositoryService->createMultiple($registration, $data['files']);
            }

            if (!empty($data['links'])) {
                $this->linksService->createMultiple($registration, $data['links']);
            }

            return $registration->fresh(['activities', 'repositories', 'links']);
        });
    }

    public function update(Registration $registration, array $data): Registration
    {
        Validator::make($data, Registration::getRules(array_keys($data), ['id' => $registration->id,]))->validate();

        return DB::transaction(function () use ($registration, $data) {

            $registrationData = Arr::except($data, ['activities', 'files', 'links',]);

            $registration->update($registrationData);

            if (isset($data['activities'])) {
                $this->activitiesService->sync($registration, $data['activities']);
            }

            if (isset($data['files'])) {
                $this->repositoryService->sync($registration, $data['files']);
            }

            if (isset($data['links'])) {
                $this->linksService->sync($registration, $data['links']);
            }

            return $registration->fresh(['activities', 'repositories', 'links']);
        });
    }

    public function changeStatus(
        Registration $registration,
        RegistrationStatus $status,
        ?string $reviewNotes = null
    ): Registration {
        $reviewer = Auth::user();

        $registration->update([
            'enum_status' => $status,
            'reviewed_at' => now(),
            'reviewed_by' => $reviewer?->id,
            'review_notes' => $reviewNotes,
        ]);

        // TODO: Implement the logic for notifications and user/artist creation based on the status change.
        // IF APPROUVED: create User, Create Artist, Notificate
        // IF REPROUVED: NOTIFICATE
        // IF PENDING: NOTIFICATE

        return $registration;
    }
}
