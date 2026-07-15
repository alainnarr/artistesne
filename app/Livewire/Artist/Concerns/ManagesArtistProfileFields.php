<?php

namespace App\Livewire\Artist\Concerns;

use App\Database\Models\Activity;
use App\Database\Models\Artist;
use App\Database\Models\Discipline;
use App\Enums\DisciplineType;
use App\Enums\RepositoryDisk;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Shared behaviour for the artist profile forms (ProfileSetup + EditProfile):
 * tag/link/collaboration management, biography HTML↔text conversion, portrait
 * processing and discipline options.
 *
 * The consuming component must declare the `$discipline_main_id`, `$activities`,
 * `$secondary_activities`, `$keywords`, `$newActivity`, `$newSecondaryActivity`
 * and `$newKeyword` properties.
 */
trait ManagesArtistProfileFields
{
    /**
     * Suggested "activités principales" for the currently selected main
     * discipline, excluding options already picked — mirrors the selector
     * used on the public registration form and the Filament admin form
     * (Activity records, not free text) instead of an open text field.
     *
     * @return array<int, string>
     */
    public function getMainActivityOptionsProperty(): array
    {
        if (blank($this->discipline_main_id)) {
            return [];
        }

        return Activity::where('discipline_id', (int) $this->discipline_main_id)
            ->orderBy('label')
            ->pluck('label')
            ->reject(fn (string $label) => in_array($label, $this->activities, true))
            ->values()
            ->all();
    }

    /**
     * Suggested "activités secondaires" — the flat, discipline-agnostic list
     * (the "secondaire" pseudo-discipline), excluding options already picked.
     *
     * @return array<int, string>
     */
    public function getSecondaryActivityOptionsProperty(): array
    {
        return Activity::whereRelation('discipline', 'enum_type', DisciplineType::SECONDARY->value)
            ->orderBy('label')
            ->pluck('label')
            ->reject(fn (string $label) => in_array($label, $this->secondary_activities, true))
            ->values()
            ->all();
    }

    public function addActivity(): void
    {
        $value = trim($this->newActivity);

        if ($value !== '' && count($this->activities) < 4 && ! in_array($value, $this->activities, true)) {
            $this->activities[] = $value;
        }

        $this->newActivity = '';
    }

    public function removeActivity(int $index): void
    {
        unset($this->activities[$index]);
        $this->activities = array_values($this->activities);
    }

    public function addSecondaryActivity(): void
    {
        $value = trim($this->newSecondaryActivity);

        if ($value !== '' && ! in_array($value, $this->secondary_activities, true)) {
            $this->secondary_activities[] = $value;
        }

        $this->newSecondaryActivity = '';
    }

    public function removeSecondaryActivity(int $index): void
    {
        unset($this->secondary_activities[$index]);
        $this->secondary_activities = array_values($this->secondary_activities);
    }

    /**
     * Resets the picked "activités principales" whenever the main discipline
     * changes, since the suggested list is scoped to that discipline (mirrors
     * the Filament admin form's `afterStateUpdated` on `discipline_main_id`).
     */
    public function updatedDisciplineMainId(): void
    {
        $this->activities = [];
    }

    public function addKeyword(): void
    {
        $value = trim($this->newKeyword);

        if ($value !== '') {
            $this->keywords[] = $value;
        }

        $this->newKeyword = '';
    }

    public function removeKeyword(int $index): void
    {
        unset($this->keywords[$index]);
        $this->keywords = array_values($this->keywords);
    }

    public function addLink(): void
    {
        $this->links[] = ['label' => '', 'url' => ''];
    }

    public function removeLink(int $index): void
    {
        unset($this->links[$index]);
        $this->links = array_values($this->links);
    }

    public function addCollaboration(): void
    {
        $this->collaborations[] = ['name' => '', 'url' => ''];
    }

    public function removeCollaboration(int $index): void
    {
        unset($this->collaborations[$index]);
        $this->collaborations = array_values($this->collaborations);
    }

    /**
     * Discipline dropdown options, keyed by id (matches Artist::discipline_main_id
     * / discipline_secondary FK columns).
     *
     * @return array<int, string>
     */
    public function getDisciplineOptionsProperty(): array
    {
        return Discipline::query()->orderBy('label')->pluck('label', 'id')->all();
    }

    protected function htmlToText(string $html): string
    {
        $text = preg_replace('#</p>\s*<p[^>]*>#i', "\n\n", $html);
        $text = preg_replace('#<br\s*/?>#i', "\n", (string) $text);
        $text = strip_tags((string) $text);

        return trim(html_entity_decode($text));
    }

    protected function textToHtml(string $text): string
    {
        return collect(preg_split('/\n{2,}/', trim($text)))
            ->filter(fn (string $paragraph) => Str::length(trim($paragraph)) > 0)
            ->map(fn (string $paragraph) => '<p>'.nl2br(e(trim($paragraph)), false).'</p>')
            ->implode('');
    }

    /**
     * Convert the upload to a B&W JPEG, store it in the public disk and
     * attach it to the artist's `rep_image` Repository record.
     */
    protected function storeBwPortrait(Artist $artist, UploadedFile $upload): void
    {
        $contents = file_get_contents($upload->getRealPath());
        $img = imagecreatefromstring($contents);

        if ($img === false) {
            return;
        }

        imagefilter($img, IMG_FILTER_GRAYSCALE);

        $filename = 'artists/portrait_'.uniqid('', true).'.jpg';
        Storage::disk('public')->makeDirectory('artists');
        $path = Storage::disk('public')->path($filename);

        imagejpeg($img, $path, 90);
        imagedestroy($img);

        $existingPath = $artist->repImage?->path;

        $size = Storage::disk('public')->size($filename);

        if ($artist->repImage) {
            $artist->repImage->update([
                'name' => 'portrait.jpg',
                'file_type' => 'image/jpeg',
                'size' => $size,
                'path' => $filename,
            ]);
        } else {
            $repository = $artist->repositories()->create([
                'name' => 'portrait.jpg',
                'file_type' => 'image/jpeg',
                'size' => $size,
                'enum_disk' => RepositoryDisk::PUBLIC->value,
                'path' => $filename,
            ]);
            $artist->rep_image = $repository->id;
        }

        if ($existingPath && $existingPath !== $filename && Storage::disk('public')->exists($existingPath)) {
            Storage::disk('public')->delete($existingPath);
        }
    }
}
