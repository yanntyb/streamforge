<?php

namespace App\Http\Integrations\TikTok;

use Illuminate\Support\Facades\Http;
use Saloon\Http\Connector;
use Saloon\Traits\Plugins\AcceptsJson;

class TikTokConnector extends Connector
{
    use AcceptsJson;

    public function resolveBaseUrl(): string
    {
        return 'https://open.tiktokapis.com/v2';
    }

    protected function defaultConfig(): array
    {
        return [
            'timeout' => 30,
        ];
    }

    public function getAuthorizationUrl(string $state): string
    {
        $params = http_build_query([
            'client_key' => config('services.tiktok.client_key'),
            'redirect_uri' => config('services.tiktok.redirect_uri'),
            'response_type' => 'code',
            'scope' => 'user.info.basic,video.publish,video.upload',
            'state' => $state,
        ]);

        return 'https://www.tiktok.com/v2/auth/authorize/?'.$params;
    }

    /**
     * @return array{access_token: string, refresh_token: string, open_id: string, expires_in: int, scope: string}
     */
    public function exchangeCodeForTokens(string $code): array
    {
        $response = Http::asForm()->post('https://open.tiktokapis.com/v2/oauth/token/', [
            'client_key' => config('services.tiktok.client_key'),
            'client_secret' => config('services.tiktok.client_secret'),
            'code' => $code,
            'grant_type' => 'authorization_code',
            'redirect_uri' => config('services.tiktok.redirect_uri'),
        ]);

        $response->throw();

        return $response->json();
    }

    /**
     * @return array{access_token: string, refresh_token: string, open_id: string, expires_in: int, scope: string}
     */
    public function refreshAccessToken(string $refreshToken): array
    {
        $response = Http::asForm()->post('https://open.tiktokapis.com/v2/oauth/token/', [
            'client_key' => config('services.tiktok.client_key'),
            'client_secret' => config('services.tiktok.client_secret'),
            'refresh_token' => $refreshToken,
            'grant_type' => 'refresh_token',
        ]);

        $response->throw();

        return $response->json();
    }

    /**
     * Upload video binary to TikTok's upload URL.
     */
    public function uploadVideoChunk(string $uploadUrl, string $filePath, int $fileSize): void
    {
        $response = Http::withHeaders([
            'Content-Range' => 'bytes 0-'.($fileSize - 1).'/'.$fileSize,
            'Content-Type' => 'video/mp4',
        ])->withBody(file_get_contents($filePath), 'video/mp4')
            ->put($uploadUrl);

        $response->throw();
    }
}
