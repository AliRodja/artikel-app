<header class="border-b border-gray-200 bg-white">
    <nav class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-3 px-4 py-3">
        <a href="{{ route('home') }}" class="text-lg font-bold text-gray-900">
            {{ config('app.name') }}
        </a>

        <div class="flex flex-wrap items-center gap-1">
            <a href="{{ route('home') }}"
               @class(['nav-link', 'nav-link-active' => request()->routeIs('home', 'public.*')])>Artikel</a>

            @auth
                <a href="{{ route('dashboard') }}"
                   @class(['nav-link', 'nav-link-active' => request()->routeIs('dashboard')])>Dashboard</a>
                <a href="{{ route('articles.index') }}"
                   @class(['nav-link', 'nav-link-active' => request()->routeIs('articles.*') && ! request()->routeIs('articles.report')])>Artikel Saya</a>
                <a href="{{ route('articles.report') }}"
                   @class(['nav-link', 'nav-link-active' => request()->routeIs('articles.report')])>Laporan Artikel</a>
            @endauth
        </div>

        <div class="flex items-center gap-3">
            @guest
                <a href="{{ route('login') }}" class="btn btn-secondary btn-sm">Masuk</a>
                <a href="{{ route('register') }}" class="btn btn-primary btn-sm">Daftar</a>
            @else
                <div class="hidden text-right sm:block">
                    <p class="text-sm font-medium text-gray-900">{{ auth()->user()->name }}</p>
                    <p class="text-xs text-gray-500">{{ auth()->user()->email }}</p>
                </div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-secondary btn-sm">Keluar</button>
                </form>
            @endguest
        </div>
    </nav>
</header>