@extends('layouts.app')

@section('title', 'APSACS Khanewal | Academic Reports')

@section('content-padding', 'p-4 space-y-4 sm:p-6 sm:space-y-6')

@section('header')
    <x-dashboard.page-header variant="session" title="Academic Reports" subtitle="Institutional reporting & exports" chip="Fall Semester 2023" />
@endsection

@section('content')
    <div class="relative isolate min-h-[min(56vh,520px)] sm:min-h-[min(70vh,720px)] overflow-hidden rounded-2xl sm:rounded-[1.75rem] border border-slate-200/80 bg-slate-50/50">
        <div class="pointer-events-none select-none blur-sm opacity-[0.38] saturate-[0.65]" aria-hidden="true">
            @include('dashboard.partials.academic-reports-content')
        </div>
        <div class="absolute inset-0 z-10 flex items-center justify-center bg-slate-100/35 px-4 py-16 backdrop-blur-sm">
            <div class="max-w-xl rounded-[2rem] border border-amber-200/90 bg-white/90 px-8 py-12 text-center shadow-2xl shadow-slate-900/10 backdrop-blur-md md:px-14 md:py-14">
                <p class="text-2xl font-black tracking-tight text-slate-900 md:text-4xl">Under Process</p>
                <p class="mt-5 text-sm font-semibold leading-relaxed text-slate-600 md:text-base">Materials Required and More Discussion Needed</p>
            </div>
        </div>
    </div>
@endsection
