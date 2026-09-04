@php
    use App\Enums\Department;
    use App\Enums\Wing;
@endphp

<div id="createSecHeadModal" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[100] items-center justify-center p-6">
    <div class="bg-white w-full max-w-4xl rounded-[3rem] shadow-2xl overflow-hidden border border-slate-200 max-h-[90vh] overflow-y-auto">
        <div class="p-10 border-b border-slate-100 flex justify-between bg-slate-50/50 sticky top-0 z-10">
            <h3 class="text-3xl font-black text-slate-800 tracking-tight uppercase leading-none">Register SectionHead</h3>
            <button type="button" onclick="toggleModal('createSecHeadModal')" class="text-slate-400"><svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
        </div>
        <form action="{{ route('section-heads.store') }}" method="post" enctype="multipart/form-data" class="p-10 space-y-10">
            @csrf
            <div class="flex justify-center">
                <div class="relative group">
                    <div class="w-32 h-32 relative rounded-full shadow-lg border-4 border-white overflow-hidden bg-aps-green flex items-center justify-center text-white font-black text-5xl" id="createInitials">NEW</div>
                    <img id="createAvatarPreview" src="" class="absolute inset-0 w-full h-full object-cover hidden rounded-full" alt="">
                    <button type="button" onclick="document.getElementById('createAvatar').click()" class="absolute right-0 -bottom-2 w-10 h-10 bg-slate-900 text-white rounded-full flex items-center justify-center border-2 border-white shadow-xl"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg></button>
                    <input type="file" id="createAvatar" name="avatar" class="hidden" accept="image/*" onchange="previewImage(this, 'createAvatarPreview', 'createInitials')">
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div><label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-2">Full name <span class="text-red-500">*</span></label><input type="text" name="name" value="{{ old('name') }}" required class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-semibold outline-none focus:border-aps-green"></div>
                <div>
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-2">Assigned wing <span class="text-red-500">*</span></label>
                    <select name="wing" id="createSecHeadWing" required class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-semibold outline-none appearance-none">
                        <option value="">Select wing</option>
                        @foreach ($availableWingsForCreate as $w)
                            <option value="{{ $w->value }}" @selected(old('wing') === $w->value)>{{ $w->label() }}</option>
                        @endforeach
                    </select>
                    @if($availableWingsForCreate->isEmpty())
                        <p class="text-xs font-semibold text-red-600 mt-2">No wings available — each wing already has a section head.</p>
                    @endif
                </div>
                <div>
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-2">Employee ID</label>
                    <div id="createSecHeadIdPreview" class="w-full px-5 py-3.5 bg-slate-100 border border-dashed border-slate-300 rounded-2xl text-sm font-mono font-bold text-slate-700 min-h-[3.25rem] flex items-center">—</div>
                    <p class="text-[10px] text-slate-400 font-semibold mt-2">Auto-generated when you save</p>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div><label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-2">Institutional email <span class="text-red-500">*</span></label><input type="email" name="email" value="{{ old('email') }}" required autocomplete="username" class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-semibold outline-none focus:border-aps-green"></div>
                <div><label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-2">Institutional password <span class="text-red-500">*</span></label><input type="password" name="password" required autocomplete="new-password" minlength="8" class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-semibold outline-none focus:border-aps-green"></div>
            </div>
            <div>
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-2">Title (optional)</label>
                <input type="text" name="title" value="{{ old('title') }}" placeholder="e.g. Senior Wing coordinator" class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-semibold outline-none focus:border-aps-green">
            </div>
            <div>
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-3">Departments <span class="text-red-500">*</span></label>
                <p class="text-xs text-slate-500 font-semibold mb-4">Select all departments this section head oversees.</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 max-h-56 overflow-y-auto pr-2">
                    @foreach (Department::cases() as $dept)
                        <label class="flex items-center gap-3 px-4 py-3 rounded-2xl border border-slate-200 bg-slate-50/80 cursor-pointer hover:border-aps-green/40 transition-colors has-[:checked]:border-aps-green has-[:checked]:bg-emerald-50/50">
                            <input type="checkbox" name="departments[]" value="{{ $dept->value }}" class="rounded border-slate-300 text-aps-green focus:ring-aps-green" {{ in_array($dept->value, old('departments', []), true) ? 'checked' : '' }}>
                            <span class="text-sm font-semibold text-slate-800">{{ $dept->label() }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
            <div id="createOtherDeptWrap" class="rounded-2xl border border-dashed border-slate-200 bg-slate-50/80 p-6 space-y-3 {{ in_array(Department::Other->value, old('departments', []), true) ? '' : 'hidden' }}">
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block">Custom department name <span class="text-red-500">*</span></label>
                <p class="text-xs text-slate-500 font-semibold">Shown when &ldquo;Other&rdquo; is selected. Must be unique (similar names are rejected).</p>
                <input type="text" name="other_department_label" id="create_other_department_label" value="{{ old('other_department_label') }}" maxlength="120" autocomplete="off" placeholder="e.g. Robotics Lab, STEAM Studio" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl text-sm font-semibold outline-none focus:border-aps-green">
                @error('other_department_label')
                    <p class="text-sm font-semibold text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div class="flex justify-end gap-4 border-t border-slate-100 pt-8">
                <button type="button" onclick="toggleModal('createSecHeadModal')" class="px-8 py-4 text-sm font-bold text-slate-400 uppercase tracking-widest">Cancel</button>
                <button type="submit" class="bg-aps-green text-white px-10 py-4 rounded-2xl font-black text-sm uppercase tracking-widest shadow-xl">Create Profile</button>
            </div>
        </form>
    </div>
</div>

<div id="viewSecHeadModal" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[100] items-center justify-center p-6">
    <div class="bg-white w-full max-w-3xl rounded-[3rem] shadow-2xl overflow-hidden border border-slate-200">
        <div class="p-10 border-b border-slate-100 flex justify-between bg-slate-50/50">
            <h3 class="text-3xl font-black text-slate-800 tracking-tight uppercase leading-none">Section head profile</h3>
            <button type="button" onclick="toggleModal('viewSecHeadModal')" class="text-slate-400"><svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
        </div>
        <div class="p-12 space-y-10">
            <div class="flex flex-col sm:flex-row sm:items-start gap-10">
                <div class="relative w-32 h-32 shrink-0 mx-auto sm:mx-0">
                    <img id="viewShAvatar" src="" alt="" class="hidden w-32 h-32 rounded-[2.5rem] object-cover shadow-xl border border-slate-100"
                        onerror="this.onerror=null;this.classList.add('hidden');document.getElementById('viewShInitials').classList.remove('hidden');">
                    <div id="viewShInitials" class="w-32 h-32 bg-aps-green rounded-[2.5rem] flex items-center justify-center text-white text-5xl font-black shadow-xl">?</div>
                </div>
                <div class="grid w-full min-w-0 sm:flex-1 grid-cols-1 sm:grid-cols-2 gap-x-10 gap-y-5">
                    <div class="min-w-0 sm:pr-1">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Name</p>
                        <p id="viewShName" class="text-base sm:text-lg font-black text-slate-800 mt-1 break-words [overflow-wrap:anywhere]"></p>
                    </div>
                    <div class="min-w-0 sm:pl-1">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Employee ID</p>
                        <p id="viewShEmp" class="text-base sm:text-lg font-black text-slate-800 mt-1 break-words [overflow-wrap:anywhere]"></p>
                    </div>
                    <div class="min-w-0 sm:col-span-2">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Email</p>
                        <p id="viewShEmail" class="text-sm sm:text-base font-bold text-aps-green mt-1 break-words [overflow-wrap:anywhere] leading-relaxed"></p>
                    </div>
                    <div class="min-w-0 sm:col-span-2">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Wing</p>
                        <p id="viewShWing" class="text-base sm:text-lg font-black text-slate-800 mt-1 break-words [overflow-wrap:anywhere]"></p>
                    </div>
                    <div class="min-w-0 sm:col-span-2">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Departments</p>
                        <p id="viewShDepartments" class="text-sm font-bold text-slate-700 leading-relaxed mt-2 break-words [overflow-wrap:anywhere]"></p>
                    </div>
                </div>
            </div>
            <div class="mt-8 pt-8 border-t border-slate-100 flex justify-end">
                <button type="button" onclick="toggleModal('viewSecHeadModal')" class="bg-slate-900 text-white px-12 py-4 rounded-2xl font-black text-sm uppercase tracking-widest shadow-xl">Close</button>
            </div>
        </div>
    </div>
</div>

<div id="editSecHeadModal" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[100] items-center justify-center p-6">
    <div class="bg-white w-full max-w-4xl rounded-[3rem] shadow-2xl overflow-hidden border border-slate-200 max-h-[90vh] overflow-y-auto">
        <div class="p-10 border-b border-slate-100 flex justify-between bg-slate-50/50 sticky top-0 z-10">
            <h3 class="text-3xl font-black text-slate-800 tracking-tight uppercase leading-none">Update Profile</h3>
            <button type="button" onclick="toggleModal('editSecHeadModal')" class="text-slate-400"><svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
        </div>
        <form id="editSecHeadForm" action="" method="post" enctype="multipart/form-data" class="p-10 space-y-10">
            @csrf
            @method('PUT')
            <div class="flex justify-center">
                <div class="relative group">
                    <div class="w-32 h-32 relative rounded-full shadow-lg border-4 border-white overflow-hidden bg-aps-green flex items-center justify-center text-white font-black text-5xl" id="editInitials">?</div>
                    <img id="editAvatarPreview" src="" class="absolute inset-0 w-full h-full object-cover hidden rounded-full" alt="">
                    <button type="button" onclick="document.getElementById('editAvatar').click()" class="absolute right-0 -bottom-2 w-10 h-10 bg-slate-900 text-white rounded-full flex items-center justify-center border-2 border-white shadow-xl hover:bg-aps-green transition-colors"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg></button>
                    <input type="file" id="editAvatar" name="avatar" class="hidden" accept="image/*" onchange="previewImage(this, 'editAvatarPreview', 'editInitials')">
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div><label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-2 px-1">Full name <span class="text-red-500">*</span></label><input type="text" id="edit_sh_name" name="name" required class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-black outline-none"></div>
                <div>
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-2 px-1">Employee ID</label>
                    <p id="edit_sh_employee_id_display" class="w-full px-5 py-3.5 bg-slate-100 border border-slate-200 rounded-2xl text-sm font-black text-slate-800"></p>
                    <p class="text-[10px] text-slate-400 font-semibold mt-1 px-1">System-assigned; cannot be changed.</p>
                </div>
                <div>
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-2 px-1">Assigned wing <span class="text-red-500">*</span></label>
                    <select id="edit_sh_wing" name="wing" required class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-black outline-none">
                        @foreach (Wing::cases() as $w)
                            <option value="{{ $w->value }}">{{ $w->label() }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div><label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-2 px-1">Institutional email <span class="text-red-500">*</span></label><input type="email" id="edit_sh_email" name="email" required class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-black outline-none"></div>
                <div><label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-2 px-1">Change Password</label><input type="password" id="edit_sh_password" name="password" placeholder="Leave blank to keep same" autocomplete="new-password" class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-black outline-none"></div>
            </div>
            <div><label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-2 px-1">Title (optional)</label><input type="text" id="edit_sh_title" name="title" class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-black outline-none"></div>
            <div>
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-3 px-1">Departments <span class="text-red-500">*</span></label>
                <div id="editSecHeadDepartments" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 max-h-56 overflow-y-auto pr-2">
                    @foreach (Department::cases() as $dept)
                        <label class="flex items-center gap-3 px-4 py-3 rounded-2xl border border-slate-200 bg-slate-50/80 cursor-pointer hover:border-aps-green/40 transition-colors has-[:checked]:border-aps-green has-[:checked]:bg-emerald-50/50">
                            <input type="checkbox" name="departments[]" value="{{ $dept->value }}" class="edit-dept-checkbox rounded border-slate-300 text-aps-green focus:ring-aps-green">
                            <span class="text-sm font-semibold text-slate-800">{{ $dept->label() }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
            <div id="editOtherDeptWrap" class="hidden rounded-2xl border border-dashed border-slate-200 bg-slate-50/80 p-6 space-y-3">
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block px-1">Custom department name <span class="text-red-500">*</span></label>
                <p class="text-xs text-slate-500 font-semibold px-1">Required when &ldquo;Other&rdquo; is selected. Must be unique (similar names are rejected).</p>
                <input type="text" name="other_department_label" id="edit_other_department_label" maxlength="120" autocomplete="off" placeholder="e.g. Robotics Lab" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl text-sm font-semibold outline-none focus:border-aps-green">
                @error('other_department_label')
                    <p class="text-sm font-semibold text-red-600 px-1">{{ $message }}</p>
                @enderror
            </div>
            <div class="flex justify-end gap-4 border-t border-slate-100 pt-8">
                <button type="button" onclick="toggleModal('editSecHeadModal')" class="px-8 py-4 text-sm font-bold text-slate-400 uppercase tracking-widest">Cancel</button>
                <button type="submit" class="bg-aps-green text-white px-10 py-4 rounded-2xl font-black text-sm uppercase tracking-widest shadow-xl">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<div id="deleteSecHeadModal" class="hidden fixed inset-0 bg-slate-900/80 backdrop-blur-sm z-[100] items-center justify-center p-6">
    <div class="bg-white w-full max-w-md rounded-[3rem] shadow-2xl p-12 text-center border-4 border-red-50">
        <div class="w-20 h-20 bg-red-50 text-red-500 rounded-full flex items-center justify-center mx-auto mb-6"><svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg></div>
        <h3 class="text-2xl font-black text-slate-800 uppercase tracking-tight">Revoke Access?</h3>
        <p class="text-slate-400 font-medium mt-4 leading-relaxed">This will permanently remove <span id="deleteSecHeadName" class="font-bold text-slate-600"></span>&rsquo;s administrative profile and wing data.</p>
        <form id="deleteSecHeadForm" action="" method="post" class="mt-10 space-y-3">
            @csrf
            @method('DELETE')
            <button type="submit" class="w-full bg-red-500 text-white py-4 rounded-2xl font-black text-sm uppercase tracking-widest shadow-xl">Yes, Delete Profile</button>
            <button type="button" onclick="toggleModal('deleteSecHeadModal')" class="w-full py-4 text-sm font-black text-slate-400 uppercase tracking-widest">Keep Profile</button>
        </form>
    </div>
</div>
