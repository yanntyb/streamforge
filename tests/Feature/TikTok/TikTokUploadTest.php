<?php

use App\Models\TikTokCredential;
use App\Models\User;

test('upload page shows link when no active accounts', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/dashboard/clips/upload-clip')
        ->assertOk()
        ->assertSee('Aucun compte TikTok actif');
});

test('upload page shows form when active account exists', function () {
    $user = User::factory()->create();
    TikTokCredential::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user)
        ->get('/dashboard/clips/upload-clip')
        ->assertOk()
        ->assertSee('Publier sur TikTok');
});

test('tiktok accounts page shows connected accounts', function () {
    $user = User::factory()->create();
    TikTokCredential::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user)
        ->get('/dashboard/plateform/tiktok-accounts')
        ->assertOk()
        ->assertSee('Connecté');
});

test('tiktok accounts page shows expired badge', function () {
    $user = User::factory()->create();
    TikTokCredential::factory()->expired()->create(['user_id' => $user->id]);

    $this->actingAs($user)
        ->get('/dashboard/plateform/tiktok-accounts')
        ->assertOk()
        ->assertSee('Expiré');
});

test('user can have multiple tiktok accounts', function () {
    $user = User::factory()->create();
    TikTokCredential::factory()->count(2)->create(['user_id' => $user->id]);

    expect($user->tiktokCredentials)->toHaveCount(2);
});
