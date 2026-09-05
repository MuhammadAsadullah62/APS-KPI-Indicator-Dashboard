    <div id="observationModal" class="hidden fixed inset-0 z-[300] flex flex-col items-center justify-center p-0 sm:p-4 md:p-6 bg-slate-900/60 backdrop-blur-sm min-h-0">
        <form id="observationStoreForm" method="post" action="{{ route('observations.store') }}" class="bg-white w-full max-w-6xl min-h-0 flex flex-col border-0 sm:border border-slate-200 shadow-2xl overflow-hidden h-[100dvh] max-h-[100dvh] sm:h-[min(92vh,900px)] sm:max-h-[min(92vh,900px)] rounded-none sm:rounded-[2.5rem] lg:rounded-[3.5rem]" data-store-url="{{ route('observations.store') }}" data-update-url-prefix="{{ url('/observations') }}">
            @csrf
            <span id="observation-method-spoof"></span>
            <input type="hidden" name="observee_id" id="store_observee_id" value="">
            <input type="hidden" name="aggregate_percent" id="store_aggregate_percent" value="0">
            <input type="hidden" name="sessions_payload" id="store_sessions_payload" value="[]">
            <div class="relative p-4 sm:p-6 lg:p-10 border-b border-slate-100 flex flex-col gap-4 sm:flex-row sm:justify-between sm:items-start bg-slate-50/50 shrink-0 pr-14 sm:pr-4">
                <div class="min-w-0 flex-1">
                    <p id="observation_modal_mode" class="text-[10px] font-black uppercase tracking-[0.35em] text-slate-400 mb-1 sm:mb-2">New observation</p>
                    <h3 class="text-xl sm:text-2xl md:text-3xl lg:text-4xl font-black text-slate-800 tracking-tight uppercase leading-tight">Comprehensive Audit</h3>
                    <p class="text-sm sm:text-base md:text-lg text-slate-500 font-medium mt-2 sm:mt-3 leading-snug [overflow-wrap:anywhere]">Reviewing: <span id="modalFacultyName" class="text-aps-green font-black">Name</span></p>
                    <div id="observeeProfileContext" class="mt-3 sm:mt-5 max-w-2xl space-y-2 rounded-2xl sm:rounded-[1.5rem] border border-slate-200 bg-white px-4 py-3 sm:px-6 sm:py-4 text-left shadow-sm">
                        <div class="flex flex-wrap items-baseline gap-x-2 gap-y-1">
                            <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">Wing</span>
                            <span id="observeeContextWing" class="text-sm font-bold text-slate-800 [overflow-wrap:anywhere]">—</span>
                        </div>
                        <div class="flex flex-wrap items-baseline gap-x-2 gap-y-1">
                            <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">Department(s)</span>
                            <span id="observeeContextDepartment" class="text-sm font-bold text-slate-800 [overflow-wrap:anywhere]">—</span>
                        </div>
                    </div>
                </div>
                <button type="button" onclick="toggleModal('observationModal')" class="absolute right-2 top-2 sm:right-3 sm:top-3 sm:static p-2 sm:p-3 shrink-0 rounded-full text-slate-400 hover:bg-slate-200 transition-colors" aria-label="Close audit">
                    <svg class="w-7 h-7 sm:w-9 sm:h-9" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <div class="flex-1 min-h-0 overflow-y-auto overscroll-contain p-4 sm:p-6 lg:p-10 space-y-8 sm:space-y-10 lg:space-y-12 bg-slate-50/30">
                @auth
                    @if(auth()->user()->canAccessObservations())
                        <div id="observationContextCard" class="bg-white rounded-2xl sm:rounded-3xl border border-slate-200 p-4 sm:p-6 md:p-8 lg:p-10 shadow-sm min-w-0">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.35em] mb-3 sm:mb-6">
                                Observation context <span id="observationContextRequiredMark" class="text-red-500">*</span>
                            </p>
                            <p class="text-xs sm:text-sm text-slate-500 font-semibold mb-4 sm:mb-8 leading-relaxed">The <strong>reviewing member’s</strong> profile is shown in the header. Select which <strong>wing and department</strong> this quant/qual review applies to (required for <strong>section heads</strong> and <strong>teachers</strong>). Department options follow SectionHead Hub rules for observers with assignments; if none, all departments appear.</p>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6 md:gap-8">
                                <div>
                                    <label for="observation_context_wing" class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Wing</label>
                                    <select name="observation_wing" id="observation_context_wing" class="w-full rounded-[2rem] border border-slate-200 bg-slate-50/80 px-6 py-4 text-sm font-bold text-slate-800 outline-none focus:border-aps-green focus:ring-2 focus:ring-emerald-500/20">
                                        <option value="">Select wing</option>
                                        @foreach (\App\Enums\Wing::cases() as $w)
                                            <option value="{{ $w->value }}">{{ $w->label() }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label for="observation_context_department" class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Department</label>
                                    <select name="observation_department" id="observation_context_department" class="w-full rounded-[2rem] border border-slate-200 bg-slate-50/80 px-6 py-4 text-sm font-bold text-slate-800 outline-none focus:border-aps-green focus:ring-2 focus:ring-emerald-500/20">
                                        <option value="">Select department</option>
                                        @foreach ($observationDepartments as $d)
                                            <option value="{{ $d->value }}">{{ $d->label() }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" name="observation_wing" id="observation_wing_null" value="" disabled>
                        <input type="hidden" name="observation_department" id="observation_department_null" value="" disabled>
                    @else
                        <input type="hidden" name="observation_wing" id="observation_context_wing" value="">
                        <input type="hidden" name="observation_department" id="observation_context_department" value="">
                    @endif
                @endauth

                <div class="bg-slate-900 rounded-2xl sm:rounded-3xl p-4 sm:p-6 md:p-8 text-white shadow-2xl flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 sm:gap-6 border-b-4 sm:border-b-8 border-emerald-500 min-w-0">
                    <div class="min-w-0 text-center sm:text-left">
                        <h4 class="text-xs sm:text-sm font-black text-emerald-400 uppercase tracking-[0.2em] sm:tracking-[0.3em] mb-1 sm:mb-2">Final Summative Result</h4>
                        <p class="text-xs sm:text-sm md:text-base text-slate-400 italic leading-snug">Cumulative weighted average across all sessions</p>
                    </div>
                    <div class="flex flex-row items-center justify-center gap-4 sm:gap-8 md:gap-10 shrink-0 w-full sm:w-auto">
                        <div class="text-center">
                            <p class="text-[10px] sm:text-xs font-black text-slate-500 uppercase tracking-widest mb-1 sm:mb-2">Sessions</p>
                            <p id="auditCount" class="text-3xl sm:text-4xl md:text-5xl font-black tabular-nums">0</p>
                        </div>
                        <div class="hidden sm:block h-12 md:h-16 w-px bg-white/10 self-center" aria-hidden="true"></div>
                        <div class="text-center sm:text-right min-w-0">
                            <p class="text-[10px] sm:text-xs font-black text-emerald-500 uppercase tracking-widest mb-1 sm:mb-2">Aggregate</p>
                            <p id="totalScore" class="text-3xl sm:text-5xl md:text-6xl lg:text-7xl font-black text-emerald-400 tabular-nums [overflow-wrap:anywhere] leading-none">0%</p>
                        </div>
                    </div>
                </div>

                <div id="auditEntriesContainer" class="space-y-8 sm:space-y-10 md:space-y-12 min-w-0"></div>

                <div class="bg-white rounded-2xl sm:rounded-3xl border border-slate-200 p-4 sm:p-6 md:p-8 shadow-sm min-w-0">
                    <label for="observation_overall_notes" class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.35em] mb-3 sm:mb-4">Overall observation notes</label>
                    <textarea id="observation_overall_notes" name="notes" rows="5" placeholder="Summarize themes, follow-up actions, or context for this submission…" class="w-full rounded-2xl sm:rounded-[2rem] border border-slate-200 bg-slate-50/80 px-4 py-4 sm:px-6 sm:py-5 text-sm font-semibold text-slate-800 placeholder:text-slate-400 focus:border-aps-green focus:ring-2 focus:ring-emerald-500/20 outline-none transition-all resize-y min-h-[100px] sm:min-h-[120px]"></textarea>
                </div>

                <div class="flex justify-center pb-4 sm:pb-8">
                    <button type="button" onclick="addNewAudit()" class="group flex w-full max-w-2xl flex-col items-center justify-center gap-3 px-4 py-5 sm:flex-row sm:gap-5 sm:px-8 sm:py-6 md:px-10 md:py-7 bg-white border-2 border-dashed border-slate-200 rounded-2xl sm:rounded-3xl hover:border-aps-green transition-all shadow-sm text-center sm:text-left">
                        <div class="w-12 h-12 sm:w-14 sm:h-14 bg-slate-100 group-hover:bg-aps-green group-hover:text-white rounded-xl sm:rounded-2xl flex items-center justify-center transition-all text-slate-400 shrink-0">
                            <svg class="w-6 h-6 sm:w-7 sm:h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                        </div>
                        <div class="min-w-0 leading-tight">
                            <p class="text-base sm:text-lg font-black text-slate-800 uppercase tracking-tight">Add New Audit Entry</p>
                            <p class="text-xs sm:text-sm text-slate-400 font-bold uppercase mt-1.5 sm:mt-2">Add another session</p>
                        </div>
                    </button>
                </div>
            </div>

            <div class="p-4 sm:p-6 border-t border-slate-100 bg-white flex flex-col-reverse gap-3 sm:flex-row sm:justify-end sm:items-center sm:gap-4 shrink-0 pb-[max(1rem,env(safe-area-inset-bottom,0px))]">
                <button type="button" onclick="toggleModal('observationModal')" class="w-full sm:w-auto px-6 py-3.5 sm:px-8 sm:py-4 text-sm font-black text-slate-400 uppercase tracking-widest hover:text-slate-600 transition-colors">Discard</button>
                <button type="button" id="observation_submit_btn" onclick="submitObservationForm()" class="w-full sm:w-auto bg-aps-green text-white px-6 py-3.5 sm:px-10 sm:py-4 rounded-2xl sm:rounded-3xl font-black text-sm uppercase tracking-widest shadow-2xl shadow-emerald-900/30 hover:bg-emerald-900 transition-all">Submit Evaluation</button>
            </div>
        </form>
    </div>

    <template id="audit-entry-template">
        <div class="audit-entry bg-white rounded-2xl sm:rounded-3xl border border-slate-200 shadow-sm overflow-hidden relative min-w-0">
            <div class="p-3 sm:p-4 md:p-6 bg-slate-50 border-b border-slate-100 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex min-w-0 items-center gap-2 sm:gap-4">
                    <div class="audit-number w-9 h-9 sm:w-10 sm:h-10 bg-slate-900 text-white rounded-lg sm:rounded-xl flex items-center justify-center text-base sm:text-lg font-black shrink-0">1</div>
                    <h5 class="min-w-0 text-sm sm:text-base md:text-lg font-black text-slate-800 uppercase tracking-tight leading-tight [overflow-wrap:anywhere]">Observation Session</h5>
                </div>
                <button type="button" class="remove-audit self-end sm:self-center text-red-500 hover:text-red-600 text-[10px] sm:text-xs font-black uppercase tracking-widest px-2 py-1.5 sm:px-4 sm:py-2 hover:bg-red-50 rounded-lg sm:rounded-xl transition-all whitespace-nowrap">Remove</button>
            </div>
            <div class="p-3 sm:p-5 md:p-8 space-y-4 sm:space-y-6 md:space-y-8">
                <div class="border border-slate-100 rounded-2xl sm:rounded-[2.5rem] overflow-hidden min-w-0">
                    <button type="button" class="collapse-trigger w-full p-3 sm:p-5 md:p-6 flex justify-between items-center gap-3 bg-emerald-50/40 text-left">
                        <p class="text-xs sm:text-sm md:text-base font-black text-aps-green uppercase tracking-wide sm:tracking-widest [overflow-wrap:anywhere]">Quantitative (70%)</p>
                        <svg class="w-5 h-5 sm:w-6 sm:h-6 text-aps-green chevron-icon shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    <div class="collapse-content active p-3 sm:p-5 md:p-6 space-y-2 sm:space-y-3 md:space-y-4 quant-metrics-list"></div>
                </div>
                <div class="border border-slate-100 rounded-2xl sm:rounded-[2.5rem] overflow-hidden min-w-0">
                    <button type="button" class="collapse-trigger w-full p-3 sm:p-5 md:p-6 flex justify-between items-center gap-3 bg-slate-100/40 text-left">
                        <p class="text-xs sm:text-sm md:text-base font-black text-slate-600 uppercase tracking-wide sm:tracking-widest [overflow-wrap:anywhere]">Qualitative (30%)</p>
                        <svg class="w-5 h-5 sm:w-6 sm:h-6 text-slate-400 chevron-icon shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    <div class="collapse-content p-3 sm:p-5 md:p-6 space-y-2 sm:space-y-3 md:space-y-4 qual-metrics-list"></div>
                </div>
                <div class="pt-2 sm:pt-0 border-t border-slate-50 pb-2">
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.35em] mb-2 sm:mb-3">Remarks</label>
                    <textarea rows="3" class="session-notes-input w-full rounded-2xl sm:rounded-[2rem] border border-slate-200 bg-slate-50/80 px-4 py-3 sm:px-6 sm:py-4 text-sm font-semibold text-slate-800 placeholder:text-slate-400 focus:border-aps-green focus:ring-2 focus:ring-emerald-500/20 outline-none transition-all resize-y" placeholder="Optional remarks for this session…"></textarea>
                </div>
            </div>
        </div>
    </template>

    <template id="rating-row-template">
        <div class="flex flex-col gap-3 p-3 sm:p-4 md:p-5 border border-slate-50 rounded-2xl sm:rounded-3xl hover:bg-slate-50/80 transition-all min-w-0">
            <p class="text-xs sm:text-sm font-black text-slate-700 uppercase tracking-wide leading-snug [overflow-wrap:anywhere] metric-name"></p>
            <div class="flex flex-wrap justify-center sm:justify-end gap-1.5 sm:gap-2 -mx-0.5 sm:mx-0">
                <label class="flex min-w-0 flex-1 flex-col items-center gap-1 sm:gap-1.5 cursor-pointer sm:min-w-0 sm:flex-initial">
                    <input type="radio" value="1" class="hidden rating-input">
                    <div class="w-11 h-11 sm:w-12 sm:h-12 md:w-14 md:h-14 rounded-xl sm:rounded-2xl border-2 border-slate-200 rating-label flex items-center justify-center font-black text-sm sm:text-base transition-all bg-white">1</div>
                    <span class="text-[7px] sm:text-[8px] md:text-[9px] text-slate-400 font-bold uppercase tracking-tighter text-center leading-tight">V. Poor</span>
                </label>
                <label class="flex min-w-0 flex-1 flex-col items-center gap-1 sm:gap-1.5 cursor-pointer sm:min-w-0 sm:flex-initial">
                    <input type="radio" value="2" class="hidden rating-input">
                    <div class="w-11 h-11 sm:w-12 sm:h-12 md:w-14 md:h-14 rounded-xl sm:rounded-2xl border-2 border-slate-200 rating-label flex items-center justify-center font-black text-sm sm:text-base transition-all bg-white">2</div>
                    <span class="text-[7px] sm:text-[8px] md:text-[9px] text-slate-400 font-bold uppercase tracking-tighter">Poor</span>
                </label>
                <label class="flex min-w-0 flex-1 flex-col items-center gap-1 sm:gap-1.5 cursor-pointer sm:min-w-0 sm:flex-initial">
                    <input type="radio" value="3" class="hidden rating-input">
                    <div class="w-11 h-11 sm:w-12 sm:h-12 md:w-14 md:h-14 rounded-xl sm:rounded-2xl border-2 border-slate-200 rating-label flex items-center justify-center font-black text-sm sm:text-base transition-all bg-white">3</div>
                    <span class="text-[7px] sm:text-[8px] md:text-[9px] text-slate-400 font-bold uppercase tracking-tighter">Avg</span>
                </label>
                <label class="flex min-w-0 flex-1 flex-col items-center gap-1 sm:gap-1.5 cursor-pointer sm:min-w-0 sm:flex-initial">
                    <input type="radio" value="4" class="hidden rating-input">
                    <div class="w-11 h-11 sm:w-12 sm:h-12 md:w-14 md:h-14 rounded-xl sm:rounded-2xl border-2 border-slate-200 rating-label flex items-center justify-center font-black text-sm sm:text-base transition-all bg-white">4</div>
                    <span class="text-[7px] sm:text-[8px] md:text-[9px] text-slate-400 font-bold uppercase tracking-tighter">Good</span>
                </label>
                <label class="flex min-w-0 flex-1 flex-col items-center gap-1 sm:gap-1.5 cursor-pointer sm:min-w-0 sm:flex-initial">
                    <input type="radio" value="5" class="hidden rating-input">
                    <div class="w-11 h-11 sm:w-12 sm:h-12 md:w-14 md:h-14 rounded-xl sm:rounded-2xl border-2 border-slate-200 rating-label flex items-center justify-center font-black text-sm sm:text-base transition-all bg-white">5</div>
                    <span class="text-[7px] sm:text-[8px] md:text-[9px] text-slate-400 font-bold uppercase tracking-tighter">Excel</span>
                </label>
            </div>
        </div>
    </template>
