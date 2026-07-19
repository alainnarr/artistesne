<?php

namespace App\Livewire\Artist;

use App\Database\Models\ArtistChangeRequest;
use App\Database\Models\User;
use App\Enums\ArtistChangeRequestStatus;
use App\Enums\ArtistShowContact;
use App\Enums\UserRole;
use App\Livewire\Artist\Concerns\ManagesArtistProfileFields;
use App\Notifications\ChangeRequestSubmittedNotification;
use Illuminate\Contracts\View\View;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.artist')]
class EditProfile extends Component
{
    use ManagesArtistProfileFields, WithFileUploads;

    public int $currentStep = 1;

    public int $totalSteps = 2;

    #[Validate('nullable|image|mimes:jpg,jpeg,png|max:5120|dimensions:min_width=400,min_height=500')]
    public ?UploadedFile $photo = null;

    #[Validate('required|string|max:255')]
    public string $artist_name = '';

    #[Validate('nullable|integer|exists:disciplines,id')]
    public ?int $discipline_main_id = null;

    #[Validate('nullable|string|max:125')]
    public ?string $city = null;

    #[Validate('nullable|integer|exists:disciplines,id')]
    public ?int $discipline_secondary = null;

    #[Validate('required|string|max:5000')]
    public string $biography = '';

    /** @var array<int, string> */
    #[Validate('array|max:4')]
    public array $activities = [];

    /** @var array<int, string> */
    #[Validate('array')]
    public array $secondary_activities = [];

    /** @var array<int, string> */
    #[Validate('array')]
    public array $keywords = [];

    /**
     * @var array<int, array{label: string, url: string}>
     */
    #[Validate([
        'links' => 'array|max:6',
        'links.*.label' => 'required|string|max:80',
        'links.*.url' => 'required|url:http,https|max:255',
    ])]
    public array $links = [];

    /**
     * @var array<int, array{name: string, url: string}>
     */
    #[Validate([
        'collaborations' => 'array|max:10',
        'collaborations.*.name' => 'required|string|max:120',
        'collaborations.*.url' => 'nullable|url:http,https|max:255',
    ])]
    public array $collaborations = [];

    #[Validate('boolean')]
    public bool $display_contact_button = false;

    public string $newActivity = '';

    public string $newSecondaryActivity = '';

    public string $newKeyword = '';

    public bool $submitted = false;

    public bool $hasPendingChange = false;

    public function mount(): void
    {
        /** @var User $user */
        $user = auth()->user();
        $artist = $user->artist;
        abort_unless($artist !== null, 404, 'Aucune page artiste rattachée.');

        $this->artist_name = $artist->artist_name;
        $this->discipline_main_id = $artist->discipline_main_id;
        $this->city = $artist->city;
        $this->discipline_secondary = $artist->discipline_secondary;
        $this->biography = $this->htmlToText($artist->biography ?? '');
        $this->activities = $artist->activities ?? [];
        $this->secondary_activities = $artist->secondary_activities ?? [];
        $this->keywords = $artist->keywords ?? [];
        $this->links = $artist->links ?? [];
        $this->collaborations = $artist->collaborations ?? [];
        $this->display_contact_button = $artist->enum_show_contact?->toBool() ?? false;
        $this->hasPendingChange = (bool) $artist->pendingChangeRequest();
    }

    public function nextStep(): void
    {
        $this->validateOnly('artist_name');
        $this->validateOnly('biography');
        $this->currentStep = min($this->totalSteps, $this->currentStep + 1);
    }

    public function previousStep(): void
    {
        $this->currentStep = max(1, $this->currentStep - 1);
    }

    public function save(): void
    {
        if ($this->hasPendingChange) {
            $this->addError('biography', 'Une modification est déjà en attente de validation.');

            return;
        }

        $data = $this->validate();
        /** @var User $user */
        $user = auth()->user();
        $artist = $user->artist;

        $proposed = [
            'artist_name' => $data['artist_name'],
            'discipline_main_id' => $data['discipline_main_id'],
            'city' => $data['city'],
            'discipline_secondary' => $data['discipline_secondary'],
            'biography' => $this->textToHtml($data['biography']),
            'activities' => array_values($data['activities']),
            'secondary_activities' => array_values($data['secondary_activities']),
            'keywords' => array_values($data['keywords']),
            'links' => array_values($data['links']),
            'collaborations' => array_values($data['collaborations']),
            'enum_show_contact' => ArtistShowContact::fromBool($data['display_contact_button'])->value,
        ];

        // Photo is handled separately — saved immediately, not via change request.
        if ($this->photo) {
            $this->storeBwPortrait($artist, $this->photo);
            $artist->saveQuietly();
            $this->photo = null;
        }

        // Keep only fields that actually changed (unwrap enum casts for comparison).
        $payload = collect($proposed)
            ->filter(function ($value, $key) use ($artist) {
                $current = $artist->{$key};

                if ($current instanceof \BackedEnum) {
                    $current = $current->value;
                }

                return $current != $value;
            })
            ->all();

        if (empty($payload)) {
            $this->addError('biography', 'Aucune modification détectée.');

            return;
        }

        $changeRequest = ArtistChangeRequest::create([
            'artist_id' => $artist->id,
            'submitted_by' => auth()->id(),
            'payload' => $payload,
            'status' => ArtistChangeRequestStatus::PENDING->value,
        ]);

        $admins = User::where('role', UserRole::ADMIN)->get();
        Notification::send($admins, new ChangeRequestSubmittedNotification($changeRequest));

        $this->hasPendingChange = true;
        $this->submitted = true;
    }

    public function render(): View
    {
        /** @var User|null $user */
        $user = auth()->user();
        $artist = $user?->artist;

        return view('livewire.artist.edit-profile', [
            'disciplineOptions' => $this->getDisciplineOptionsProperty(),
            'mainActivityOptions' => $this->getMainActivityOptionsProperty(),
            'secondaryActivityOptions' => $this->getSecondaryActivityOptionsProperty(),
            'currentImageUrl' => $artist?->repImage?->file,
            'fullName' => $artist?->registration?->real_name,
            'email' => $artist?->email,
        ]);
    }
}
