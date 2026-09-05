@props([
    'id',
    'title' => 'Profile',
    'avatarId',
    'initialsId',
    'accent' => 'bg-aps-green',
    'closeLabel' => 'Close',
    'fields' => [],
])

<div id="{{ $id }}" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[100] items-center justify-center p-6">
    <div class="bg-white w-full max-w-3xl rounded-[3rem] shadow-2xl overflow-hidden border border-slate-200">
        <div class="p-10 border-b border-slate-100 flex justify-between bg-slate-50/50">
            <h3 class="text-3xl font-black text-slate-800 tracking-tight uppercase leading-none">{{ $title }}</h3>
            <button type="button" onclick="toggleModal('{{ $id }}')" class="text-slate-400"><svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
        </div>
        <div class="p-12 space-y-10">
            <div class="flex flex-col sm:flex-row sm:items-start gap-10">
                <div class="relative w-32 h-32 shrink-0 mx-auto sm:mx-0">
                    <img id="{{ $avatarId }}" src="" alt="" class="hidden w-32 h-32 rounded-[2.5rem] object-cover shadow-xl border border-slate-100"
                        onerror="this.onerror=null;this.classList.add('hidden');document.getElementById('{{ $initialsId }}').classList.remove('hidden');">
                    <div id="{{ $initialsId }}" class="w-32 h-32 {{ $accent }} rounded-[2.5rem] flex items-center justify-center text-white text-5xl font-black shadow-xl">?</div>
                </div>
                <div class="grid w-full min-w-0 sm:flex-1 grid-cols-1 sm:grid-cols-2 gap-x-10 gap-y-5">
                    @foreach ($fields as $field)
                        <div class="min-w-0 {{ ($field['full'] ?? false) ? 'sm:col-span-2' : (($loop->index % 2 === 0) ? 'sm:pr-1' : 'sm:pl-1') }}">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ $field['label'] }}</p>
                            @if ($field['email'] ?? false)
                                <p id="{{ $field['id'] }}" class="text-sm sm:text-base font-bold text-aps-green mt-1 break-words [overflow-wrap:anywhere] leading-relaxed"></p>
                            @elseif ($field['full'] ?? false)
                                <p id="{{ $field['id'] }}" class="text-sm font-bold text-slate-700 leading-relaxed mt-2 break-words [overflow-wrap:anywhere]"></p>
                            @else
                                <p id="{{ $field['id'] }}" class="text-base sm:text-lg font-black text-slate-800 mt-1 break-words [overflow-wrap:anywhere]"></p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="mt-8 pt-8 border-t border-slate-100 flex justify-end">
                <button type="button" onclick="toggleModal('{{ $id }}')" class="bg-slate-900 text-white px-12 py-4 rounded-2xl font-black text-sm uppercase tracking-widest shadow-xl">{{ $closeLabel }}</button>
            </div>
        </div>
    </div>
</div>
