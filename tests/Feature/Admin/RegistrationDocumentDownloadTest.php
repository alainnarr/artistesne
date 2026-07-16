<?php

use App\Database\Models\Discipline;
use App\Database\Models\Registration;
use App\Database\Models\User;
use App\Enums\RegistrationStatus;
use App\Enums\RepositoryDisk;
use App\Filament\Resources\Registrations\Pages\ViewRegistration;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

function makeRegistrationWithDocument(): Registration
{
    Storage::fake('private');

    $musique = Discipline::where('code', 'musique')->firstOrFail();

    $registration = Registration::create([
        'real_name' => 'Demande Test',
        'artist_name' => 'Demande Test',
        'birth_date' => now()->subYears(25)->toDateString(),
        'email' => 'demande.doc-'.uniqid().'@inventaire.test',
        'phone' => '+41791234567',
        'residence_location' => 'Neuchâtel',
        'discipline_main' => $musique->id,
        'enum_status' => RegistrationStatus::OPEN->value,
    ]);

    $file = UploadedFile::fake()->create('cv.pdf', 100, 'application/pdf');
    $path = $file->store('registrations', 'private');

    $registration->repositories()->create([
        'name' => 'cv.pdf',
        'file_type' => 'application/pdf',
        'size' => $file->getSize(),
        'enum_disk' => RepositoryDisk::PRIVATE,
        'path' => $path,
    ]);

    return $registration->refresh();
}

it('shows attached documents on the registration review page', function () {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin);

    $registration = makeRegistrationWithDocument();

    Livewire::test(ViewRegistration::class, ['record' => $registration->id])
        ->assertSee('cv.pdf');
});

it('lets an admin download a private registration document', function () {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin);

    $registration = makeRegistrationWithDocument();
    $repository = $registration->repositories()->firstOrFail();

    $response = $this->get(route('admin.registrations.documents.download', [
        'registration' => $registration,
        'repository' => $repository,
    ]));

    $response->assertOk();
});

it('forbids non-admins from downloading a registration document', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $registration = makeRegistrationWithDocument();
    $repository = $registration->repositories()->firstOrFail();

    $response = $this->get(route('admin.registrations.documents.download', [
        'registration' => $registration,
        'repository' => $repository,
    ]));

    $response->assertForbidden();
});

it('returns 404 when the repository does not belong to the registration', function () {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin);

    $registrationA = makeRegistrationWithDocument();
    $registrationB = makeRegistrationWithDocument();
    $repositoryB = $registrationB->repositories()->firstOrFail();

    $response = $this->get(route('admin.registrations.documents.download', [
        'registration' => $registrationA,
        'repository' => $repositoryB,
    ]));

    $response->assertNotFound();
});
