<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    @include('partials.head')
</head>

<body class="min-h-screen bg-background text-foreground">
    <flux:sidebar sticky stashable class="border-r border-sidebar-border bg-sidebar text-sidebar-foreground">
        <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

        <a href="{{ route('dashboard') }}"
            class="me-5 flex items-center space-x-2 rtl:space-x-reverse text-sidebar-foreground" wire:navigate>
            <x-app-logo />
        </a>

        <flux:input as="button" variant="filled" placeholder="Search..." icon="magnifying-glass" />
        
        <flux:navlist variant="outline" class="[--navlist-accent:var(--sidebar-accent)] [--navlist-accent-foreground:var(--sidebar-accent-foreground)]">

            <flux:navlist.item icon="home" :href="route('dashboard')" :current="request() -> routeIs('dashboard')"
                wire:navigate>
                {{ __('Dashboard') }}
            </flux:navlist.item>

            <flux:navlist.item icon="users" :href="route('entrepreneurs')"
                :current="request() -> routeIs('entrepreneurs')" wire:navigate>
                {{ __('Entrepreneurs') }}
            </flux:navlist.item>

            <flux:navlist.item icon="users" :href="route('entrepreneurship')"
                :current="request() -> routeIs('entrepreneurship')" wire:navigate>
                {{ __('Entrepreneurship') }}
            </flux:navlist.item>

            <flux:navlist.group expandable :heading="__('Events')"  icon="calendar-days" class="hidden lg:grid">
                <flux:navlist.item :href="route('events')" wire:navigate>{{ __('Manage Events') }}</flux:navlist.item>
                <flux:navlist.item :href="route('events.register')" wire:navigate>{{ __('Create Events') }}</flux:navlist.item>
            </flux:navlist.group>

            <flux:navlist.group expandable :heading="__('Stands')" icon="calendar-days" class="hidden lg:grid">
                <flux:navlist.item :href="route('stands.admin')" wire:navigate>{{ __('Manage stands') }}</flux:navlist.item>
                <flux:navlist.item :href="route('stands')" wire:navigate>{{ __('Add stands') }}</flux:navlist.item>
            </flux:navlist.group>
        </flux:navlist>

        <flux:spacer />

        <flux:navlist variant="outline"
            class="[--navlist-accent:var(--sidebar-accent)] [--navlist-accent-foreground:var(--sidebar-accent-foreground)]">
            <flux:navlist.item icon="folder-git-2" href="https://github.com/laravel/livewire-starter-kit"
                target="_blank" class="hover:bg-sidebar-accent/20 hover:text-sidebar-accent-foreground">
                {{ __('Repository') }}
            </flux:navlist.item>

            <flux:navlist.item icon="book-open-text" href="https://laravel.com/docs/starter-kits" target="_blank"
                class="hover:bg-sidebar-accent/20 hover:text-sidebar-accent-foreground">
                {{ __('Documentation') }}
            </flux:navlist.item>
        </flux:navlist>

        <!-- Desktop User Menu -->
        <flux:dropdown position="bottom" align="start">
            <flux:profile :name="auth() -> user() -> name" :initials="auth() -> user() -> initials()"
                icon-trailing="chevrons-up-down" class="hover:bg-sidebar-accent/20 text-sidebar-foreground" />

            <flux:menu class="w-[220px] bg-sidebar-accent border border-sidebar-border">
                <flux:menu.radio.group>
                    <div class="p-0 text-sm font-normal">
                        <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm hover:bg-sidebar-primary/20">
                            <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                <span
                                    class="flex h-full w-full items-center justify-center rounded-lg bg-sidebar-primary text-sidebar-primary-foreground">
                                    {{ auth()->user()->initials() }}
                                </span>
                            </span>

                            <div class="grid flex-1 text-start text-sm leading-tight">
                                <span
                                    class="truncate font-semibold text-sidebar-foreground">{{ auth()->user()->name }}</span>
                                <span
                                    class="truncate text-xs text-sidebar-accent-foreground">{{ auth()->user()->email }}</span>
                            </div>
                        </div>
                    </div>
                </flux:menu.radio.group>

                <flux:menu.separator class="border-sidebar-border" />

                <flux:menu.radio.group>
                    <flux:menu.item :href="route('settings.profile')" icon="cog" wire:navigate
                        class="hover:bg-sidebar-primary/20 hover:text-sidebar-primary-foreground data-[active]:bg-sidebar-primary data-[active]:text-sidebar-primary-foreground">
                        {{ __('Settings') }}
                    </flux:menu.item>
                </flux:menu.radio.group>

                <flux:menu.separator class="border-sidebar-border" />

                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle"
                        class="w-full hover:bg-sidebar-primary/20 hover:text-sidebar-primary-foreground">
                        {{ __('Log Out') }}
                    </flux:menu.item>
                </form>
            </flux:menu>
        </flux:dropdown>
    </flux:sidebar>

    <!-- Mobile User Menu -->
    <flux:header class="lg:hidden bg-sidebar-accent border-b border-sidebar-border">
        <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

        <flux:spacer />

        <flux:dropdown position="top" align="end">
            <flux:profile :initials="auth() -> user() -> initials()" icon-trailing="chevron-down"
                class="hover:bg-sidebar-accent/20 text-sidebar-foreground" />

            <flux:menu class="bg-sidebar-accent border border-sidebar-border">
                <flux:menu.radio.group>
                    <div class="p-0 text-sm font-normal">
                        <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm hover:bg-sidebar-primary/20">
                            <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                <span
                                    class="flex h-full w-full items-center justify-center rounded-lg bg-sidebar-primary text-sidebar-primary-foreground">
                                    {{ auth()->user()->initials() }}
                                </span>
                            </span>

                            <div class="grid flex-1 text-start text-sm leading-tight">
                                <span
                                    class="truncate font-semibold text-sidebar-foreground">{{ auth()->user()->name }}</span>
                                <span
                                    class="truncate text-xs text-sidebar-accent-foreground">{{ auth()->user()->email }}</span>
                            </div>
                        </div>
                    </div>
                </flux:menu.radio.group>

                <flux:menu.separator class="border-sidebar-border" />

                <flux:menu.radio.group>
                    <flux:menu.item :href="route('settings.profile')" icon="cog" wire:navigate
                        class="hover:bg-sidebar-primary/20 hover:text-sidebar-primary-foreground data-[active]:bg-sidebar-primary data-[active]:text-sidebar-primary-foreground">
                        {{ __('Settings') }}
                    </flux:menu.item>
                </flux:menu.radio.group>

                <flux:menu.separator class="border-sidebar-border" />

                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle"
                        class="w-full hover:bg-sidebar-primary/20 hover:text-sidebar-primary-foreground">
                        {{ __('Log Out') }}
                    </flux:menu.item>
                </form>
            </flux:menu>
        </flux:dropdown>
    </flux:header>

    {{ $slot }}

    @fluxScripts
</body>

</html>
