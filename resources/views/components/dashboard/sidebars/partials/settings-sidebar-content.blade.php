@php
    $r = request()->route()?->getName();
@endphp
<div class="p-5 sm:p-8 flex flex-col flex-1 min-h-0">
    <div class="flex flex-col flex-1 min-h-0">
        <div class="mb-6 sm:mb-10">
            <x-dashboard.brand />
        </div>

        @auth
            @unless(auth()->user()->isFaculty())
                @if(auth()->user()->canViewSystemSettingsOverview())
                    <nav class="space-y-1 mb-6 sm:mb-8">
                        <x-dashboard.nav-link :href="route('systemsettings')" :active="$r === 'systemsettings'">
                            <svg class="w-5 h-5 {{ $r === 'systemsettings' ? 'text-aps-green' : 'text-slate-400' }} shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path></svg>
                            System Overview
                        </x-dashboard.nav-link>
                    </nav>
                @endif

                <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-4 px-4">User Management</p>
                <nav class="space-y-2">
                    @can('sectionheads.manage')
                        <x-dashboard.nav-link :href="route('sechead')" :active="$r === 'sechead'">
                            <svg class="w-5 h-5 {{ $r === 'sechead' ? 'text-aps-green' : 'text-slate-400 group-hover:text-aps-green' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            SecHead Management
                        </x-dashboard.nav-link>
                    @endcan

                    @can('faculty.view')
                        <x-dashboard.nav-link :href="route('teachermanagement')" :active="$r === 'teachermanagement'">
                            <svg class="w-5 h-5 {{ $r === 'teachermanagement' ? 'text-aps-green' : 'text-slate-400 group-hover:text-aps-green' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                            Faculty Management
                        </x-dashboard.nav-link>
                    @endcan

                    @if(auth()->user()->canAccessObservations())
                        <x-dashboard.nav-link :href="route('observations')" :active="$r === 'observations'">
                            <svg class="w-5 h-5 {{ $r === 'observations' ? 'text-aps-green' : 'text-slate-400 group-hover:text-aps-green' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                            Observations
                        </x-dashboard.nav-link>
                    @endif
                </nav>
            @endunless
        @endauth
    </div>

    <div class="pt-6 sm:pt-10 border-t border-slate-100 shrink-0">
        <x-dashboard.nav-link :href="route('kpidashboard')" :active="$r === 'kpidashboard'">
            <svg class="w-5 h-5 {{ $r === 'kpidashboard' ? 'text-aps-green' : 'text-slate-400 group-hover:text-aps-green' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
            KPI Dashboard
        </x-dashboard.nav-link>
    </div>
</div>
