<x-filament-panels::page>
    @if (! $this->hasActiveCredentials())
        <x-filament::section>
            <p class="text-center text-sm text-gray-500 dark:text-gray-400">
                No active TikTok accounts. Connect one first on the
                <a href="{{ \App\Filament\Clusters\Plateform\Pages\TikTokAccounts::getUrl() }}" class="text-primary-600 underline hover:text-primary-500">
                    TikTok Accounts
                </a>
                page.
            </p>
        </x-filament::section>
    @else
        {{ $this->form }}
    @endif
</x-filament-panels::page>
