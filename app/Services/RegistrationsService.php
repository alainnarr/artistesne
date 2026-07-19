<?php
declare(strict_types=1);

namespace App\Services;

use App\Database\Models\Registration;
use App\Enums\RegistrationStatus;
use App\Enums\RepositoryDisk;
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
        private readonly UsersService $usersService,
        private readonly ArtistsService $artistsService
    ) {}

    public function create(array $data, RegistrationStatus $status = RegistrationStatus::OPEN): Registration
    {
        $registrationData = Arr::except($data, ['activities', 'files', 'links',]);
        $registrationData = array_merge($registrationData, ['enum_status' => $status]);
        Validator::make($registrationData, Registration::getRules())->validate();

        return DB::transaction(function () use ($data, $registrationData) {
            $registration = Registration::create($registrationData);

            // It is not possible to use `attach` directly, as it is not captured by Audit.
            $this->activitiesService->attachMultiple($registration, $data['activities'] ?? []);

            if (! empty($data['files'])) {
                // Supporting documents (ID, portfolio, etc.) are attached to an
                // unreviewed registration — they must never be publicly
                // reachable before (or after) a gestionnaire has approved it.
                $this->repositoryService->createMultiple($registration, $data['files'], RepositoryDisk::PRIVATE);
            }

            $this->linksService->createMultiple($registration, $data['links'] ?? []);

            return $registration->fresh(['activities', 'repositories', 'links']);
        });
    }

    public function update(Registration $registration, array $data): Registration
    {
        Validator::make($data, Registration::getRules(array_keys($data), ['id' => $registration->id]))->validate();

        return DB::transaction(function () use ($registration, $data) {

            $registrationData = Arr::except($data, ['activities', 'files', 'links']);

            $registration->update($registrationData);

            if (isset($data['activities'])) {
                $this->activitiesService->sync($registration, $data['activities']);
            }

            if (isset($data['files'])) {
                $this->repositoryService->sync($registration, $data['files'], RepositoryDisk::PRIVATE);
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

        if ($registration->enum_status === $status) {
            return $registration;
        }

        $data = [
            'enum_status' => $status,
            'reviewed_at' => now(),
            'reviewed_by' => Auth::id() ?? null,
            'review_notes' => $reviewNotes,
        ];
        Validator::make($data, Registration::getRules(array_keys($data), ['id' => $registration->id]))->validate();

        return DB::transaction(function () use ($registration, $data) {
            if ($data['enum_status'] === RegistrationStatus::APPROVED) {
            $user = $this->usersService->create($registration->email, $registration->name);
            $this->artistsService->create($registration, $user);
            }

            $registration->update([
                'enum_status' => $data['enum_status'],
                'reviewed_at' => $data['reviewed_at'],
                'reviewed_by' => $data['reviewed_by'],
                'review_notes' => $data['review_notes'],
            ]);

            return $registration;
        });
    }
}
