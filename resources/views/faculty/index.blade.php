@extends('layouts.app')

@section('title', 'APSACS Khanewal | Faculty Management')

@push('styles')
<style>.modal-active { align-items: center; justify-content: center; }</style>
@endpush

@section('header')
    <x-dashboard.page-header title="Faculty Hub">
        <x-slot name="actions">
            @can('faculty.manage')
            @php
                $shCanRegister = auth()->user()->isAdmin() || auth()->user()->isPrincipal() || (auth()->user()->isSectionHead() && auth()->user()->wing);
            @endphp
            <button type="button"
                @if($shCanRegister) onclick="toggleModal('createFacultyModal')" @endif
                @unless($shCanRegister) disabled @endunless
                title="{{ auth()->user()->isSectionHead() && !auth()->user()->wing ? 'Your profile must have a wing assigned before you can register teachers.' : '' }}"
                class="bg-aps-green text-white px-6 py-2.5 rounded-xl font-black text-xs shadow-lg shadow-emerald-900/20 hover:bg-emerald-900 transition-all flex items-center gap-2 uppercase tracking-widest disabled:opacity-40 disabled:cursor-not-allowed disabled:hover:bg-aps-green">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                Register Teacher
            </button>
            @endcan
        </x-slot>
    </x-dashboard.page-header>
@endsection

@section('content')
            @php
                use App\Enums\Department;
                $facultyOtherDeptValue = Department::Other->value;
            @endphp
            @if (session('status'))
                <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-900">{{ session('status') }}</div>
            @endif
            @if ($errors->any())
                <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-800">{{ $errors->first() }}</div>
            @endif

            <x-directory.list
                heading="Active Faculty Directory"
                :rows="$facultyMembers"
                empty="No faculty members registered yet."
                :columns="['Instructor', 'Wing', 'Departments', 'Actions']"
                row-view="faculty.partials.directory-row-table"
                card-view="faculty.partials.directory-row-mobile-card" />
@endsection

@push('modals')
@include('faculty.partials.modals')
@endpush

@push('scripts')
<script>
var FACULTY_OTHER_DEPT = @json($facultyOtherDeptValue);
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
    modal.classList.toggle('hidden');
    modal.classList.toggle('modal-active');
    if (modalId === 'createFacultyModal' && !modal.classList.contains('hidden')) {
        updateCreateFacultyIdPreview();
        syncCreateFacultyOtherDeptWrap();
    }
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
var INSTITUTIONAL_WING_PREFIX = { pre_junior: 'PRE', middle: 'M', senior: 'SE' };
function updateCreateFacultyIdPreview() {
    var modal = document.getElementById('createFacultyModal');
    var el = modal ? modal.querySelector('.js-create-faculty-id-preview') : null;
    if (!el) return;
    var wingVal = '';
    if (modal && modal.dataset.shWing) {
        wingVal = modal.dataset.shWing;
    } else {
        var sel = document.getElementById('createFacultyWing');
        wingVal = sel ? sel.value : '';
    }
    if (!wingVal || !INSTITUTIONAL_WING_PREFIX[wingVal]) {
        el.textContent = '—';
        return;
    }
    el.textContent = INSTITUTIONAL_WING_PREFIX[wingVal] + '-TE-0001';
}
document.addEventListener('DOMContentLoaded', function () {
    var sel = document.getElementById('createFacultyWing');
    if (sel) sel.addEventListener('change', updateCreateFacultyIdPreview);
    updateCreateFacultyIdPreview();
    document.querySelectorAll('#createFacultyModal input[name="departments[]"]').forEach(function (el) {
        el.addEventListener('change', syncCreateFacultyOtherDeptWrap);
    });
    document.querySelectorAll('#editFacultyForm input[name="departments[]"]').forEach(function (el) {
        el.addEventListener('change', syncEditFacultyOtherDeptWrap);
    });
    syncCreateFacultyOtherDeptWrap();
});
</script>
@endpush
