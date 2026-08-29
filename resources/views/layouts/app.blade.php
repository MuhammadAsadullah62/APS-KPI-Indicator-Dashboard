<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('partials.head', ['title' => trim($__env->yieldContent('title')) ?: 'APSACS Khanewal'])
</head>
<body class="@yield('body-class', 'flex h-screen min-h-0 w-full overflow-hidden text-slate-900')" style="background-color: #f1f5f9;">
    <x-maintenance-banner />
    <x-dashboard.sidebar />
    <x-dashboard.mobile-nav-drawer />
    <main class="flex min-h-0 min-w-0 flex-1 flex-col overflow-hidden @yield('main-class', 'bg-slate-100')">
        @yield('header')
        <div class="flex min-h-0 flex-1 flex-col overflow-y-auto @yield('scroll-class')">
            <div class="@yield('content-padding', 'p-4 space-y-6 sm:p-6 sm:space-y-8 lg:p-8') no-scrollbar flex-1 min-w-0">
                @yield('content')
            </div>
            @hasSection('hide-footer')
            @else
                <x-dashboard.footer />
            @endif
        </div>
    </main>
    @stack('modals')
    @stack('scripts')
</body>
</html>
