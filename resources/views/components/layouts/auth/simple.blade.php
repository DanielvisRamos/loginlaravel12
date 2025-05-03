<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="layout-auth">
        <div class="auth-container">
            <div class="auth-content">
                <a href="{{ route('home') }}" class="logo-container" wire:navigate>
                    <span class="logo-icon">
                        <x-app-logo-icon class="logo-svg" />
                    </span>
                    <span class="sr-only">{{ config('app.name', 'Laravel') }}</span>
                </a>
                <div class="auth-slot">
                    {{ $slot }}
                </div>
            </div>
        </div>
        @fluxScripts
    </body>
</html>
