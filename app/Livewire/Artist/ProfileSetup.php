<?php

namespace App\Livewire\Artist;

use App\Database\Models\User;
use App\Enums\ArtistShowContact;
use App\Livewire\Artist\Concerns\ManagesArtistProfileFields;
use Illuminate\Contracts\View\View;
use Illuminate\Http\UploadedFile;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.artist')]
class ProfileSetup extends Component
{
    use ManagesArtistProfileFields, WithFileUploads;

    public int $currentStep = 1;

    public int $totalSteps = 2;

    // Read-only confirmation fields
    public string $displayName = '';

    public string $displayEmail = '';

    public string $displayLocality = '';

    // ── Step 1 — Activités ──────────────────────────────────────────────────

    #[Validate('nullable|image|mimes:jpg,jpeg,png|max:5120|dimensions:min_width=400,min_height=500')]
    public ?UploadedFile $photo = null;

    #[Validate('required|string|max:5000')]
    public string $biography = '';

    #[Validate('nullable|integer|exists:disciplines,id')]
    public ?int $discipline_main_id = null;

    #[Validate('nullable|integer|exists:disciplines,id')]
    public ?int $discipline_secondary = null;

    /** @var array<int, string> */
    #[Validate('array|max:4')]
    public array $activities = [];

    /** @var array<int, string> */
    #[Validate('array')]
    public array $secondary_activities = [];

    /** @var array<int, string> */
    #[Validate('array')]
    public array $keywords = [];

    public string $newActivity = '';

    public string $newSecondaryActivity = '';

    public string $newKeyword = '';

    // ── Step 2 — Liens ──────────────────────────────────────────────────────

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

    public bool $submitted = false;

    public function mount(): void
    {
        /** @var User $user */
        $user = auth()->user();
        $artist = $user->artist;
        abort_unless($artist !== null, 404);

        $this->displayName = $artist->artist_name;
        $this->displayEmail = $user->email;
        $this->displayLocality = $artist->city ?? '—';

        $this->biography = $this->htmlToText($artist->biography ?? '');
        $this->discipline_main_id = $artist->discipline_main_id;
        $this->discipline_secondary = $artist->discipline_secondary;
        $this->activities = $artist->activities ?? [];
        $this->secondary_activities = $artist->secondary_activities ?? [];
        $this->keywords = $artist->keywords ?? [];
        $this->links = $artist->links ?? [];
        $this->collaborations = $artist->collaborations ?? [];
        $this->display_contact_button = $artist->enum_show_contact?->toBool() ?? false;
    }

    public function nextStep(): void
    {
        $this->validateOnly('biography');
        $this->currentStep = min($this->totalSteps, $this->currentStep + 1);
    }

    public function previousStep(): void
    {
        $this->currentStep = max(1, $this->currentStep - 1);
    }

    // ── Save ────────────────────────────────────────────────────────────────

    public function save(): void
    {
        $this->validate();

        /** @var User $user */
        $user = auth()->user();
        $artist = $user->artist;

        $artist->forceFill([
            'biography' => $this->textToHtml($this->biography),
            'discipline_main_id' => $this->discipline_main_id,
            'discipline_secondary' => $this->discipline_secondary,
            'activities' => array_values($this->activities),
            'secondary_activities' => array_values($this->secondary_activities),
            'keywords' => array_values($this->keywords),
            'links' => array_values($this->links),
            'collaborations' => array_values($this->collaborations),
            'enum_show_contact' => ArtistShowContact::fromBool($this->display_contact_button)->value,
        ]);

        if ($this->photo) {
            $this->storeBwPortrait($artist, $this->photo);
        }

        $artist->save();

        $this->submitted = true;
    }

    public function render(): View
    {
        /** @var User|null $user */
        $user = auth()->user();
        $artist = $user?->artist;

        return view('livewire.artist.profile-setup', [
            'disciplineOptions' => $this->getDisciplineOptionsProperty(),
            'mainActivityOptions' => $this->getMainActivityOptionsProperty(),
            'secondaryActivityOptions' => $this->getSecondaryActivityOptionsProperty(),
            'currentImageUrl' => $artist?->repImage?->file,
        ]);
    }
}
