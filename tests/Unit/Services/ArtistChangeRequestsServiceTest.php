<?php

namespace Tests\Unit\Services;

use App\Database\Models\Artist;
use App\Database\Models\ArtistChangeRequest;
use App\Database\Models\Repository;
use App\Enums\ArtistChangeRequestStatus;
use App\Services\ArtistChangeRequestsService;
use App\Services\ArtistsService;
use App\Services\RepositoriesService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Mockery;
use RuntimeException;
use Tests\TestCase;
use Tests\Unit\Services\Traits\Trait_Seed;

class ArtistChangeRequestsServiceTest extends TestCase
{
    use DatabaseTransactions;
    use Trait_Seed;

    private ArtistChangeRequestsService $service;
    private RepositoriesService $repositoryService;
    private ArtistsService $artistsService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repositoryService = Mockery::mock(RepositoriesService::class);
        $this->artistsService = Mockery::mock(ArtistsService::class);

        $this->service = new ArtistChangeRequestsService($this->repositoryService, $this->artistsService,);
    }

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function testCreateChangeRequest(): void
    {
        $artist = $this->seedArtist();
        $request = $this->service->create($artist, ['artist_name' => 'New Artist']);
        $payload = json_decode($request->payload, true);

        $this->assertDatabaseHas('artists_change_requests', [
            'artist_id' => $artist->id,
            'enum_status' => ArtistChangeRequestStatus::PENDING->value,
        ]);
        $this->assertEquals('New Artist', $payload['artist_name']);
    }

    public function testCreateChangeRequestStoresOnlyChangedFields(): void
    {
        $artist = $this->seedArtist();
        $request = $this->service->create($artist, [
            'artist_name' => 'Changed',
            'email' => $artist->email,
            'phone' => $artist->phone,
        ]);
        $payload = json_decode($request->payload, true);

        $this->assertArrayHasKey('artist_name', $payload);
        $this->assertArrayNotHasKey('email', $payload);
        $this->assertArrayNotHasKey('phone', $payload);
    }

    public function testCreateChangeRequestIgnoresIgnoredFields(): void
    {
        $artist = $this->seedArtist();
        $request = $this->service->create($artist, [
            'artist_name' => 'Changed',
            'registration_id' => $artist->registration_id + 1,
            'user_id' => $artist->user_id + 1,
            'enum_status' => 'DRAFT',
            'published_at' => now(),
            'confirmed_at' => now(),
            'reminded_at' => now(),
            'rep_image' => 'some-value',
        ]);
        $payload = json_decode($request->payload, true);

        $this->assertArrayHasKey('artist_name', $payload);
        $this->assertArrayNotHasKey('registration_id', $payload);
        $this->assertArrayNotHasKey('user_id', $payload);
        $this->assertArrayNotHasKey('enum_status', $payload);
        $this->assertArrayNotHasKey('published_at', $payload);
        $this->assertArrayNotHasKey('confirmed_at', $payload);
        $this->assertArrayNotHasKey('reminded_at', $payload);
        $this->assertArrayNotHasKey('rep_image', $payload);
    }

    public function testDeletePreviousChangeRequestedRequests(): void
    {
        $artist = $this->seedArtist();
        $this->seedArtistChangeRequest($artist, ArtistChangeRequestStatus::CHANGES_REQUESTED);
        $this->service->create($artist, ['artist_name' => 'Changed']);

        $this->assertDatabaseCount('artists_change_requests', 1);
    }

    public function testCreateChangeRequestDoesNotDeleteOtherStatusRequests(): void
    {
        $artist = $this->seedArtist();
        $this->seedArtistChangeRequest($artist, ArtistChangeRequestStatus::PENDING);
        $this->seedArtistChangeRequest($artist, ArtistChangeRequestStatus::APPROVED);
        $this->seedArtistChangeRequest($artist, ArtistChangeRequestStatus::REJECTED);

        $this->service->create($artist, ['artist_name' => 'Changed']);

        $this->assertDatabaseCount('artists_change_requests', 4);
        $this->assertDatabaseHas('artists_change_requests', [
            'artist_id' => $artist->id,
            'enum_status' => ArtistChangeRequestStatus::PENDING->value,
        ]);
        $this->assertDatabaseHas('artists_change_requests', [
            'artist_id' => $artist->id,
            'enum_status' => ArtistChangeRequestStatus::APPROVED->value,
        ]);
        $this->assertDatabaseHas('artists_change_requests', [
            'artist_id' => $artist->id,
            'enum_status' => ArtistChangeRequestStatus::REJECTED->value,
        ]);
    }

    public function testThrowsExceptionWhenNoChangesDetected(): void
    {
        $artist = $this->seedArtist();

        $this->expectException(RuntimeException::class);
        $this->service->create($artist, [
            'artist_name' => $artist->artist_name,
            'email' => $artist->email,
            'phone' => $artist->phone,
            'city' => $artist->city,
            'biography' => $artist->biography,
            'discipline_secondary' => $artist->discipline_secondary,
            'enum_show_contact' => $artist->enum_show_contact,
        ]);
    }

    public function testCreateChangeRequestWithActivities(): void
    {
        $artist = $this->seedArtist();
        $activity = $this->seedActivity($this->seedDiscipline());
        $request = $this->service->create($artist, ['activities' => [$activity->id]]);
        $payload = json_decode($request->payload, true);

        $this->assertEquals([$activity->id], $payload['activities']);
    }

    public function testCreateChangeRequestWithUnchangedActivitiesThrowsException(): void
    {
        $artist = $this->seedArtist();

        $this->expectException(RuntimeException::class);
        $this->service->create($artist, ['activities' => []]);
    }

    public function testCreateChangeRequestWithLinks(): void
    {
        $artist = $this->seedArtist();
        $links = [['enum_type' => 'WEB', 'link' => 'https://example.com']];
        $request = $this->service->create($artist, ['links' => $links]);
        $payload = json_decode($request->payload, true);

        $this->assertEquals($links, $payload['links']);
    }

    public function testCreateChangeRequestWithUnchangedLinksThrowsException(): void
    {
        $artist = $this->seedArtist();

        $this->expectException(RuntimeException::class);
        $this->service->create($artist, ['links' => []]);
    }

    public function testCreateChangeRequestWithKeywords(): void
    {
        $artist = $this->seedArtist();
        $keywords = ['Test Keyword'];
        $request = $this->service->create($artist, ['keywords' => $keywords]);
        $payload = json_decode($request->payload, true);

        $this->assertEquals($keywords, $payload['keywords']);
    }

    public function testCreateChangeRequestWithUnchangedKeywordsThrowsException(): void
    {
        $artist = $this->seedArtist();

        $this->expectException(RuntimeException::class);
        $this->service->create($artist, ['keywords' => []]);
    }

    public function testCreateChangeRequestWithImage(): void
    {
        $artist = $this->seedArtist();
        $image = UploadedFile::fake()->image('artist.jpg');

        $repository = new Repository();
        $repository->id = 999;

        $this->repositoryService
            ->shouldReceive('create')
            ->once()
            ->with(Mockery::type(ArtistChangeRequest::class), $image)
            ->andReturn($repository);

        $request = $this->service->create($artist, [
            'artist_name' => 'Changed',
            'image' => $image,
        ]);
        $payload = json_decode($request->payload, true);

        $this->assertTrue($payload['image']);
        $this->assertDatabaseHas('artists_change_requests', [
            'id' => $request->id,
            'artist_id' => $artist->id,
        ]);
    }

    public function testChangeStatusUpdatesReviewerInformation(): void
    {
        $artist = $this->seedArtist();
        $user = $this->seedUser(['email' => 'reviewer@test.com']);
        $this->actingAs($user);
        $request = $this->seedArtistChangeRequest($artist);
        $this->artistsService
            ->shouldReceive('update')
            ->once()
            ->with(Mockery::type(Artist::class), Mockery::type(ArtistChangeRequest::class));
        $this->service->changeStatus($request, ArtistChangeRequestStatus::APPROVED, 'Approved');

        $this->assertDatabaseHas('artists_change_requests',
            [
                'id' => $request->id,
                'enum_status' => ArtistChangeRequestStatus::APPROVED->value,
                'reviewed_by' => $user->id,
                'review_notes' => 'Approved',
            ]
        );
    }

    public function testChangeStatusWithoutAuthenticatedUser(): void
    {
        $artist = $this->seedArtist();
        $request = $this->seedArtistChangeRequest($artist);
        $this->artistsService->shouldNotReceive('update');
        $this->service->changeStatus($request, ArtistChangeRequestStatus::REJECTED);

        $this->assertDatabaseHas('artists_change_requests',
            [
                'id' => $request->id,
                'enum_status' => ArtistChangeRequestStatus::REJECTED->value,
                'reviewed_by' => null,
            ]
        );
    }

    public function testChangeStatusApprovedCallsArtistsServiceUpdate(): void
    {
        $artist = $this->seedArtist();
        $request = $this->seedArtistChangeRequest($artist);
        $this->artistsService
            ->shouldReceive('update')
            ->once()
            ->with(
                Mockery::on(fn (Artist $a) => $a->id === $artist->id),
                Mockery::on(fn (ArtistChangeRequest $r) => $r->id === $request->id),
            );
        $updated = $this->service->changeStatus($request, ArtistChangeRequestStatus::APPROVED);

        $this->assertEquals(ArtistChangeRequestStatus::APPROVED, $updated->enum_status);
    }

    public function testChangeStatusRejectedDoesNotCallArtistsServiceUpdate(): void
    {
        $artist = $this->seedArtist();
        $request = $this->seedArtistChangeRequest($artist);
        $this->artistsService->shouldNotReceive('update');
        $updated = $this->service->changeStatus($request, ArtistChangeRequestStatus::REJECTED);

        $this->assertEquals(ArtistChangeRequestStatus::REJECTED, $updated->enum_status);
    }

    public function testChangeStatusPendingDoesNotCallArtistsServiceUpdate(): void
    {
        $artist = $this->seedArtist();
        $request = $this->seedArtistChangeRequest($artist, ArtistChangeRequestStatus::CHANGES_REQUESTED);
        $this->artistsService->shouldNotReceive('update');
        $updated = $this->service->changeStatus($request, ArtistChangeRequestStatus::PENDING);

        $this->assertEquals(ArtistChangeRequestStatus::PENDING, $updated->enum_status);
    }
}
