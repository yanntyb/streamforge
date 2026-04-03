<?php

use App\Models\TikTokCredential;
use App\Models\User;
use Illuminate\Support\Facades\Http;

test('guests cannot access tiktok manage page', function () {
    $this->get(route('tiktok.manage'))
        ->assertRedirect(route('login'));
});

test('authenticated users can view tiktok manage page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('tiktok.manage'))
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
            'scope' => 'user.info.basic,video.upload,video.publish,video.list',
        ]),
    ]);

    $this->actingAs($user)
        ->withSession(['tiktok_oauth_state' => 'test_state'])
        ->get(route('tiktok.callback', [
            'code' => 'test_code',
            'state' => 'test_state',
        ]))
        ->assertRedirect(route('tiktok.manage'))
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
        ->assertRedirect(route('tiktok.manage'))
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
        ->assertRedirect(route('tiktok.manage'))
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
