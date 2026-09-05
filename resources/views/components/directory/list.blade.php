@props([
    'heading',
    'rows',
    'empty' => 'Nothing here yet.',
    'columns' => [],
    'rowView',
    'cardView',
    'rowData' => [],
])

<div class="min-w-0 overflow-hidden rounded-2xl border border-slate-200 bg-white font-semibold shadow-sm sm:rounded-[2.5rem]">
    <div class="border-b border-slate-100 bg-slate-50/50 p-4 sm:p-6 md:p-8">
        <h3 class="text-lg font-black uppercase tracking-tight text-slate-800 sm:text-xl">{{ $heading }}</h3>
    </div>
    @if($rows->isEmpty())
        <p class="px-4 py-10 text-center font-semibold text-slate-400 sm:px-6 sm:py-12">{{ $empty }}</p>
    @else
        <div class="space-y-3 p-4 md:hidden">
            @foreach ($rows as $row)
                @include($cardView, array_merge(['row' => $row], $rowData))
            @endforeach
        </div>
        <div class="hidden overflow-x-auto md:block">
            <table class="w-full border-collapse text-left">
                <thead>
                    <tr class="bg-slate-50/30 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">
                        @foreach ($columns as $col)
                            <th class="px-3 py-3 sm:px-4 sm:py-4 md:px-8 md:py-5 {{ $loop->last ? 'text-right' : '' }}">{{ $col }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($rows as $row)
                        @include($rowView, array_merge(['row' => $row], $rowData))
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
