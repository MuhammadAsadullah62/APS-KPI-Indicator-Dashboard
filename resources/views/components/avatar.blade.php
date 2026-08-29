@props([
    'user' => null,
    'src' => null,
    'initials' => null,
    // Tailwind sizing/shape classes shared by the image and the initials fallback
    // (e.g. "h-12 w-12 rounded-2xl").
    'box' => 'h-10 w-10 rounded-xl',
    // Extra classes for the <img> only (borders, shadows, shrink-0 …).
    'imgClass' => '',
    // Extra classes for the initials fallback only (background, text colour/size, shadow …).
    'fallbackClass' => 'bg-emerald-500 text-white text-sm',
    // Intrinsic pixel size for width/height attrs — reduces layout shift.
    'px' => 40,
])

@php
    $resolvedUrl = $src ?? $user?->avatarUrl();
    $resolvedInitials = $initials ?? $user?->initials() ?? '?';
@endphp

@if ($resolvedUrl)
    {{-- If the file 404s / 403s / is corrupt, hide the <img> and reveal the initials
         sibling instead of showing a broken-image icon. --}}
    <img src="{{ $resolvedUrl }}" alt="" loading="lazy" decoding="async"
        width="{{ $px }}" height="{{ $px }}"
        class="{{ $box }} object-cover {{ $imgClass }}"
        onerror="this.onerror=null;this.style.display='none';this.nextElementSibling.style.display='flex';">
    <span class="{{ $box }} {{ $fallbackClass }} items-center justify-center font-black" style="display:none">{{ $resolvedInitials }}</span>
@else
    <span class="{{ $box }} {{ $fallbackClass }} flex items-center justify-center font-black">{{ $resolvedInitials }}</span>
@endif
