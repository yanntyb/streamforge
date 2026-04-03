<?php

namespace App\Http\Controllers;

use App\Http\Integrations\TikTok\TikTokConnector;
use App\Models\TikTokCredential;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TikTokCallbackController extends Controller
{
    public function __invoke(Request $request, TikTokConnector $connector): RedirectResponse
    {
        if ($request->has('error')) {
            return redirect()->route('tiktok.manage')
                ->with('error', 'TikTok authorization was denied: '.$request->string('error_description', 'Unknown error'));
        }

        if ($request->input('state') !== session('tiktok_oauth_state')) {
            return redirect()->route('tiktok.manage')
                ->with('error', 'Invalid OAuth state. Please try again.');
        }

        session()->forget('tiktok_oauth_state');

        $tokens = $connector->exchangeCodeForTokens($request->input('code'));

        \Log::info('TikTok token response', $tokens);

        TikTokCredential::updateOrCreate(
            ['tiktok_open_id' => $tokens['open_id']],
            [
                'user_id' => $request->user()->id,
                'access_token' => $tokens['access_token'],
                'refresh_token' => $tokens['refresh_token'],
                'token_expires_at' => now()->addSeconds($tokens['expires_in']),
                'scopes' => isset($tokens['scope']) ? explode(',', $tokens['scope']) : null,
            ],
        );

        return redirect()->route('tiktok.manage')
            ->with('success', 'TikTok account connected successfully.');
    }
}
