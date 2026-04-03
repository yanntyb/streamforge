<?php

use App\Models\TikTokCredential;
use App\Models\User;
use Illuminate\Support\Facades\Http;

test('guests cannot access tiktok accounts page', function () {
    $this->get('/dashboard/tiktok-accounts')
        ->assertRedirect('/dashboard/login');
});

test('authenticated users can view tiktok accounts page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/dashboard/tiktok-accounts')
        ->assertOk();
});

test('tiktok callback stores credentials', function () {
    $user = User::factory()->create();

    Http::fake([
        'open.tiktokapis.com/v2/oauth/token/' => Http::response([
            'access_token' => 'test_access_token',
            'refresh_token' => 'test_refresh_token',
            'open_id' => 'test_open_id_123',
            'expires_in' => 86400,
            'scope' => 'user.info.basic,video.upload,video.publish',
        ]),
    ]);

    $this->actingAs($user)
        ->withSession(['tiktok_oauth_state' => 'test_state'])
        ->get(route('tiktok.callback', [
            'code' => 'test_code',
            'state' => 'test_state',
        ]))
        ->assertRedirect('/dashboard/tiktok-accounts')
        ->assertSessionHas('success');

    $this->assertDatabaseHas('tik_tok_credentials', [
        'user_id' => $user->id,
        'tiktok_open_id' => 'test_open_id_123',
    ]);
});

test('tiktok callback rejects invalid state', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->withSession(['tiktok_oauth_state' => 'correct_state'])
        ->get(route('tiktok.callback', [
            'code' => 'test_code',
            'state' => 'wrong_state',
        ]))
        ->assertRedirect('/dashboard/tiktok-accounts')
        ->assertSessionHas('error');

    $this->assertDatabaseCount('tik_tok_credentials', 0);
});

test('tiktok callback handles denied authorization', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('tiktok.callback', [
            'error' => 'access_denied',
            'error_description' => 'User denied access',
        ]))
        ->assertRedirect('/dashboard/tiktok-accounts')
        ->assertSessionHas('error');
});

test('tiktok callback updates existing credential for same open_id', function () {
    $user = User::factory()->create();
    TikTokCredential::factory()->create([
        'user_id' => $user->id,
        'tiktok_open_id' => 'existing_open_id',
        'access_token' => 'old_token',
    ]);

    Http::fake([
        'open.tiktokapis.com/v2/oauth/token/' => Http::response([
            'access_token' => 'new_access_token',
            'refresh_token' => 'new_refresh_token',
            'open_id' => 'existing_open_id',
            'expires_in' => 86400,
            'scope' => 'user.info.basic,video.upload',
        ]),
    ]);

    $this->actingAs($user)
        ->withSession(['tiktok_oauth_state' => 'test_state'])
        ->get(route('tiktok.callback', [
            'code' => 'test_code',
            'state' => 'test_state',
        ]));

    $this->assertDatabaseCount('tik_tok_credentials', 1);
});
