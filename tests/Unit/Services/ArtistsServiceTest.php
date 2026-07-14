<?php

namespace Tests\Unit\Services;

use App\Database\Models\Artist;
use App\Database\Models\ArtistChangeRequest;
use App\Database\Models\Registration;
use App\Database\Models\User;
use App\Enums\ArtistStatus;
use App\Services\ActivitiesService;
use App\Services\ArtistsService;
use App\Services\KeywordsService;
use App\Services\LinksService;
use App\Services\RepositoriesService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery;
use Tests\TestCase;
use Tests\Unit\Services\Traits\Trait_Seed;
class ArtistsServiceTest extends TestCase {
    use DatabaseTransactions;
    use Trait_Seed;

    private ArtistsService $service;
    private ActivitiesService $activitiesService;
    private LinksService $linksService;
    private RepositoriesService $repositoryService;
    private KeywordsService $keywordsService;

    protected function setUp(): void {
        parent::setUp();

        $this->activitiesService = Mockery::mock(ActivitiesService::class);
        $this->linksService = Mockery::mock(LinksService::class);
        $this->repositoryService = Mockery::mock(RepositoriesService::class);
        $this->keywordsService = Mockery::mock(KeywordsService::class);

        $this->service = new ArtistsService(
            $this->activitiesService,
            $this->linksService,
            $this->repositoryService,
            $this->keywordsService,
        );
    }

    protected function tearDown(): void {
        Mockery::close();

        parent::tearDown();
    }

    public function testCreateArtistFromRegistration(): void {
        $registration = $this->seedRegistration();
        $user = $this->seedUser();
        $this->activitiesService->shouldReceive('sync')->once();
        $artist = $this->service->create($registration, $user);

        $this->assertInstanceOf(Artist::class, $artist);
        $this->assertEquals($registration->id, $artist->registration_id);
        $this->assertEquals($user->id, $artist->user_id);
        $this->assertEquals(ArtistStatus::Draft, $artist->enum_status);
    }

    public function testCreateArtistWithCustomStatus(): void {
        $registration = $this->seedRegistration();
        $user = $this->seedUser();
        $this->activitiesService->shouldReceive('sync')->once();
        $artist = $this->service->create($registration, $user, ArtistStatus::Published);

        $this->assertEquals(ArtistStatus::Published, $artist->enum_status);
    }

    public function testUpdateArtistUpdatesBasicFields(): void {
        $artist = $this->seedArtist();
        $changeRequest = ArtistChangeRequest::create([
                                                         'artist_id' => $artist->id,
                                                         'payload' => [
                                                             'artist_name' => 'New Name',
                                                         ],
                                                     ]);
        $updated = $this->service->update($artist, $changeRequest);
        $this->assertEquals('New Name', $updated->artist_name);
        $this->assertEquals(ArtistStatus::Published, $updated->enum_status);
        $this->assertNotNull($updated->confirmed_at);
    }

    public function testUpdateArtistSetsPublishedAtWhenNotPublished(): void {
        $artist = $this->seedArtist();
        $artist->update(['published_at' => null]);
        $changeRequest = ArtistChangeRequest::create([
                                                         'artist_id' => $artist->id,
                                                         'payload' => [
                                                             'artist_name' => 'Updated',
                                                         ],
                                                     ]);
        $updated = $this->service->update($artist, $changeRequest);

        $this->assertNotNull($updated->published_at);
    }

    public function testUpdateArtistDoesNotChangePublishedAt(): void {
        $artist = $this->seedArtist();
        $publishedAt = now()->subDay();
        $artist->update(['published_at' => $publishedAt]);
        $changeRequest = ArtistChangeRequest::create([
                                                         'artist_id' => $artist->id,
                                                         'payload' => [
                                                             'artist_name' => 'Updated',
                                                         ],
                                                     ]);
        $updated = $this->service->update($artist, $changeRequest);
        $this->assertEquals($publishedAt->timestamp, $updated->published_at->timestamp);
    }

    public function testUpdateSyncsActivities(): void {
        $artist = $this->seedArtist();
        $activities = [1, 2, 3];
        $changeRequest = ArtistChangeRequest::create([
                                                         'artist_id' => $artist->id,
                                                         'payload' => [
                                                             'activities' => $activities,
                                                         ],
                                                     ]);
        $this->activitiesService
            ->shouldReceive('sync')
            ->once()
            ->with(
                Mockery::type(Artist::class),
                $activities
            );

        $this->service->update($artist, $changeRequest);
    }

    public function testUpdateSyncsLinks(): void {
        $artist = $this->seedArtist();
        $links = [
            [
                'enum_type' => 'website',
                'link' => 'https://example.com',
            ],
        ];
        $changeRequest = ArtistChangeRequest::create([
                                                         'artist_id' => $artist->id,
                                                         'payload' => [
                                                             'links' => $links,
                                                         ],
                                                     ]);
        $this->linksService->shouldReceive('sync')->once();
        $this->service->update($artist, $changeRequest);
    }

    public function testUpdateSyncsKeywords(): void {
        $artist = $this->seedArtist();
        $keywords = ['Rock', 'Pop'];
        $changeRequest = ArtistChangeRequest::create([
                                                         'artist_id' => $artist->id,
                                                         'payload' => [
                                                             'keywords' => $keywords,
                                                         ],
                                                     ]);
        $this->keywordsService->shouldReceive('sync')->once();
        $this->service->update($artist, $changeRequest);
    }

    public function testUpdateReplicatesImage(): void {
        $artist = $this->seedArtist();
        $image = $this->seedRepository();
        $changeRequest = ArtistChangeRequest::create([
                                                         'artist_id' => $artist->id,
                                                         'payload' => [
                                                             'image' => true,
                                                         ],
                                                         'image_id' => $image->id,
                                                     ]);
        $changeRequest->load('image');
        $this->repositoryService->shouldReceive('replicateRepository')->once();
        $this->service->update($artist, $changeRequest);
    }
}
