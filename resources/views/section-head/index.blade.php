@extends('layouts.app')

@section('title', 'APSACS Khanewal | Section Heads Management')

@push('styles')
<style>
    .modal-active { align-items: center; justify-content: center; }
</style>
@endpush

@section('header')
    <x-dashboard.page-header title="SectionHead Hub">
        <x-slot name="actions">
            @can('sectionheads.manage')
            <button type="button"
                @if($canRegisterSectionHead) onclick="toggleModal('createSecHeadModal')" @endif
                @disabled(! $canRegisterSectionHead)
                title="{{ $canRegisterSectionHead ? '' : 'Every wing already has a section head.' }}"
                class="bg-aps-green text-white px-6 py-2.5 rounded-xl font-black text-xs shadow-lg shadow-emerald-900/20 hover:bg-emerald-900 transition-all flex items-center gap-2 uppercase tracking-widest disabled:opacity-40 disabled:cursor-not-allowed disabled:hover:bg-aps-green">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                Register SectionHead
            </button>
            @endcan
        </x-slot>
    </x-dashboard.page-header>
@endsection

@section('content')
@php
    use App\Enums\Department;
    $sectionHeadOtherDeptValue = Department::Other->value;
@endphp
            @if (session('status'))
                <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-900">{{ session('status') }}</div>
            @endif
            @if ($errors->any())
                <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-800">{{ $errors->first() }}</div>
            @endif

            <div class="min-w-0 overflow-hidden rounded-2xl border border-slate-200 bg-white font-semibold shadow-sm sm:rounded-[2.5rem]">
                <div class="border-b border-slate-100 bg-slate-50/50 p-4 sm:p-6 md:p-8">
                    <h3 class="text-lg font-black uppercase tracking-tight text-slate-800 sm:text-xl">Active Profiles</h3>
                </div>
                @if($sectionHeads->isEmpty())
                    <p class="px-4 py-10 text-center font-semibold text-slate-400 sm:px-6 sm:py-12">No section heads registered yet.</p>
                @else
                    <div class="space-y-3 p-4 md:hidden">
                        @foreach ($sectionHeads as $row)
                            @include('section-head.partials.directory-row-mobile-card', ['row' => $row, 'sectionHeads' => $sectionHeads])
                        @endforeach
                    </div>
                    <div class="hidden overflow-x-auto md:block">
                        <table class="w-full border-collapse text-left">
                            <thead>
                                <tr class="bg-slate-50/30 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">
                                    <th class="px-3 py-3 sm:px-4 sm:py-4 md:px-8 md:py-5">SecHead</th>
                                    <th class="px-3 py-3 sm:px-4 sm:py-4 md:px-8 md:py-5">Wing Assignment</th>
                                    <th class="px-3 py-3 sm:px-4 sm:py-4 md:px-8 md:py-5">Departments</th>
                                    <th class="px-3 py-3 text-right sm:px-4 sm:py-4 md:px-8 md:py-5">Directory Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach ($sectionHeads as $row)
                                    @include('section-head.partials.directory-row-table', ['row' => $row, 'sectionHeads' => $sectionHeads])
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
@endsection

@push('modals')
@include('section-head.partials.modals')
@endpush

@push('scripts')
<script>
var SECTION_HEAD_OTHER_DEPT = @json($sectionHeadOtherDeptValue);
function syncCreateOtherDeptWrap() {
    var wrap = document.getElementById('createOtherDeptWrap');
    if (!wrap) return;
    var cb = document.querySelector('#createSecHeadModal input[name="departments[]"][value="' + SECTION_HEAD_OTHER_DEPT + '"]');
    if (cb && cb.checked) wrap.classList.remove('hidden');
    else wrap.classList.add('hidden');
}
function syncEditOtherDeptWrap() {
    var wrap = document.getElementById('editOtherDeptWrap');
    if (!wrap) return;
    var cb = document.querySelector('#editSecHeadForm input[name="departments[]"][value="' + SECTION_HEAD_OTHER_DEPT + '"]');
    if (cb && cb.checked) wrap.classList.remove('hidden');
    else wrap.classList.add('hidden');
}
function toggleModal(modalId) {
    const modal = document.getElementById(modalId);
    modal.classList.toggle('hidden');
    modal.classList.toggle('modal-active');
    if (modalId === 'createSecHeadModal' && !modal.classList.contains('hidden')) {
        updateCreateSecHeadIdPreview();
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
function openSecHeadView(data) {
    document.getElementById('viewShName').textContent = data.name;
    document.getElementById('viewShEmp').textContent = data.employee_id;
    document.getElementById('viewShEmail').textContent = data.email;
    document.getElementById('viewShWing').textContent = data.wing_label || '—';
    document.getElementById('viewShDepartments').textContent = data.departments_display || '—';
    const img = document.getElementById('viewShAvatar');
    const initialsEl = document.getElementById('viewShInitials');
    if (data.avatar) {
        img.src = data.avatar;
        img.classList.remove('hidden');
        initialsEl.classList.add('hidden');
    } else {
        img.classList.add('hidden');
        initialsEl.textContent = data.initials;
        initialsEl.classList.remove('hidden');
    }
    toggleModal('viewSecHeadModal');
}
function openSecHeadEdit(data) {
    document.getElementById('editSecHeadForm').action = data.updateUrl;
    document.getElementById('edit_sh_name').value = data.name;
    document.getElementById('edit_sh_employee_id_display').textContent = data.employee_id || '—';
    document.getElementById('edit_sh_email').value = data.email;
    const wingSelect = document.getElementById('edit_sh_wing');
    wingSelect.innerHTML = '';
    (data.allowedWings || []).forEach(function (o) {
        const opt = document.createElement('option');
        opt.value = o.value;
        opt.textContent = o.label;
        wingSelect.appendChild(opt);
    });
    wingSelect.value = data.wing || '';
    document.getElementById('edit_sh_title').value = data.title || '';
    document.querySelectorAll('#editSecHeadForm [name="departments[]"]').forEach(function (cb) {
        cb.checked = Array.isArray(data.departments) && data.departments.indexOf(cb.value) !== -1;
    });
    var otherInput = document.getElementById('edit_other_department_label');
    if (otherInput) otherInput.value = data.other_department_label || '';
    syncEditOtherDeptWrap();
    document.getElementById('edit_sh_password').value = '';
    document.getElementById('editAvatar').value = '';
    const editAvatarPreview = document.getElementById('editAvatarPreview');
    const editInitials = document.getElementById('editInitials');
    if (data.avatar) {
        editAvatarPreview.src = data.avatar;
        editAvatarPreview.classList.remove('hidden');
        editInitials.classList.add('hidden');
    } else {
        editAvatarPreview.src = '';
        editAvatarPreview.classList.add('hidden');
        editInitials.classList.remove('hidden');
    }
    editInitials.textContent = data.name.split(/\s+/).filter(Boolean).slice(0, 2).map(function (p) { return p[0]; }).join('').toUpperCase();
    toggleModal('editSecHeadModal');
}
function openSecHeadDelete(data) {
    document.getElementById('deleteSecHeadForm').action = data.destroyUrl;
    document.getElementById('deleteSecHeadName').textContent = data.name;
    toggleModal('deleteSecHeadModal');
}
var INSTITUTIONAL_WING_PREFIX = { pre_junior: 'PRE', middle: 'M', senior: 'SE' };
function updateCreateSecHeadIdPreview() {
    var sel = document.getElementById('createSecHeadWing');
    var el = document.getElementById('createSecHeadIdPreview');
    if (!el) return;
    if (!sel || !sel.value || !INSTITUTIONAL_WING_PREFIX[sel.value]) {
        el.textContent = '—';
        return;
    }
    el.textContent = INSTITUTIONAL_WING_PREFIX[sel.value] + '-SEC-0001';
}
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('#createSecHeadModal input[name="departments[]"]').forEach(function (el) {
        el.addEventListener('change', syncCreateOtherDeptWrap);
    });
    document.querySelectorAll('#editSecHeadForm input[name="departments[]"]').forEach(function (el) {
        el.addEventListener('change', syncEditOtherDeptWrap);
    });
    syncCreateOtherDeptWrap();
    var shWing = document.getElementById('createSecHeadWing');
    if (shWing) {
        shWing.addEventListener('change', updateCreateSecHeadIdPreview);
        updateCreateSecHeadIdPreview();
    }
});
</script>
@endpush
