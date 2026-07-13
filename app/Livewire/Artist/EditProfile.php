<?php

namespace App\Livewire\Artist;

use App\Enums\ApprovalStatus;
use App\Enums\UserRole;
use App\Livewire\Artist\Concerns\ManagesArtistProfileFields;
use App\Models\ArtistChangeRequest;
use App\Models\User;
use App\Notifications\ChangeRequestSubmittedNotification;
use Illuminate\Contracts\View\View;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.artist')]
class EditProfile extends Component
{
    use ManagesArtistProfileFields, WithFileUploads;

    #[Validate('nullable|image|mimes:jpg,jpeg,png|max:5120|dimensions:min_width=400,min_height=500')]
    public ?UploadedFile $photo = null;

    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('nullable|string|max:120')]
    public ?string $discipline = null;

    #[Validate('nullable|string|max:255')]
    public ?string $city = null;

    #[Validate('nullable|string|max:120')]
    public ?string $secondary_discipline = null;

    #[Validate('required|string|max:5000')]
    public string $biographyText = '';

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

    public string $newKeyword = '';

    public bool $submitted = false;

    public bool $hasPendingChange = false;

    public function mount(): void
    {
        /** @var User $user */
        $user = auth()->user();
        $artist = $user->artist;
        abort_unless($artist, 404, 'Aucune page artiste rattachée.');

        $this->name = $artist->name;
        $this->discipline = $artist->discipline;
        $this->city = $artist->city;
        $this->secondary_discipline = $artist->secondary_discipline;
        $this->biographyText = $this->htmlToText($artist->biography ?? '');
        $this->activities = $artist->activities ?? [];
        $this->secondary_activities = $artist->secondary_activities ?? [];
        $this->keywords = $artist->keywords ?? [];
        $this->links = $artist->links ?? [];
        $this->collaborations = $artist->collaborations ?? [];
        $this->display_contact_button = (bool) $artist->display_contact_button;
        $this->hasPendingChange = (bool) $artist->pendingChangeRequest();
    }

    public function save(): void
    {
        if ($this->hasPendingChange) {
            $this->addError('biographyText', 'Une modification est déjà en attente de validation.');

            return;
        }

        $data = $this->validate();
        /** @var User $user */
        $user = auth()->user();
        $artist = $user->artist;

        $proposed = [
            'name' => $data['name'],
            'discipline' => $data['discipline'],
            'city' => $data['city'],
            'secondary_discipline' => $data['secondary_discipline'],
            'biography' => $this->textToHtml($data['biographyText']),
            'activities' => array_values($data['activities']),
            'secondary_activities' => array_values($data['secondary_activities']),
            'keywords' => array_values($data['keywords']),
            'links' => array_values($data['links']),
            'collaborations' => array_values($data['collaborations']),
            'display_contact_button' => $data['display_contact_button'],
        ];

        // Photo is handled separately — saved immediately, not via change request.
        if ($this->photo) {
            $artist->cover_image = $this->storeBwPortrait($this->photo, $artist->cover_image);
            $artist->saveQuietly();
            $this->photo = null;
        }

        // Keep only fields that actually changed.
        $payload = collect($proposed)
            ->filter(fn ($value, $key) => $artist->{$key} != $value)
            ->all();

        if (empty($payload)) {
            $this->addError('biographyText', 'Aucune modification détectée.');

            return;
        }

        $changeRequest = ArtistChangeRequest::create([
            'artist_id' => $artist->id,
            'submitted_by' => auth()->id(),
            'payload' => $payload,
            'status' => ApprovalStatus::Pending,
        ]);

        $admins = User::where('role', UserRole::Admin)->get();
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
            'currentImageUrl' => $artist?->cover_image
                ? Storage::url($artist->cover_image)
                : null,
        ]);
    }
}
