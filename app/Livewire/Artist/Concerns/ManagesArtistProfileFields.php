<?php

namespace App\Livewire\Artist\Concerns;

use App\Models\TaxonomyTerm;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Shared behaviour for the artist profile forms (ProfileSetup + EditProfile):
 * tag/link/collaboration management, biography HTML↔text conversion, portrait
 * processing and discipline options.
 *
 * The consuming component must declare the `$activities`, `$keywords`,
 * `$links`, `$collaborations`, `$newActivity` and `$newKeyword` properties.
 */
trait ManagesArtistProfileFields
{
    public function addActivity(): void
    {
        $value = trim($this->newActivity);

        if ($value !== '' && count($this->activities) < 4) {
            $this->activities[] = $value;
        }

        $this->newActivity = '';
    }

    public function removeActivity(int $index): void
    {
        unset($this->activities[$index]);
        $this->activities = array_values($this->activities);
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
     * Discipline dropdown options, administered via the Filament Taxonomies page.
     *
     * @return array<string, string>
     */
    public function getDisciplineOptionsProperty(): array
    {
        return TaxonomyTerm::domainOptions();
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
     * Convert the upload to a B&W JPEG, store it in the public artists/ folder
     * and delete the previous file. Returns the new storage path.
     */
    protected function storeBwPortrait(UploadedFile $upload, ?string $existing): string
    {
        $contents = file_get_contents($upload->getRealPath());
        $img = imagecreatefromstring($contents);

        if ($img === false) {
            return $existing ?? '';
        }

        imagefilter($img, IMG_FILTER_GRAYSCALE);

        $filename = 'artists/portrait_'.uniqid('', true).'.jpg';
        Storage::disk('public')->makeDirectory('artists');
        $path = Storage::disk('public')->path($filename);

        imagejpeg($img, $path, 90);
        imagedestroy($img);

        if ($existing && Storage::disk('public')->exists($existing)) {
            Storage::disk('public')->delete($existing);
        }

        return $filename;
    }
}
