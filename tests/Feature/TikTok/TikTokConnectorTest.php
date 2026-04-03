<?php

use App\Http\Integrations\TikTok\TikTokConnector;
use App\Models\TikTokCredential;

test('TikTokConnector builds correct authorization url', function () {
    config([
        'services.tiktok.client_key' => 'test_client_key',
        'services.tiktok.redirect_uri' => 'https://example.com/callback',
    ]);

    $connector = new TikTokConnector;
    $url = $connector->getAuthorizationUrl('test_state');

    expect($url)
        ->toContain('https://www.tiktok.com/v2/auth/authorize/')
        ->toContain('client_key=test_client_key')
        ->toContain('redirect_uri='.urlencode('https://example.com/callback'))
        ->toContain('response_type=code')
        ->toContain('state=test_state')
        ->toContain('scope=user.info.basic');
});

test('TikTokCredential isExpired returns true for expired tokens', function () {
    $credential = TikTokCredential::factory()->expired()->create();

    expect($credential->isExpired())->toBeTrue();
});

test('TikTokCredential isExpired returns false for valid tokens', function () {
    $credential = TikTokCredential::factory()->create();

    expect($credential->isExpired())->toBeFalse();
});

test('TikTokConnector resolves correct base url', function () {
    $connector = new TikTokConnector;

    expect($connector->resolveBaseUrl())->toBe('https://open.tiktokapis.com/v2');
});
