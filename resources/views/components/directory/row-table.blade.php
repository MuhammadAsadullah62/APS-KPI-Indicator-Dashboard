@props([
    'row',
    'wingLabel' => null,
    'deptChips' => [],
    'manage' => false,
    'handlerPrefix',
    'viewPayload' => [],
    'editPayload' => [],
    'destroyPayload' => [],
    'readOnlyLabel' => null,
    'avatarFallbackClass' => 'bg-aps-green text-white text-lg shadow-sm',
])

@php
    $editOnclick = $handlerPrefix.'Edit('.\Illuminate\Support\Js::from($editPayload).')';
    $deleteOnclick = $handlerPrefix.'Delete('.\Illuminate\Support\Js::from($destroyPayload).')';
@endphp

<tr class="group transition-colors hover:bg-emerald-50/30">
    <td class="align-middle px-3 py-4 sm:px-5 sm:py-5 md:px-10 md:py-6">
        <div class="flex min-w-0 items-center gap-4">
            <x-avatar :user="$row" box="h-12 w-12 rounded-2xl" :px="48"
                img-class="shrink-0 border border-slate-100 shadow-sm"
                :fallback-class="'shrink-0 '.$avatarFallbackClass" />
            <div>
                <p class="font-black leading-none text-slate-800">{{ $row->name }}</p>
                <p class="mt-2 text-[10px] font-bold uppercase text-slate-400">{{ $row->employee_id }}</p>
            </div>
        </div>
    </td>
    <td class="align-middle px-3 py-4 sm:px-5 sm:py-5 md:px-10 md:py-6">
        @if($wingLabel)
            <span class="inline-flex items-center rounded-lg bg-slate-100 px-4 py-1.5 text-[10px] font-black uppercase tracking-widest text-slate-600">{{ $wingLabel }}</span>
        @else
            <span class="text-xs text-slate-400">—</span>
        @endif
    </td>
    <td class="align-middle px-3 py-4 sm:px-5 sm:py-5 md:px-10 md:py-6">
        @if(count($deptChips))
            <div class="flex max-w-md flex-wrap items-center gap-2">
                @foreach ($deptChips as $lbl)
                    <span class="inline-flex items-center rounded-lg border border-emerald-100/80 bg-emerald-50 px-3 py-1.5 text-[10px] font-black text-aps-green">{{ $lbl }}</span>
                @endforeach
            </div>
        @else
            <span class="text-xs text-slate-400">—</span>
        @endif
    </td>
    <td class="align-middle px-3 py-4 text-right sm:px-5 sm:py-5 md:px-10 md:py-6">
        @if($manage)
            <div class="flex items-center justify-end gap-3 opacity-60 transition-opacity group-hover:opacity-100">
                <x-directory.view-button variant="icon" :handler="$handlerPrefix.'View'" :payload="$viewPayload" />
                <button type="button" onclick="{{ $editOnclick }}" class="rounded-lg border border-transparent p-2 text-slate-400 shadow-sm transition-all hover:border-slate-100 hover:bg-white hover:text-aps-green"><svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></button>
                <button type="button" onclick="{{ $deleteOnclick }}" class="rounded-lg border border-transparent p-2 text-slate-400 shadow-sm transition-all hover:border-slate-100 hover:bg-white hover:text-red-500"><svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
            </div>
        @elseif($readOnlyLabel)
            <span class="text-[10px] font-bold uppercase text-slate-300">{{ $readOnlyLabel }}</span>
        @else
            <div class="flex items-center justify-end gap-3 opacity-60 transition-opacity group-hover:opacity-100">
                <x-directory.view-button variant="icon" :handler="$handlerPrefix.'View'" :payload="$viewPayload" />
            </div>
        @endif
    </td>
</tr>
