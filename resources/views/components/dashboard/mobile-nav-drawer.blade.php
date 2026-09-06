@php
    $name = request()->route()?->getName();
    $kpi = ['kpidashboard', 'quantitativeobservations', 'qualitativeobservations', 'academicreports'];
    $settingsRoutes = ['sechead', 'teachermanagement', 'systemsettings', 'observations'];
@endphp
@auth
    <div
        id="mobileNavRoot"
        class="lg:hidden fixed inset-0 z-[200] hidden"
        role="dialog"
        aria-modal="true"
        aria-labelledby="mobileNavTitle"
    >
        <div
            id="mobileNavBackdrop"
            class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity"
            aria-hidden="true"
        ></div>
        <div
            class="absolute left-0 top-0 bottom-0 flex w-[min(100vw,20.5rem)] max-w-full flex-col bg-white shadow-2xl"
        >
            <div class="flex items-center justify-between gap-3 border-b border-slate-200 px-4 py-3 shrink-0">
                <p id="mobileNavTitle" class="text-sm font-black uppercase tracking-tight text-slate-400">Menu</p>
                <button
                    type="button"
                    id="mobileNavClose"
                    class="flex h-10 w-10 items-center justify-center rounded-xl text-slate-500 hover:bg-slate-100 hover:text-slate-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-aps-green/50"
                    aria-label="Close navigation"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="flex-1 min-h-0 overflow-y-auto overscroll-y-contain">
                @if (in_array($name, $kpi, true))
                    @include('components.dashboard.sidebars.partials.kpi-sidebar-content')
                @elseif ($name === 'adminpanel')
                    @include('components.dashboard.sidebars.partials.admin-sidebar-content')
                @elseif (in_array($name, $settingsRoutes, true))
                    @include('components.dashboard.sidebars.partials.settings-sidebar-content')
                @else
                    @include('components.dashboard.sidebars.partials.kpi-sidebar-content')
                @endif
            </div>
        </div>
    </div>
    @once
        @push('scripts')
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    var root = document.getElementById('mobileNavRoot');
                    if (!root) return;
                    var backdrop = document.getElementById('mobileNavBackdrop');
                    var closeBtn = document.getElementById('mobileNavClose');
                    var openers = document.querySelectorAll('[data-mobile-nav-open]');
                    function openNav() {
                        root.classList.remove('hidden');
                        document.body.classList.add('overflow-hidden');
                    }
                    function closeNav() {
                        root.classList.add('hidden');
                        document.body.classList.remove('overflow-hidden');
                    }
                    openers.forEach(function (el) {
                        el.addEventListener('click', function (e) {
                            e.preventDefault();
                            e.stopPropagation();
                            openNav();
                        });
                    });
                    if (closeBtn) {
                        closeBtn.addEventListener('click', function () {
                            closeNav();
                        });
                    }
                    if (backdrop) {
                        backdrop.addEventListener('click', function () {
                            closeNav();
                        });
                    }
                    root.querySelectorAll('a[href]').forEach(function (a) {
                        a.addEventListener('click', function () {
                            closeNav();
                        });
                    });
                    document.addEventListener('keydown', function (e) {
                        if (e.key === 'Escape' && !root.classList.contains('hidden')) {
                            closeNav();
                        }
                    });
                });
            </script>
        @endpush
    @endonce
@endauth
