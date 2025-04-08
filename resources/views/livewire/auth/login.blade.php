<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;
use Illuminate\Validation\ValidationException;

new #[Layout('components.layouts.auth')] class extends Component {
    #[Validate('required|email')]
    public string $email = '';

    #[Validate('required|string')]
    public string $password = '';

    public bool $remember = false;

    public function login(): void
    {
        $this->validate();

        if (! Auth::attempt([
            'email' => $this->email,
            'password' => $this->password,
            'estado' => 'activo', // <-- opcional: si usás campo "estado"
        ], $this->remember)) {
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        session()->regenerate();

        $this->redirectIntended(route('dashboard'));
    }
};
?>


<!-- ======================== BLADE ========================= -->
<div class="flex flex-col gap-6">
    <x-auth-header 
        :title="__('Log in to your account')" 
        :description="__('Enter your email and password below to log in')" 
    />

    <x-auth-session-status class="text-center" :status="session('status')" />

    <form wire:submit.prevent="login" class="flex flex-col gap-6">
        <!-- Email -->
        <flux:input
            wire:model="email"
            :label="__('Email address')"
            type="email"
            required
            autofocus
            autocomplete="email"
            placeholder="email@example.com"
        />
        @error('email')
            <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
        @enderror

        <!-- Password -->
        <div class="relative">
            <flux:input
                wire:model="password"
                :label="__('Password')"
                type="password"
                required
                autocomplete="current-password"
                placeholder="******"
            />
            @if (Route::has('password.request'))
                <flux:link class="absolute end-0 top-0 text-sm" :href="route('password.request')" wire:navigate>
                    {{ __('Forgot your password?') }}
                </flux:link>
            @endif
        </div>
        @error('password')
            <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
        @enderror

        <!-- Remember me -->
        <flux:checkbox wire:model="remember" :label="__('Remember me')" />

        <!-- Login button -->
        <div class="flex items-center justify-end">
            <flux:button variant="primary" type="submit" class="w-full">
                {{ __('Log in') }}
            </flux:button>
        </div>
    </form>

    @if (Route::has('register'))
        <div class="text-center text-sm text-zinc-600 dark:text-zinc-400">
            {{ __("Don't have an account?") }}
            <flux:link :href="route('register')" wire:navigate>{{ __('Sign up') }}</flux:link>
        </div>
    @endif
</div>
