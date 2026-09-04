<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('partials.head', ['title' => trim($__env->yieldContent('title')) ?: 'APSACS Khanewal'])
    <style>
        .focus-ring:focus { outline: none; ring: 4px; --tw-ring-color: rgba(6, 78, 59, 0.1); border-color: #064e3b; }
    </style>
    @stack('styles')
</head>
<body class="bg-slate-100 min-h-screen flex items-center justify-center p-6">
    <x-maintenance-banner />
    @yield('content')
    @stack('scripts')
</body>
</html>
