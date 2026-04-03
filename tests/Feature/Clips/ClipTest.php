<?php

use App\Filament\Clusters\Clips\Pages\ListClips;
use App\Models\Clip;
use App\Models\ClipPlatformUpload;
use App\Models\TikTokCredential;
use App\Models\User;
use Livewire\Livewire;

test('clip belongs to a user', function () {
    $clip = Clip::factory()->create();

    expect($clip->user)->toBeInstanceOf(User::class);
});

test('clip has many platform uploads', function () {
    $clip = Clip::factory()->create();
    ClipPlatformUpload::factory()->count(2)->create(['clip_id' => $clip->id]);

    expect($clip->platformUploads)->toHaveCount(2);
});

test('clip platform upload has polymorphic platform relation', function () {
    $credential = TikTokCredential::factory()->create();
    $upload = ClipPlatformUpload::factory()->create([
        'platform_type' => TikTokCredential::class,
        'platform_id' => $credential->id,
    ]);

    expect($upload->platform)->toBeInstanceOf(TikTokCredential::class)
        ->and($upload->platform->id)->toBe($credential->id);
});

test('user has many clips', function () {
    $user = User::factory()->create();
    Clip::factory()->count(3)->create(['user_id' => $user->id]);

    expect($user->clips)->toHaveCount(3);
});

test('list clips page is accessible', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/dashboard/clips/list-clips')
        ->assertOk();
});

test('list clips page shows uploaded clips', function () {
    $user = User::factory()->create();
    $clip = Clip::factory()->create(['user_id' => $user->id, 'title' => 'Mon super clip']);

    $this->actingAs($user);

    Livewire::test(ListClips::class)
        ->assertCanSeeTableRecords([$clip]);
});

test('list clips page only shows own clips', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $ownClip = Clip::factory()->create(['user_id' => $user->id]);
    $otherClip = Clip::factory()->create(['user_id' => $otherUser->id]);

    $this->actingAs($user);

    Livewire::test(ListClips::class)
        ->assertCanSeeTableRecords([$ownClip])
        ->assertCanNotSeeTableRecords([$otherClip]);
});

test('list clips page shows empty state when no clips', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/dashboard/clips/list-clips')
        ->assertOk()
        ->assertSee('Aucun clip upload');
});

test('clip has thumbnail_path attribute', function () {
    $clip = Clip::factory()->withThumbnail()->create();

    expect($clip->thumbnail_path)->toBe('thumbnails/fake-thumb.jpg');
});

test('clip thumbnail_path is nullable', function () {
    $clip = Clip::factory()->create();

    expect($clip->thumbnail_path)->toBeNull();
});

test('list clips page renders thumbnail column', function () {
    $user = User::factory()->create();
    Clip::factory()->withThumbnail()->create(['user_id' => $user->id]);

    $this->actingAs($user);

    Livewire::test(ListClips::class)
        ->assertSuccessful();
});

test('deleting a clip cascades to platform uploads', function () {
    $clip = Clip::factory()->create();
    ClipPlatformUpload::factory()->create(['clip_id' => $clip->id]);

    $clip->delete();

    expect(ClipPlatformUpload::where('clip_id', $clip->id)->count())->toBe(0);
});
