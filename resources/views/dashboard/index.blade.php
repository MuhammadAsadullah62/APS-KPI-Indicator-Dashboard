@extends('layouts.app')

@section('title', 'APSACS Khanewal | Teacher KPI Dashboard')

@php
    $variant = $overviewVariant ?? null;
    $dashboardTitle = match ($variant) {
        'principal', 'section_head' => 'Leadership overview',
        'faculty' => 'My performance dashboard',
        default => 'Teacher KPI Dashboard',
    };
    $showStaffStatus = in_array($variant, ['faculty', 'section_head'], true);
@endphp

@section('header')
    <x-dashboard.page-header
        variant="profile"
        :title="$dashboardTitle"
        :show-staff-status="$showStaffStatus"
        :staff-status="$staffStatus ?? null"
    />
@endsection

@section('content')
    @if ($variant === 'principal')
        @include('dashboard.partials.overview-principal')
    @elseif ($showStaffStatus)
        @include('dashboard.partials.overview-staff')
    @else
        <div class="rounded-2xl border border-amber-200 bg-amber-50 px-6 py-4 text-sm font-semibold text-amber-900">
            Dashboard content is not available for your role.
        </div>
    @endif
@endsection
