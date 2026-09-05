@props([
    'showName' => true,
])
@auth
    @php($u = auth()->user())
    <div class="relative shrink-0">
        <button
            type="button"
            id="profileMenuButton"
            class="flex items-center gap-3 cursor-pointer group text-left rounded-xl focus:outline-none focus-visible:ring-2 focus-visible:ring-aps-green/40"
            aria-expanded="false"
            aria-haspopup="true"
        >
            @if($showName)
                <div class="text-right leading-none hidden sm:block">
                    <p class="text-sm font-bold text-slate-900 truncate max-w-[12rem]">{{ $u->name }}</p>
                    <p class="text-[10px] font-bold text-slate-400 uppercase mt-1">{{ $u->roleLabel() }}</p>
                </div>
            @endif
            <x-avatar :user="$u" box="w-10 h-10 rounded-xl" :px="40"
                img-class="shadow-md border-2 border-white group-hover:border-aps-green transition-all"
                fallback-class="shadow-md border-2 border-white bg-slate-100 text-slate-700 text-xs group-hover:border-aps-green transition-all" />
        </button>
        <div
            id="profileDropdown"
            class="hidden absolute right-0 mt-3 w-64 max-w-[min(18rem,calc(100vw-1.5rem))] bg-white border border-slate-200 rounded-[1.5rem] shadow-2xl py-2 z-50 transform origin-top-right transition-all"
            role="menu"
        >
            <div class="px-6 py-4 border-b border-slate-100">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Signed in as</p>
                <p class="text-sm font-black text-slate-900 leading-none truncate">{{ $u->name }}</p>
                <p class="text-[11px] text-emerald-600 font-bold mt-1 truncate">{{ $u->email }}</p>
            </div>
            <div class="p-2 space-y-1">
                @if($u->canAccessSystemSettings())
                    <a href="{{ route('systemsettings') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm font-semibold text-slate-600 hover:bg-emerald-50 hover:text-aps-green rounded-xl transition-all group" role="menuitem">
                        <svg class="w-4 h-4 text-slate-400 group-hover:text-aps-green shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path></svg>
                        System settings
                    </a>
                @endif
            </div>
            <div class="h-px bg-slate-100 my-2"></div>
            <div class="p-2">
                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <button type="submit" class="w-full flex items-center gap-3 px-4 py-2.5 text-sm font-bold text-red-500 hover:bg-red-50 rounded-xl transition-all text-left" role="menuitem">
                        <svg class="w-4 h-4 text-red-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                        Sign out
                    </button>
                </form>
            </div>
        </div>
    </div>
    @once
        @push('scripts')
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    var profileBtn = document.getElementById('profileMenuButton');
                    var dropdown = document.getElementById('profileDropdown');
                    if (!profileBtn || !dropdown) return;
                    profileBtn.addEventListener('click', function (e) {
                        e.stopPropagation();
                        dropdown.classList.toggle('hidden');
                    });
                    document.addEventListener('click', function (e) {
                        if (!dropdown.contains(e.target) && !profileBtn.contains(e.target)) {
                            dropdown.classList.add('hidden');
                        }
                    });
                });
            </script>
        @endpush
    @endonce
@endauth
