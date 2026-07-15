<?php

namespace App\Livewire\Public;

use App\Database\Models\Activity;
use App\Database\Models\Discipline;
use App\Database\Models\Registration;
use App\Database\Models\User;
use App\Enums\DisciplineType;
use App\Enums\RegistrationStatus;
use App\Enums\UserRole;
use App\Notifications\NewRegistrationNotification;
use App\Services\RegistrationsService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * @property-read bool $isOutsideCanton
 * @property-read bool $isOtherActivity
 * @property-read int $criteriaCount
 * @property-read array<string, array<int, string>> $localityGroups
 * @property-read array<int, string> $domainOptions
 * @property-read array<int, string> $availableActivities
 */
#[Layout('components.layouts.public')]
class RegisterArtist extends Component
{
    use WithFileUploads;

    public int $currentStep = 1;

    public int $totalSteps = 3;

    // --- Étape 1 : Identité ---
    public string $full_name = '';

    public string $artist_name = '';

    public bool $show_artist_name = false;

    public ?string $birth_date = null;

    public string $email = '';

    public bool $display_contact_button = false;

    public string $phoneCountry = 'CH';

    public ?string $phone = null;

    // --- Étape 1 : Territorialité ---
    public string $locality = '';

    public ?string $commune = null;

    public ?string $canton_link = null;

    // --- Étape 2 : Domaine & activités ---
    public string $main_domain = '';

    public string $main_activity = '';

    public ?string $main_activity_other = null;

    // --- Étape 2 : Professionnalisme ---
    public ?string $training = null;

    public ?string $paid_activity = null;

    public ?string $recognition = null;

    public ?string $recent_achievement = null;

    // --- Étape 2 : Temporalité ---
    public ?string $last_activity = null;

    // --- Étape 3 : Documents & liens ---
    /** @var array<int, mixed> */
    public array $documents = [];

    /** @var array<int, string> */
    public array $links = [''];

    public bool $attests = false;

    public ?string $turnstileToken = null;

    public bool $submitted = false;

    /**
     * @return array<string, array<int, mixed>>
     */
    protected function rules(): array
    {
        return [
            // Étape 1
            'full_name' => ['required', 'string', 'max:255'],
            'artist_name' => ['nullable', 'string', 'max:255'],
            'show_artist_name' => ['boolean'],
            'birth_date' => ['required', 'date', 'before:today'],
            'email' => ['required', 'email', 'max:255'],
            'display_contact_button' => ['boolean'],
            'phoneCountry' => ['required', 'string', 'size:2', 'regex:/^[A-Z]{2}$/'],
            'phone' => ['required', 'string', 'max:50', function (string $attribute, mixed $value, \Closure $fail): void {
                if (! $this->isValidPhoneNumber($value)) {
                    $fail('Veuillez saisir un numéro de téléphone valide.');
                }
            }],
            'locality' => ['required', 'string', 'max:255'],
            'commune' => [Rule::requiredIf($this->isOutsideCanton), 'nullable', 'string', 'max:255'],
            'canton_link' => [Rule::requiredIf($this->isOutsideCanton), 'nullable', 'string', 'max:500'],

            // Étape 2
            'main_domain' => ['required', Rule::exists('disciplines', 'id')],
            'main_activity' => ['required', Rule::exists('activities', 'id')],
            'main_activity_other' => [Rule::requiredIf($this->isOtherActivity), 'nullable', 'string', 'max:255'],
            'training' => ['nullable', 'string', 'max:1000'],
            'paid_activity' => ['nullable', 'string', 'max:1000'],
            'recognition' => ['nullable', 'string', 'max:1000'],
            'recent_achievement' => [Rule::requiredIf($this->criteriaCount < 2), 'nullable', 'string', 'max:1000'],
            'last_activity' => ['nullable', 'string', 'max:500'],

            // Étape 3
            'documents' => ['nullable', 'array', 'max:10'],
            'documents.*' => ['file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'links' => ['nullable', 'array'],
            'links.*' => ['nullable', 'string', 'url:http,https', 'max:255'],
            'attests' => ['accepted'],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return [
            'full_name.required' => 'Ce champ est obligatoire.',
            'birth_date.required' => 'Ce champ est obligatoire.',
            'birth_date.before' => "La date de naissance doit être antérieure à aujourd'hui.",
            'email.required' => 'Ce champ est obligatoire.',
            'email.email' => 'Veuillez saisir une adresse e-mail valide.',
            'phone.required' => 'Ce champ est obligatoire.',
            'locality.required' => 'Veuillez sélectionner votre lieu de résidence.',
            'commune.required' => 'Veuillez préciser votre commune de résidence.',
            'canton_link.required' => 'Décrivez votre ancrage dans le tissu culturel neuchâtelois (activité régulière et significative, partenariats, collaborations).',
            'main_domain.required' => 'Veuillez sélectionner un domaine principal.',
            'main_domain.exists' => 'Veuillez sélectionner un domaine principal valide.',
            'main_activity.required' => 'Veuillez sélectionner une activité principale.',
            'main_activity.exists' => 'Veuillez sélectionner une activité principale valide.',
            'main_activity_other.required' => 'Merci de préciser votre activité.',
            'recent_achievement.required' => "Si un seul critère sur 3 est rempli, merci d'indiquer au moins une réalisation artistique dans un cadre professionnel au cours des 3 dernières années.",
            'links.*.url' => 'Veuillez saisir une URL valide (https://…).',
            'documents.*.mimes' => 'Formats acceptés : PDF, JPG, PNG.',
            'documents.*.max' => 'Chaque fichier doit faire 5 Mo maximum.',
            'attests' => "Vous devez attester l'exactitude des informations transmises.",
        ];
    }

    /**
     * Champs validés par étape (validation au blur et passage d'étape).
     *
     * @return array<int, array<int, string>>
     */
    protected function stepFields(): array
    {
        return [
            1 => ['full_name', 'artist_name', 'show_artist_name', 'birth_date', 'email', 'display_contact_button', 'phoneCountry', 'phone', 'locality', 'commune', 'canton_link'],
            2 => ['main_domain', 'main_activity', 'main_activity_other', 'training', 'paid_activity', 'recognition', 'recent_achievement', 'last_activity'],
            3 => ['documents', 'documents.*', 'links', 'links.*', 'attests'],
        ];
    }

    #[Computed]
    public function isOutsideCanton(): bool
    {
        return $this->locality === config('localities.outside_canton_value');
    }

    /**
     * Valide un numéro international en acceptant les formats usuels.
     */
    private function isValidPhoneNumber(?string $value): bool
    {
        if (blank($value)) {
            return false;
        }

        $raw = trim((string) $value);
        $normalized = preg_replace('/[^\d+]/', '', $raw) ?? '';

        if ($normalized === '') {
            return false;
        }

        if (substr_count($normalized, '+') > 1 || (str_contains($normalized, '+') && ! str_starts_with($normalized, '+'))) {
            return false;
        }

        $digits = preg_replace('/\D+/', '', $normalized) ?? '';
        $length = strlen($digits);

        return $length >= 6 && $length <= 15;
    }

    public function updatedPhoneCountry(string $value): void
    {
        $this->phoneCountry = strtoupper($value);
    }

    #[Computed]
    public function isOtherActivity(): bool
    {
        return $this->main_activity === config('taxonomy.other_value');
    }

    #[Computed]
    public function criteriaCount(): int
    {
        return (int) filled($this->training)
            + (int) filled($this->paid_activity)
            + (int) filled($this->recognition);
    }

    /**
     * @return array<string, array<int, string>>
     */
    #[Computed]
    public function localityGroups(): array
    {
        return config('localities.groups');
    }

    /**
     * Discipline options from DB, keyed by ID.
     *
     * @return array<int, string>
     */
    #[Computed]
    public function domainOptions(): array
    {
        return Discipline::where('enum_type', DisciplineType::MAIN->value)
            ->orderBy('label')
            ->pluck('label', 'id')
            ->all();
    }

    /**
     * Activities for the selected discipline, keyed by ID.
     *
     * @return array<int, string>
     */
    #[Computed]
    public function availableActivities(): array
    {
        if (blank($this->main_domain)) {
            return [];
        }

        return Activity::where('discipline_id', (int) $this->main_domain)
            ->orderBy('label')
            ->pluck('label', 'id')
            ->all();
    }

    public function updatedMainDomain(): void
    {
        $this->main_activity = '';
        $this->main_activity_other = null;
    }

    public function updatedLocality(): void
    {
        if (! $this->isOutsideCanton) {
            $this->commune = null;
            $this->canton_link = null;
        }
    }

    /**
     * Valide un champ dès que l'utilisateur le quitte (wire:model.blur).
     */
    public function updated(string $property): void
    {
        $rules = $this->rules();

        if (! array_key_exists($property, $rules)) {
            return;
        }

        $this->validateOnly($property);
    }

    public function removeDocument(int $index): void
    {
        unset($this->documents[$index]);
        $this->documents = array_values($this->documents);
    }

    public function addLink(): void
    {
        $this->links[] = '';
    }

    public function removeLink(int $index): void
    {
        unset($this->links[$index]);
        $this->links = array_values($this->links);

        if ($this->links === []) {
            $this->links = [''];
        }
    }

    public function nextStep(): void
    {
        $this->validate($this->rulesFor($this->currentStep));

        if ($this->currentStep < $this->totalSteps) {
            $this->currentStep++;
        }
    }

    public function previousStep(): void
    {
        if ($this->currentStep > 1) {
            $this->currentStep--;
        }
    }

    /**
     * Sous-ensemble de règles pour une étape donnée.
     *
     * @return array<string, array<int, mixed>>
     */
    protected function rulesFor(int $step): array
    {
        $rules = $this->rules();
        $fields = $this->stepFields()[$step] ?? [];

        return array_intersect_key($rules, array_flip($fields));
    }

    public function submit(): void
    {
        $data = $this->validate();

        if (! $this->verifyTurnstile()) {
            $this->addError('turnstileToken', 'La vérification anti-robot a échoué. Veuillez réessayer.');

            return;
        }

        $alreadyPending = Registration::where('email', $data['email'])
            ->whereIn('enum_status', [RegistrationStatus::OPEN->value, RegistrationStatus::PENDING->value])
            ->exists();

        if ($alreadyPending) {
            $this->addError('email', 'Une demande est déjà en cours de traitement pour cette adresse e-mail. Merci de patienter le temps de son examen avant d\'en soumettre une nouvelle.');

            return;
        }

        $registration = app(RegistrationsService::class)->create([
            'real_name' => $data['full_name'],
            'artist_name' => filled($data['artist_name']) ? $data['artist_name'] : $data['full_name'],
            'birth_date' => $data['birth_date'],
            'email' => $data['email'],
            'phone' => trim((string) $data['phone']),
            'residence_location' => $this->isOutsideCanton ? $data['commune'] : $data['locality'],
            'locality' => $data['locality'],
            'canton_link' => $data['canton_link'],
            'discipline_main' => (int) $data['main_domain'],
            'training' => $data['training'],
            'paid_work' => $data['paid_activity'],
            'recognition' => $data['recognition'],
            'recent_achievements' => $data['recent_achievement'],
            'last_work' => $data['last_activity'],
            'enum_status' => RegistrationStatus::OPEN->value,
            'activities' => [(int) $data['main_activity']],
            'files' => $this->documents,
        ]);

        $admins = User::where('role', UserRole::Admin)->get();

        if ($admins->isEmpty()) {
            Log::warning('No admin user found to notify of new registration.', ['registration_id' => $registration->id]);
        }

        try {
            Notification::send($admins, new NewRegistrationNotification($registration));
        } catch (\Throwable $e) {
            // The registration itself is already saved at this point — a mail
            // failure must not surface as an error to the applicant nor block
            // their submission from completing.
            Log::error('Failed to send NewRegistrationNotification to admins.', [
                'registration_id' => $registration->id,
                'exception' => $e->getMessage(),
            ]);
        }

        $this->submitted = true;
    }

    protected function verifyTurnstile(): bool
    {
        if (! config('services.turnstile.enabled')) {
            return true;
        }

        if (blank($this->turnstileToken)) {
            return false;
        }

        $response = Http::asForm()->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
            'secret' => config('services.turnstile.secret_key'),
            'response' => $this->turnstileToken,
        ]);

        return $response->successful() && $response->json('success') === true;
    }

    public function render(): View
    {
        return view('livewire.public.register-artist');
    }
}
