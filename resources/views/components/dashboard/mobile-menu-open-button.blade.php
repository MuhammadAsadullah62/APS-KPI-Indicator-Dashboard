@props(['label' => 'Open navigation menu'])
@auth
    <button
        type="button"
        data-mobile-nav-open
        {{ $attributes->merge(['class' => 'inline-flex lg:hidden items-center justify-center p-2 -ml-1 mr-1 rounded-xl text-slate-600 hover:bg-slate-100 hover:text-slate-900 active:bg-slate-200/80 focus:outline-none focus-visible:ring-2 focus-visible:ring-aps-green/50 shrink-0']) }}
        aria-label="{{ $label }}"
    >
        <span class="sr-only">{{ $label }}</span>
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
    </button>
@endauth
