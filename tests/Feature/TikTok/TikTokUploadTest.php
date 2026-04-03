<?php

use App\Models\TikTokCredential;
use App\Models\User;

test('user can see upload form when connected', function () {
    $user = User::factory()->create();
    TikTokCredential::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user)
        ->get(route('tiktok.manage'))
        ->assertOk()
        ->assertSee('Upload a clip');
});

test('user cannot see upload form when not connected', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('tiktok.manage'))
        ->assertOk()
        ->assertSee('No TikTok accounts connected yet.');
});

test('expired credential does not show upload form', function () {
    $user = User::factory()->create();
    TikTokCredential::factory()->expired()->create(['user_id' => $user->id]);

    $this->actingAs($user)
        ->get(route('tiktok.manage'))
        ->assertOk()
        ->assertSee('Token expired')
        ->assertDontSee('Upload a clip');
});

test('user can have multiple tiktok accounts', function () {
    $user = User::factory()->create();
    TikTokCredential::factory()->count(2)->create(['user_id' => $user->id]);

    $this->actingAs($user)
        ->get(route('tiktok.manage'))
        ->assertOk();

    expect($user->tiktokCredentials)->toHaveCount(2);
});
