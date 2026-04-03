<x-filament-panels::page>
    <div class="space-y-4">
        @forelse ($this->credentials as $credential)
            <x-filament::section>
                <div class="flex items-center justify-between">
                    <div class="space-y-1">
                        <h3 class="text-base font-semibold">
                            {{ $credential->tiktok_username ?? $credential->tiktok_open_id }}
                        </h3>
                        <div class="flex items-center gap-2">
                            @if ($credential->isExpired())
                                <x-filament::badge color="danger" size="sm">
                                    {{ __('Token expired') }}
                                </x-filament::badge>
                            @else
                                <x-filament::badge color="success" size="sm">
                                    {{ __('Connected') }}
                                </x-filament::badge>
                                <span class="text-xs text-gray-500">
                                    {{ __('Expires :date', ['date' => $credential->token_expires_at->diffForHumans()]) }}
                                </span>
                            @endif
                        </div>
                    </div>
                    <x-filament::button
                        color="danger"
                        size="sm"
                        wire:click="disconnectTikTok({{ $credential->id }})"
                        wire:confirm="{{ __('Are you sure you want to disconnect this account?') }}"
                    >
                        {{ __('Disconnect') }}
                    </x-filament::button>
                </div>
            </x-filament::section>
        @empty
            <x-filament::section>
                <p class="text-center text-sm text-gray-500">
                    {{ __('No TikTok accounts connected yet.') }}
                </p>
            </x-filament::section>
        @endforelse
    </div>

    <x-filament::button wire:click="connectTikTok" icon="heroicon-o-plus">
        {{ __('Add TikTok Account') }}
    </x-filament::button>
</x-filament-panels::page>
