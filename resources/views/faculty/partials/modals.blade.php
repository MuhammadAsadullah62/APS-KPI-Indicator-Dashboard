@php
    use App\Enums\Department;
    use App\Enums\Wing;
@endphp

<div id="createFacultyModal" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-100 items-center justify-center p-6" @if(auth()->user()->isSectionHead() && auth()->user()->wing) data-sh-wing="{{ auth()->user()->wing->value }}" @endif>
    <div class="bg-white w-full max-w-4xl rounded-[3rem] shadow-2xl border border-slate-200 max-h-[90vh] overflow-hidden flex flex-col">
        <div class="min-h-0 overflow-y-auto">
        <div class="p-10 border-b border-slate-100 flex justify-between bg-slate-50/50 sticky top-0 z-10">
            <h3 class="text-3xl font-black text-slate-800 tracking-tight uppercase">Onboard Teacher</h3>
            <button type="button" onclick="toggleModal('createFacultyModal')" class="text-slate-400 hover:text-slate-600"><svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
        </div>
        <form action="{{ route('faculty.store') }}" method="post" enctype="multipart/form-data" class="p-10 space-y-8">
            @csrf
            <div class="flex justify-center">
                <div class="relative group w-32 h-32">
                    <div class="w-32 h-32 relative rounded-full shadow-lg border-4 border-white overflow-hidden bg-aps-green flex items-center justify-center text-white font-black text-5xl" id="cInitials">NEW</div>
                    <img id="cAvatarPreview" src="" class="absolute inset-0 w-full h-full object-cover hidden rounded-full" alt="">
                    <button type="button" onclick="document.getElementById('cAvatar').click()" class="absolute right-0 -bottom-2 w-10 h-10 bg-slate-900 text-white rounded-full flex items-center justify-center border-2 border-white shadow-xl"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg></button>
                    <input type="file" id="cAvatar" name="avatar" class="hidden" accept="image/*" onchange="previewImage(this, 'cAvatarPreview', 'cInitials')">
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div><label class="text-[10px] font-black text-slate-400 uppercase mb-2 block">Full name <span class="text-red-500">*</span></label><input type="text" name="name" value="{{ old('name') }}" required class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-semibold outline-none focus:border-aps-green"></div>
            </div>
            <div>
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-3">Departments <span class="text-red-500">*</span></label>
                <p class="text-xs text-slate-500 font-semibold mb-4">Select all departments this teacher is associated with.</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 max-h-56 overflow-y-auto pr-2">
                    @foreach (Department::cases() as $dept)
                        <label class="flex items-center gap-3 px-4 py-3 rounded-2xl border border-slate-200 bg-slate-50/80 cursor-pointer hover:border-aps-green/40 transition-colors has-checked:border-aps-green has-checked:bg-emerald-50/50">
                            <input type="checkbox" name="departments[]" value="{{ $dept->value }}" class="rounded border-slate-300 text-aps-green focus:ring-aps-green create-fa-dept-cb" {{ in_array($dept->value, old('departments', []), true) ? 'checked' : '' }}>
                            <span class="text-sm font-semibold text-slate-800">{{ $dept->label() }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
            <div id="createFacultyOtherDeptWrap" class="rounded-2xl border border-dashed border-slate-200 bg-slate-50/80 p-6 space-y-3 {{ in_array(Department::Other->value, old('departments', []), true) ? '' : 'hidden' }}">
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block">Custom department name <span class="text-red-500">*</span></label>
                <p class="text-xs text-slate-500 font-semibold">Shown when &ldquo;Other&rdquo; is selected. Must be unique (similar names are rejected).</p>
                <input type="text" name="other_department_label" id="create_faculty_other_department_label" value="{{ old('other_department_label') }}" maxlength="120" autocomplete="off" placeholder="e.g. Robotics Lab, STEAM Studio" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl text-sm font-semibold outline-none focus:border-aps-green">
                @error('other_department_label')
                    <p class="text-sm font-semibold text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                @if(auth()->user()->isAdmin() || auth()->user()->isPrincipal())
                <div>
                    <label class="text-[10px] font-black text-slate-400 uppercase mb-2 block">Assigned wing <span class="text-red-500">*</span></label>
                    <select name="wing" id="createFacultyWing" required class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-semibold outline-none appearance-none">
                        <option value="">Select wing</option>
                        @foreach (Wing::cases() as $w)
                            <option value="{{ $w->value }}" @selected(old('wing') === $w->value)>{{ $w->label() }}</option>
                        @endforeach
                    </select>
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-2 mt-5">Employee ID</label>
                    <div class="js-create-faculty-id-preview w-full px-5 py-3.5 bg-slate-100 border border-dashed border-slate-300 rounded-2xl text-sm font-mono font-bold text-slate-700 min-h-13 flex items-center">—</div>
                    <p class="text-[10px] text-slate-400 font-semibold mt-2">Auto-generated when you save</p>
                </div>
                @elseif(auth()->user()->isSectionHead() && auth()->user()->wing)
                <div class="flex flex-col justify-end pb-1">
                    <label class="text-[10px] font-black text-slate-400 uppercase mb-2 block">Assigned wing</label>
                    <p class="text-sm font-black text-slate-800">{{ auth()->user()->wing->label() }}</p>
                    <p class="text-[10px] text-slate-400 font-semibold mt-1">Teachers are registered under your wing.</p>
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-2 mt-5">Employee ID</label>
                    <div class="js-create-faculty-id-preview w-full px-5 py-3.5 bg-slate-100 border border-dashed border-slate-300 rounded-2xl text-sm font-mono font-bold text-slate-700 min-h-13 flex items-center">—</div>
                    <p class="text-[10px] text-slate-400 font-semibold mt-2">Auto-generated when you save</p>
                </div>
                @endif
                <div><label class="text-[10px] font-black text-slate-400 uppercase mb-2 block">Email <span class="text-red-500">*</span></label><input type="email" name="email" value="{{ old('email') }}" required autocomplete="username" class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-semibold outline-none focus:border-aps-green"></div>
                <div><label class="text-[10px] font-black text-slate-400 uppercase mb-2 block">Password <span class="text-red-500">*</span></label><input type="password" name="password" required autocomplete="new-password" minlength="8" class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-semibold outline-none focus:border-aps-green"></div>
            </div>
            <div>
                <label class="text-[10px] font-black text-slate-400 uppercase mb-2 block">Title (optional)</label>
                <input type="text" name="title" value="{{ old('title') }}" class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-semibold outline-none focus:border-aps-green">
            </div>
            <div class="flex justify-end gap-4 border-t border-slate-100 pt-8">
                <button type="button" onclick="toggleModal('createFacultyModal')" class="px-8 py-4 text-sm font-bold text-slate-400 uppercase tracking-widest">Cancel</button>
                <button type="submit" class="bg-aps-green text-white px-10 py-4 rounded-2xl font-black text-sm uppercase tracking-widest shadow-xl">Register Account</button>
            </div>
        </form>
        </div>
    </div>
</div>

<x-directory.view-modal
    id="viewFacultyModal"
    title="Instructor Overview"
    avatar-id="viewFaAvatar"
    initials-id="viewFaInitials"
    accent="bg-emerald-500"
    close-label="Close Profile"
    :fields="[
        ['label' => 'Full Name', 'id' => 'viewFaName'],
        ['label' => 'Employee ID', 'id' => 'viewFaEmp'],
        ['label' => 'Email', 'id' => 'viewFaEmail', 'full' => true, 'email' => true],
        ['label' => 'Departments & wing', 'id' => 'viewFaDept', 'full' => true],
    ]" />

<div id="editFacultyModal" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-100 items-center justify-center p-6">
    <div class="bg-white w-full max-w-4xl rounded-[3rem] shadow-2xl border border-slate-200 max-h-[90vh] overflow-hidden flex flex-col">
        <div class="min-h-0 overflow-y-auto">
        <div class="p-10 border-b border-slate-100 flex justify-between bg-slate-50/50 sticky top-0 z-10">
            <h3 class="text-3xl font-black text-slate-800 tracking-tight uppercase leading-none">Update Faculty Data</h3>
            <button type="button" onclick="toggleModal('editFacultyModal')" class="text-slate-400"><svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
        </div>
        <form id="editFacultyForm" action="" method="post" enctype="multipart/form-data" class="p-10 space-y-8">
            @csrf
            @method('PUT')
            <div class="flex justify-center">
                <div class="relative group w-32 h-32">
                    <div class="w-32 h-32 relative rounded-full shadow-lg border-4 border-white overflow-hidden bg-emerald-500 flex items-center justify-center text-white font-black text-5xl" id="eInitials">?</div>
                    <img id="editFaAvatarPreview" src="" class="absolute inset-0 w-full h-full object-cover hidden rounded-full" alt="">
                    <button type="button" onclick="document.getElementById('editFaAvatar').click()" class="absolute right-0 -bottom-2 w-10 h-10 bg-slate-900 text-white rounded-full flex items-center justify-center border-2 border-white shadow-xl hover:bg-aps-green transition-colors"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg></button>
                    <input type="file" id="editFaAvatar" name="avatar" class="hidden" accept="image/*" onchange="previewImage(this, 'editFaAvatarPreview', 'eInitials')">
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div><label class="text-[10px] font-black text-slate-400 uppercase mb-2 block">Full name <span class="text-red-500">*</span></label><input type="text" id="edit_fa_name" name="name" required class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-black outline-none focus:border-aps-green"></div>
                <div>
                    <label class="text-[10px] font-black text-slate-400 uppercase mb-2 block">Employee ID</label>
                    <p id="edit_fa_employee_id_display" class="w-full px-5 py-3.5 bg-slate-100 border border-slate-200 rounded-2xl text-sm font-black text-slate-800"></p>
                    <p class="text-[10px] text-slate-400 font-semibold mt-1">System-assigned; cannot be changed.</p>
                </div>
            </div>
            <div>
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-3 px-1">Departments <span class="text-red-500">*</span></label>
                <div id="editFacultyDepartmentsGrid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 max-h-56 overflow-y-auto pr-2">
                    @foreach (Department::cases() as $dept)
                        <label class="flex items-center gap-3 px-4 py-3 rounded-2xl border border-slate-200 bg-slate-50/80 cursor-pointer hover:border-aps-green/40 transition-colors has-checked:border-aps-green has-checked:bg-emerald-50/50">
                            <input type="checkbox" name="departments[]" value="{{ $dept->value }}" class="edit-fa-dept-cb rounded border-slate-300 text-aps-green focus:ring-aps-green">
                            <span class="text-sm font-semibold text-slate-800">{{ $dept->label() }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
            <div id="editFacultyOtherDeptWrap" class="hidden rounded-2xl border border-dashed border-slate-200 bg-slate-50/80 p-6 space-y-3">
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block px-1">Custom department name <span class="text-red-500">*</span></label>
                <p class="text-xs text-slate-500 font-semibold px-1">Required when &ldquo;Other&rdquo; is selected. Must be unique (similar names are rejected).</p>
                <input type="text" name="other_department_label" id="edit_faculty_other_department_label" maxlength="120" autocomplete="off" placeholder="e.g. Robotics Lab" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl text-sm font-semibold outline-none focus:border-aps-green">
                @error('other_department_label')
                    <p class="text-sm font-semibold text-red-600 px-1">{{ $message }}</p>
                @enderror
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                @if(auth()->user()->isAdmin() || auth()->user()->isPrincipal())
                <div>
                    <label class="text-[10px] font-black text-slate-400 uppercase mb-2 block">Assigned wing <span class="text-red-500">*</span></label>
                    <select id="edit_fa_wing" name="wing" required class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-black outline-none">
                        @foreach (Wing::cases() as $w)
                            <option value="{{ $w->value }}">{{ $w->label() }}</option>
                        @endforeach
                    </select>
                </div>
                @elseif(auth()->user()->isSectionHead() && auth()->user()->wing)
                <div class="flex flex-col justify-end pb-1">
                    <label class="text-[10px] font-black text-slate-400 uppercase mb-2 block">Wing</label>
                    <p class="text-sm font-black text-slate-800">{{ auth()->user()->wing->label() }}</p>
                    <p class="text-[10px] text-slate-400 font-semibold mt-1">Teachers remain under your wing.</p>
                </div>
                @endif
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div><label class="text-[10px] font-black text-slate-400 uppercase mb-2 block">Email <span class="text-red-500">*</span></label><input type="email" id="edit_fa_email" name="email" required class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-black outline-none focus:border-aps-green"></div>
                <div><label class="text-[10px] font-black text-slate-400 uppercase mb-2 block">Modify Password</label><input type="password" id="edit_fa_password" name="password" placeholder="Leave empty to remain unchanged" autocomplete="new-password" class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-black outline-none focus:border-aps-green"></div>
            </div>
            <div><label class="text-[10px] font-black text-slate-400 uppercase mb-2 block">Title (optional)</label><input type="text" id="edit_fa_title" name="title" class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-black outline-none focus:border-aps-green"></div>
            <div class="flex justify-end gap-4 border-t border-slate-100 pt-8">
                <button type="button" onclick="toggleModal('editFacultyModal')" class="px-8 py-4 text-sm font-bold text-slate-400 uppercase tracking-widest hover:text-slate-600 transition-colors">Cancel</button>
                <button type="submit" class="bg-aps-green text-white px-10 py-4 rounded-2xl font-black text-sm uppercase tracking-widest shadow-xl shadow-emerald-900/20 hover:bg-emerald-900 transition-all">Save Faculty Record</button>
            </div>
        </form>
        </div>
    </div>
</div>

<div id="deleteFacultyModal" class="hidden fixed inset-0 bg-slate-900/80 backdrop-blur-sm z-100 items-center justify-center p-6">
    <div class="bg-white w-full max-w-md rounded-[3rem] shadow-2xl p-12 text-center border-4 border-red-50">
        <div class="w-20 h-20 bg-red-50 text-red-500 rounded-full flex items-center justify-center mx-auto mb-6"><svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg></div>
        <h3 class="text-2xl font-black text-slate-800 uppercase tracking-tight leading-none">Terminate Access?</h3>
        <p class="text-slate-400 font-medium mt-4 leading-relaxed">This will permanently remove <span id="deleteFacultyName" class="font-bold text-slate-600"></span>&rsquo;s profile from the system.</p>
        <form id="deleteFacultyForm" action="" method="post" class="mt-10 space-y-3">
            @csrf
            @method('DELETE')
            <button type="submit" class="w-full bg-red-500 text-white py-4 rounded-2xl font-black text-sm uppercase tracking-widest shadow-xl hover:bg-red-600">Permanently Delete</button>
            <button type="button" onclick="toggleModal('deleteFacultyModal')" class="w-full py-4 text-sm font-black text-slate-400 uppercase tracking-widest">Discard Action</button>
        </form>
    </div>
</div>
