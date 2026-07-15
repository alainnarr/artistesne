<?php

namespace Tests\Unit\Services;

use App\Database\Models\Artist;
use App\Database\Models\Repository;
use App\Enums\ArtistStatus;
use App\Services\ArtistsService;
use App\Services\ActivitiesService;
use App\Services\LinksService;
use App\Services\KeywordsService;
use App\Services\RepositoriesService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;
use Tests\Unit\Services\Traits\Trait_Seed;
use App\Database\Models\ArtistChangeRequest;
use App\Enums\ArtistChangeRequestStatus;

class ArtistsServiceTest extends TestCase
{
    use RefreshDatabase;
    use Trait_Seed;

    private ArtistsService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = $this->app->make(ArtistsService::class);
    }

    public function testCreatePersistsArtist(): void
    {
        $registration = $this->seedRegistration();
        $user = $this->seedUser([
            'email' => $registration->email,
        ]);
        $artist = $this->service->create($registration, $user);

        $this->assertInstanceOf(Artist::class, $artist);
        $this->assertDatabaseHas('newartists', [
            'id' => $artist->id,
            'registration_id' => $registration->id,
            'user_id' => $user->id,
            'email' => $registration->email,
        ]);
    }

    public function testCreateCopiesRegistrationData(): void
    {
        $registration = $this->seedRegistration([
            'artist_name' => 'My Artist',
            'phone' => '999999',
            'locality' => 'Bern',
        ]);
        $user = $this->seedUser([
            'email' => $registration->email,
        ]);
        $artist = $this->service->create($registration, $user);

        $this->assertEquals($registration->name, $artist->artist_name);
        $this->assertEquals($registration->phone, $artist->phone);
        $this->assertEquals($registration->city, $artist->city);
    }

    public function testCreateCopiesActivities(): void
    {
        $registration = $this->seedRegistration();
        $discipline = $registration->disciplineMain;
        $activity = $this->seedActivity($discipline);
        $registration->activities()->attach($activity);
        $user = $this->seedUser([
            'email' => $registration->email,
        ]);
        $artist = $this->service->create($registration, $user);

        $this->assertDatabaseHas('activities_artists', ['artist_id' => $artist->id, 'activity_id' => $activity->id]);
    }

    public function testCreateAllowsStatus(): void
    {
        $registration = $this->seedRegistration();
        $user = $this->seedUser([
            'email' => $registration->email,
        ]);
        $artist = $this->service->create($registration, $user, ArtistStatus::Published);

        $this->assertEquals(ArtistStatus::Published, $artist->enum_status);
    }

    public function testUpdatePersistsChanges(): void
    {
        $artist = $this->seedArtist();
        $request = $this->seedArtistChangeRequest(
            $artist,
            payload: [
                'artist_name' => 'New Artist',
                'biography' => 'New Biography',
            ]
        );
        $updated = $this->service->update($artist, $request);

        $this->assertEquals('New Artist', $updated->artist_name);
        $this->assertEquals('New Biography', $updated->biography);
        $this->assertDatabaseHas('newartists', [
            'id' => $artist->id,
            'artist_name' => 'New Artist',
            'biography' => 'New Biography',
        ]);
    }

    public function testUpdatePublishesArtist(): void
    {
        $artist = $this->seedArtist([
            'enum_status' => ArtistStatus::Draft,
            'published_at' => null,
            'confirmed_at' => null,
        ]);
        $request = $this->seedArtistChangeRequest($artist);
        $updated = $this->service->update($artist, $request);

        $this->assertEquals(ArtistStatus::Published, $updated->enum_status);
        $this->assertNotNull($updated->confirmed_at);
        $this->assertNotNull($updated->published_at);
    }

    public function testUpdateDoesNotOverridePublishedAt(): void
    {
        $published = now()->subWeek()->startOfDay();
        $artist = $this->seedArtist(['published_at' => $published]);
        $request = $this->seedArtistChangeRequest($artist);
        $updated = $this->service->update($artist, $request);

        $this->assertEquals($published, $updated->published_at);
    }

    public function testUpdateSyncsActivities(): void
    {
        $artist = $this->seedArtist();
        $discipline = $this->seedDiscipline();
        $activity = $this->seedActivity($discipline);
        $request = $this->seedArtistChangeRequest(
            $artist,
            payload: [
                'artist_name' => $artist->artist_name,
                'activities' => [$activity->id],
            ]
        );
        $this->service->update($artist, $request);

        $this->assertDatabaseHas('activities_artists', ['artist_id' => $artist->id, 'activity_id' => $activity->id]);
    }

    public function testUpdateThrowsValidationException(): void
    {
        $this->expectException(ValidationException::class);

        $artist = $this->seedArtist();
        $request = $this->seedArtistChangeRequest(
            $artist,
            payload: [
                'email' => 'invalid-email',
            ]
        );
        $this->service->update($artist, $request);
    }

    public function testUpdateWithNonArrayPayloadThrowsValidationException(): void
    {
        $this->expectException(ValidationException::class);
        $artist = $this->seedArtist();
        $request = ArtistChangeRequest::create([
            'artist_id' => $artist->id,
            'payload' => 123,
            'enum_status' => ArtistChangeRequestStatus::PENDING,
        ]);

        $this->service->update($artist, $request);
    }

    public function testUpdateSyncsLinks(): void
    {
        $this->mock(LinksService::class, function ($mock) {
            $mock->shouldReceive('sync')->once();
        });
        $service = $this->app->make(ArtistsService::class);
        $artist = $this->seedArtist();
        $request = $this->seedArtistChangeRequest(
            $artist,
            payload: [
                'artist_name' => $artist->artist_name,
                'links' => ['https://example.com'],
            ]
        );
        $service->update($artist, $request);
    }

    public function testUpdateSyncsKeywords(): void
    {
        $this->mock(KeywordsService::class, function ($mock) {
            $mock->shouldReceive('sync')->once();
        });
        $service = $this->app->make(ArtistsService::class);
        $artist = $this->seedArtist();
        $request = $this->seedArtistChangeRequest(
            $artist,
            payload: [
                'artist_name' => $artist->artist_name,
                'keywords' => ['keyword-one'],
            ]
        );
        $service->update($artist, $request);
    }

    private function makeService(): ArtistsService
    {
        return new ArtistsService(
            $this->app->make(ActivitiesService::class),
            $this->app->make(LinksService::class),
            $this->app->make(RepositoriesService::class),
            $this->app->make(KeywordsService::class),
        );
    }

    public function testUpdateReplicatesImageRepository(): void
    {
        $this->mock(RepositoriesService::class, function ($mock) {
            $mock->shouldReceive('replicateRepository')->once();
        });

        $service = $this->makeService();
        $artist = $this->seedArtist();
        $request = $this->seedArtistChangeRequest(
            $artist,
            payload: [
                'artist_name' => $artist->artist_name,
                'image' => true,
            ]
        );
        $request->setRelation('image', new Repository());
        $service->update($artist, $request);
    }
}
