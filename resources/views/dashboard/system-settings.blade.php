@extends('layouts.app')

@php
    use App\Enums\Department;
    use App\Enums\Wing;
@endphp

@section('title', 'APSACS Khanewal | System Settings Dashboard')

@push('styles')
<style>.modal-active { align-items: center; justify-content: center; }</style>
@endpush

@section('header')
    <x-dashboard.page-header title="System settings" subtitle="{{ $showOverview ? 'Institutional overview & statistics' : '' }}" />
@endsection

@section('content')
            @if($showOverview)
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-6">
                <div class="bg-aps-green p-8 rounded-4xl text-white shadow-xl relative overflow-hidden group">
                    <p class="text-[10px] font-bold text-emerald-300 uppercase tracking-widest mb-1 relative z-10">Total users</p>
                    <h3 class="text-5xl font-black relative z-10">{{ number_format($stats['total_users']) }}</h3>
                    <div class="absolute -right-6 -bottom-6 w-32 h-32 bg-white/5 rounded-full blur-2xl group-hover:bg-white/10 transition-all"></div>
                </div>
                <div class="bg-white p-8 rounded-4xl border border-slate-200 shadow-sm"><p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Section heads</p><h3 class="text-5xl font-black text-slate-800">{{ number_format($stats['section_heads']) }}</h3></div>
                <div class="bg-white p-8 rounded-4xl border border-slate-200 shadow-sm"><p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Teachers</p><h3 class="text-5xl font-black text-slate-800">{{ number_format($stats['faculty']) }}</h3></div>
                <div class="bg-white p-8 rounded-4xl border border-slate-200 shadow-sm"><p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Admin / Principal</p><h3 class="text-5xl font-black text-slate-800">{{ number_format($stats['leadership']) }}</h3></div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="lg:col-span-2 bg-white rounded-[2.5rem] border border-slate-200 shadow-sm overflow-hidden">
                    <div class="p-8 border-b border-slate-100 bg-slate-50/50">
                        <h3 class="text-xl font-black text-slate-800 uppercase tracking-tight">Recently onboarded</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <tbody class="divide-y divide-slate-100">
                                @forelse ($recentUsers as $row)
                                <tr class="group hover:bg-emerald-50/30 transition-colors">
                                    <td class="px-8 py-5 flex items-center gap-4">
                                        <x-avatar :user="$row" box="h-10 w-10 rounded-xl" :px="40"
                                            img-class="shadow-sm border border-slate-100"
                                            fallback-class="bg-slate-100 text-slate-500 text-xs" />
                                        <div>
                                            <p class="text-sm text-slate-800 leading-none">{{ $row->name }}</p>
                                            @php
                                                $deptLine = $row->departmentsLabelForDisplay();
                                                $sub = array_values(array_filter([
                                                    $row->wing?->label(),
                                                    $deptLine !== '—' ? $deptLine : null,
                                                ]));
                                            @endphp
                                            <p class="text-[10px] text-slate-400 mt-1 uppercase tracking-wider">{{ count($sub) ? implode(' • ', $sub) : $row->roleLabel() }}</p>
                                        </div>
                                    </td>
                                    <td class="px-8 py-5 text-right">
                                        <x-directory.view-button variant="compact" handler="openOverviewUserView" :payload="[
                                            'name' => $row->name,
                                            'employee_id' => $row->employee_id,
                                            'email' => $row->email,
                                            'wing_label' => $row->wing?->label(),
                                            'department_label' => $row->departmentsLabelForDisplay(),
                                            'role_label' => $row->roleLabel(),
                                            'avatar' => $row->avatarUrl(),
                                            'initials' => $row->initials(),
                                        ]" />
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="2" class="px-8 py-12 text-center text-slate-400 font-semibold">No users in the directory yet.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="bg-slate-900 rounded-[2.5rem] p-10 text-white shadow-2xl relative overflow-hidden border-b-4 border-emerald-500 flex flex-col justify-center text-center">
                    <h4 class="text-2xl font-black mb-4 tracking-tight uppercase">Security protocol</h4>
                    <p class="text-sm text-slate-400 italic">User data modifications are restricted and logged by institutional governance.</p>
                </div>
            </div>
            @endif

            <div class="space-y-6 {{ $showOverview ? 'mt-6' : '' }}">
                <div class="flex items-center gap-4 px-2">
                    <div class="h-px bg-slate-200 flex-1"></div>
                    <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.4em]">Faculty directory by wing</h3>
                    <div class="h-px bg-slate-200 flex-1"></div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    @foreach (Wing::cases() as $wing)
                    @php
                        $members = $facultyByWing[$wing->value] ?? collect();
                    @endphp
                    <div class="bg-white rounded-[2.5rem] border border-slate-200 shadow-sm flex flex-col h-105">
                        <div class="p-8 border-b border-slate-100 flex items-center justify-between">
                            <h4 class="text-lg font-black text-slate-800 uppercase">{{ $wing->label() }}</h4>
                            <span class="bg-emerald-50 text-aps-green text-[10px] font-black px-2.5 py-1 rounded-lg uppercase">{{ $members->count() }} {{ $members->count() === 1 ? 'member' : 'members' }}</span>
                        </div>
                        <div class="flex-1 overflow-y-auto p-6 space-y-4 no-scrollbar">
                            @forelse ($members as $row)
                                @include('faculty.partials.directory-member-row', ['row' => $row, 'readOnly' => $directoryReadOnly])
                            @empty
                            <div class="flex items-center justify-between p-3 rounded-2xl font-semibold text-slate-400 text-sm">No teachers in this wing yet.</div>
                            @endforelse
                        </div>
                    </div>
                    @endforeach
                </div>

                @if($facultyUnassigned->isNotEmpty())
                <div class="mt-6 bg-amber-50/80 rounded-[2.5rem] border border-amber-200/80 shadow-sm flex flex-col min-h-50 max-h-105">
                    <div class="p-8 border-b border-amber-100 flex items-center justify-between bg-white/60 rounded-t-[2.5rem]">
                        <div>
                            <h4 class="text-lg font-black text-slate-800 uppercase tracking-tight">No wing assigned</h4>
                            <p class="text-[11px] text-amber-900/70 font-semibold mt-1">Teachers listed here still need a wing set on their profile.</p>
                        </div>
                        <span class="bg-amber-100 text-amber-950 text-[10px] font-black px-2.5 py-1 rounded-lg uppercase">{{ $facultyUnassigned->count() }} {{ $facultyUnassigned->count() === 1 ? 'teacher' : 'teachers' }}</span>
                    </div>
                    <div class="flex-1 overflow-y-auto p-6 space-y-4 no-scrollbar">
                        @foreach ($facultyUnassigned as $row)
                            @include('faculty.partials.directory-member-row', ['row' => $row, 'readOnly' => $directoryReadOnly])
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
@endsection

@push('modals')
@include('dashboard.partials.system-modals')
@include('faculty.partials.modals')
@endpush

@push('scripts')
<script>
var FACULTY_OTHER_DEPT = @json(Department::Other->value);
function syncCreateFacultyOtherDeptWrap() {
    var wrap = document.getElementById('createFacultyOtherDeptWrap');
    if (!wrap) return;
    var cb = document.querySelector('#createFacultyModal input[name="departments[]"][value="' + FACULTY_OTHER_DEPT + '"]');
    if (cb && cb.checked) wrap.classList.remove('hidden');
    else wrap.classList.add('hidden');
}
function syncEditFacultyOtherDeptWrap() {
    var wrap = document.getElementById('editFacultyOtherDeptWrap');
    if (!wrap) return;
    var cb = document.querySelector('#editFacultyForm input[name="departments[]"][value="' + FACULTY_OTHER_DEPT + '"]');
    if (cb && cb.checked) wrap.classList.remove('hidden');
    else wrap.classList.add('hidden');
}
function toggleModal(modalId) {
    const modal = document.getElementById(modalId);
    if (!modal) return;
    modal.classList.toggle('hidden');
    modal.classList.toggle('modal-active');
}
function previewImage(input, previewId, placeholderId) {
    const preview = document.getElementById(previewId);
    const placeholder = document.getElementById(placeholderId);
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = (e) => {
            preview.src = e.target.result;
            preview.classList.remove('hidden');
            placeholder.classList.add('hidden');
        };
        reader.readAsDataURL(input.files[0]);
    }
}
function openOverviewUserView(data) {
    document.getElementById('overviewUserName').textContent = data.name || '';
    document.getElementById('overviewUserEmp').textContent = data.employee_id || '—';
    document.getElementById('overviewUserEmail').textContent = data.email || '—';
    document.getElementById('overviewUserRole').textContent = data.role_label || '—';
    var parts = [];
    if (data.wing_label) parts.push(data.wing_label);
    if (data.department_label) parts.push(data.department_label);
    document.getElementById('overviewUserWingDept').textContent = parts.length ? parts.join(' • ') : '—';
    const img = document.getElementById('overviewUserAvatar');
    const initialsEl = document.getElementById('overviewUserInitials');
    if (data.avatar) {
        img.src = data.avatar;
        img.classList.remove('hidden');
        initialsEl.classList.add('hidden');
    } else {
        img.classList.add('hidden');
        initialsEl.textContent = data.initials || '?';
        initialsEl.classList.remove('hidden');
    }
    toggleModal('overviewUserModal');
}
function openFacultyView(data) {
    document.getElementById('viewFaName').textContent = data.name;
    document.getElementById('viewFaEmp').textContent = data.employee_id;
    document.getElementById('viewFaEmail').textContent = data.email;
    document.getElementById('viewFaDept').textContent = data.departments_display && data.departments_display !== '—' ? (data.departments_display + (data.wing_label ? ' (' + data.wing_label + ')' : '')) : (data.wing_label || '—');
    const img = document.getElementById('viewFaAvatar');
    const initialsEl = document.getElementById('viewFaInitials');
    if (data.avatar) {
        img.src = data.avatar;
        img.classList.remove('hidden');
        initialsEl.classList.add('hidden');
    } else {
        img.classList.add('hidden');
        initialsEl.textContent = data.initials;
        initialsEl.classList.remove('hidden');
    }
    toggleModal('viewFacultyModal');
}
function openFacultyEdit(data) {
    document.getElementById('editFacultyForm').action = data.updateUrl;
    document.getElementById('edit_fa_name').value = data.name;
    document.getElementById('edit_fa_employee_id_display').textContent = data.employee_id || '—';
    document.getElementById('edit_fa_email').value = data.email;
    var wingSel = document.getElementById('edit_fa_wing');
    if (wingSel) wingSel.value = data.wing || '';
    document.querySelectorAll('#editFacultyForm [name="departments[]"]').forEach(function (cb) {
        cb.checked = Array.isArray(data.departments) && data.departments.indexOf(cb.value) !== -1;
    });
    var otherFa = document.getElementById('edit_faculty_other_department_label');
    if (otherFa) otherFa.value = data.other_department_label || '';
    syncEditFacultyOtherDeptWrap();
    document.getElementById('edit_fa_title').value = data.title || '';
    document.getElementById('edit_fa_password').value = '';
    document.getElementById('editFaAvatar').value = '';
    var editFaAvatarPreview = document.getElementById('editFaAvatarPreview');
    var editFaInitials = document.getElementById('eInitials');
    if (data.avatar) {
        editFaAvatarPreview.src = data.avatar;
        editFaAvatarPreview.classList.remove('hidden');
        editFaInitials.classList.add('hidden');
    } else {
        editFaAvatarPreview.src = '';
        editFaAvatarPreview.classList.add('hidden');
        editFaInitials.classList.remove('hidden');
    }
    editFaInitials.textContent = data.name.split(/\s+/).filter(Boolean).slice(0, 2).map(function (p) { return p[0]; }).join('').toUpperCase();
    toggleModal('editFacultyModal');
}
function openFacultyDelete(data) {
    document.getElementById('deleteFacultyForm').action = data.destroyUrl;
    document.getElementById('deleteFacultyName').textContent = data.name;
    toggleModal('deleteFacultyModal');
}
</script>
@endpush
