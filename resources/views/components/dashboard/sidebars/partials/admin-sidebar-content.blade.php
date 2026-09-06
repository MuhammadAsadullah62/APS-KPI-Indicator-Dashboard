@php
    $r = request()->route()?->getName();
@endphp
<div class="p-5 sm:p-8 flex-1 min-h-0">
    <div class="mb-8 sm:mb-12">
        <x-dashboard.brand />
    </div>
    <nav class="space-y-2">
        <x-dashboard.nav-link :href="route('kpidashboard')" :active="false">
            <svg class="w-5 h-5 text-slate-400 group-hover:text-aps-green" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
            KPI Dashboard
        </x-dashboard.nav-link>
        <x-dashboard.nav-link :href="route('adminpanel')" :active="$r === 'adminpanel'">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
            User Management
        </x-dashboard.nav-link>
        <x-dashboard.nav-link :href="route('academicreports')" :active="$r === 'academicreports'">
            <svg class="w-5 h-5 {{ $r === 'academicreports' ? 'text-aps-green' : 'text-slate-400 group-hover:text-aps-green' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            Academic Reports
        </x-dashboard.nav-link>
        <x-dashboard.nav-link :href="route('systemsettings')" :active="$r === 'systemsettings'">
            <svg class="w-5 h-5 text-slate-400 group-hover:text-aps-green" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
            System Settings
        </x-dashboard.nav-link>
    </nav>
</div>
<div class="p-5 sm:p-8 border-t border-slate-200 shrink-0">
    <div class="bg-aps-green p-5 sm:p-6 rounded-3xl text-white shadow-xl">
        <p class="text-[10px] font-bold text-emerald-300 uppercase tracking-widest mb-1">Current User</p>
        <p class="text-base sm:text-lg font-extrabold mb-4">Super Admin</p>
        <form method="POST" action="{{ route('logout') }}" class="block w-full">
            @csrf
            <button type="submit" class="block w-full py-3 bg-white text-aps-green rounded-xl text-sm font-bold shadow-lg hover:bg-slate-50 transition-colors text-center">Logout Session</button>
        </form>
    </div>
</div>
