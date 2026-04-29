<nav x-data="{ open: false }" class="bg-white border-b border-gray-200" role="navigation" aria-label="Navegacao principal">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <a href="{{ route('dashboard') }}" class="shrink-0 flex items-center gap-2 px-2" aria-label="AlfaFut - Pagina inicial">
                    <span class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-primary text-on-primary font-bold" aria-hidden="true">A</span>
                    <span class="font-semibold text-lg">AlfaFut</span>
                </a>

                <div class="hidden space-x-1 sm:ms-8 sm:flex sm:items-center" role="menubar">
                    <a href="{{ route('dashboard') }}"
                       role="menuitem"
                       class="px-4 py-2 rounded-full text-sm font-medium {{ request()->routeIs('dashboard') ? 'bg-primary text-on-primary' : 'text-gray-700 hover:bg-gray-100' }}"
                       @if(request()->routeIs('dashboard')) aria-current="page" @endif>
                        Dashboard
                    </a>
                    <a href="{{ route('patotas.index') }}"
                       role="menuitem"
                       class="px-4 py-2 rounded-full text-sm font-medium {{ request()->routeIs('patotas.*') ? 'bg-primary text-on-primary' : 'text-gray-700 hover:bg-gray-100' }}"
                       @if(request()->routeIs('patotas.*')) aria-current="page" @endif>
                        Patotas
                    </a>
                    <a href="{{ route('partidas.index') }}"
                       role="menuitem"
                       class="px-4 py-2 rounded-full text-sm font-medium {{ request()->routeIs('partidas.*') ? 'bg-primary text-on-primary' : 'text-gray-700 hover:bg-gray-100' }}"
                       @if(request()->routeIs('partidas.*')) aria-current="page" @endif>
                        Partidas
                    </a>
                    <a href="{{ route('acessibilidade.edit') }}"
                       role="menuitem"
                       class="px-4 py-2 rounded-full text-sm font-medium {{ request()->routeIs('acessibilidade.*') ? 'bg-primary text-on-primary' : 'text-gray-700 hover:bg-gray-100' }}"
                       @if(request()->routeIs('acessibilidade.*')) aria-current="page" @endif>
                        Acessibilidade
                    </a>
                </div>
            </div>

            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button type="button" class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-medium text-gray-700 hover:bg-gray-100" aria-haspopup="true">
                            <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-secondary text-on-secondary font-bold" aria-hidden="true">
                                {{ strtoupper(substr(Auth::user()->apelido ?? Auth::user()->name, 0, 1)) }}
                            </span>
                            <span>{{ Auth::user()->apelido ?? Auth::user()->name }}</span>
                            <svg class="h-4 w-4" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">Meu Perfil</x-dropdown-link>
                        <x-dropdown-link :href="route('acessibilidade.edit')">Acessibilidade</x-dropdown-link>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                                Sair
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open"
                        type="button"
                        class="inline-flex items-center justify-center p-2 rounded-md text-gray-700"
                        :aria-expanded="open ? 'true' : 'false'"
                        aria-controls="menu-mobile"
                        aria-label="Abrir menu de navegacao">
                    <svg class="h-6 w-6" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div id="menu-mobile" :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden border-t">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">Dashboard</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('patotas.index')" :active="request()->routeIs('patotas.*')">Patotas</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('partidas.index')" :active="request()->routeIs('partidas.*')">Partidas</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('acessibilidade.edit')" :active="request()->routeIs('acessibilidade.*')">Acessibilidade</x-responsive-nav-link>
        </div>
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-900">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-700">{{ Auth::user()->email }}</div>
            </div>
            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">Meu Perfil</x-responsive-nav-link>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">Sair</x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
