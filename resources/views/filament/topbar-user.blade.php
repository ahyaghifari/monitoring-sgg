{{-- Pengganti dropdown profil: badge peran, sakelar tema, dan tombol logout --}}
@auth
    @php($role = auth()->user()->role)
    <div style="display: flex; align-items: center; gap: 0.75rem;">
        <x-filament::badge :color="$role === \App\Enums\UserRole::SUPER_ADMIN ? 'success' : 'gray'">
            {{ $role->getLabel() }}
        </x-filament::badge>

        @if (filament()->hasDarkMode() && ! filament()->hasDarkModeForced())
            <x-filament-panels::theme-switcher />
        @endif

        {{-- Logout langsung (POST + CSRF) --}}
        <form method="POST" action="{{ filament()->getLogoutUrl() }}" style="margin: 0;">
            @csrf
            <x-filament::icon-button
                type="submit"
                icon="heroicon-o-arrow-right-start-on-rectangle"
                color="danger"
                label="Keluar"
                tooltip="Keluar"
            />
        </form>
    </div>
@endauth
