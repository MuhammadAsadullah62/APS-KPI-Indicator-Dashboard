@extends('layouts.app')

@section('title', 'APSACS Khanewal | Settings')

@section('header')
    <x-dashboard.page-header title="Settings" subtitle="Update your name, email, password, and profile photo. Assignment details are set by administration." />
@endsection

@section('content')
    @php
        $u = $profileUser;
    @endphp

    @if (session('status'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-900">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
        <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-900">
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="post" action="{{ route('systemsettings.profile') }}" enctype="multipart/form-data" class="space-y-8">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 xl:grid-cols-12 gap-8 items-start">
            {{-- Profile photo --}}
            <section class="xl:col-span-4 bg-white rounded-[2rem] border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-8 py-6 border-b border-slate-100 bg-slate-50/60">
                    <h2 class="text-lg font-black text-slate-800 tracking-tight">Profile photo</h2>
                    <p class="mt-1 text-sm font-semibold text-slate-500">Shown on rankings, directories, and your menu.</p>
                </div>
                <div class="p-8 flex flex-col items-center text-center gap-6">
                    <div class="relative">
                        <div id="staffSettingsAvatarPlaceholder" class="w-40 h-40 rounded-[2rem] bg-slate-100 flex items-center justify-center text-slate-500 font-black text-4xl border border-slate-200 {{ $u->avatarUrl() ? 'hidden' : '' }}">{{ $u->initials() }}</div>
                        <img id="staffSettingsAvatarPreview" src="{{ $u->avatarUrl() ?: '' }}" alt="" class="{{ $u->avatarUrl() ? '' : 'hidden' }} w-40 h-40 rounded-[2rem] object-cover border border-slate-200 shadow-md"
                            onerror="this.onerror=null;this.classList.add('hidden');document.getElementById('staffSettingsAvatarPlaceholder').classList.remove('hidden');">
                    </div>
                    <div class="w-full space-y-3">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest text-left">Upload image</label>
                        <input type="file" name="avatar" accept="image/*" class="block w-full text-sm font-semibold text-slate-600 file:mr-4 file:py-3 file:px-5 file:rounded-2xl file:border-0 file:text-sm file:font-black file:bg-slate-900 file:text-white hover:file:bg-aps-green file:cursor-pointer cursor-pointer" onchange="previewStaffSettingsAvatar(this)">
                        <p class="text-xs font-semibold text-slate-400 text-left">JPG, PNG, or WebP · max 4&nbsp;MB. Optional — leave empty to keep the current photo.</p>
                    </div>
                </div>
            </section>

            {{-- Editable account + read-only assignment --}}
            <div class="xl:col-span-8 space-y-8">
                <section class="bg-white rounded-[2rem] border border-slate-200 shadow-sm overflow-hidden">
                    <div class="px-8 py-6 border-b border-slate-100 bg-slate-50/60">
                        <h2 class="text-lg font-black text-slate-800 tracking-tight">Account details</h2>
                        <p class="mt-1 text-sm font-semibold text-slate-500">You can correct your name spelling, email, and password.</p>
                    </div>
                    <div class="p-8 space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="md:col-span-2">
                                <label for="staff_settings_name" class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 block">Full name</label>
                                <input id="staff_settings_name" type="text" name="name" value="{{ old('name', $u->name) }}" required maxlength="255" autocomplete="name" class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-semibold text-slate-900 outline-none focus:border-aps-green focus:bg-white transition-colors">
                            </div>
                            <div class="md:col-span-2">
                                <label for="staff_settings_email" class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 block">Email</label>
                                <input id="staff_settings_email" type="email" name="email" value="{{ old('email', $u->email) }}" required maxlength="255" autocomplete="email" class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-semibold text-slate-900 outline-none focus:border-aps-green focus:bg-white transition-colors">
                            </div>
                        </div>

                        <div class="pt-6 border-t border-slate-100">
                            <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-4">Change password</h3>
                            <p class="text-sm font-semibold text-slate-500 mb-5">Leave blank to keep your current password.</p>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="md:col-span-2">
                                    <label for="staff_settings_current_password" class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 block">Current password</label>
                                    <input id="staff_settings_current_password" type="password" name="current_password" autocomplete="current-password" class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-semibold text-slate-900 outline-none focus:border-aps-green focus:bg-white transition-colors">
                                </div>
                                <div>
                                    <label for="staff_settings_password" class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 block">New password</label>
                                    <input id="staff_settings_password" type="password" name="password" autocomplete="new-password" minlength="8" class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-semibold text-slate-900 outline-none focus:border-aps-green focus:bg-white transition-colors">
                                </div>
                                <div>
                                    <label for="staff_settings_password_confirmation" class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 block">Confirm new password</label>
                                    <input id="staff_settings_password_confirmation" type="password" name="password_confirmation" autocomplete="new-password" minlength="8" class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-semibold text-slate-900 outline-none focus:border-aps-green focus:bg-white transition-colors">
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="bg-white rounded-[2rem] border border-slate-200 shadow-sm overflow-hidden">
                    <div class="px-8 py-6 border-b border-slate-100 bg-slate-50/60">
                        <h2 class="text-lg font-black text-slate-800 tracking-tight">Assignment</h2>
                        <p class="mt-1 text-sm font-semibold text-slate-500">Read-only — contact administration to change these.</p>
                    </div>
                    <div class="p-8">
                        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <div class="rounded-2xl border border-slate-100 bg-slate-50/80 px-5 py-4">
                                <dt class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Employee ID</dt>
                                <dd class="mt-2 text-sm font-black text-slate-800">{{ $u->employee_id ?: '—' }}</dd>
                            </div>
                            <div class="rounded-2xl border border-slate-100 bg-slate-50/80 px-5 py-4">
                                <dt class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Role</dt>
                                <dd class="mt-2 text-sm font-black text-slate-800">{{ $u->roleLabel() }}</dd>
                            </div>
                            <div class="rounded-2xl border border-slate-100 bg-slate-50/80 px-5 py-4">
                                <dt class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Wing</dt>
                                <dd class="mt-2 text-sm font-black text-slate-800">{{ $u->wing?->label() ?? '—' }}</dd>
                            </div>
                            <div class="rounded-2xl border border-slate-100 bg-slate-50/80 px-5 py-4 sm:col-span-2">
                                <dt class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Department</dt>
                                <dd class="mt-2 text-sm font-black text-slate-800">{{ $u->departmentsLabelForDisplay() }}</dd>
                            </div>
                        </dl>
                    </div>
                </section>

                <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-end gap-3">
                    <button type="submit" class="inline-flex items-center justify-center px-10 py-4 rounded-2xl bg-aps-green text-white text-sm font-black uppercase tracking-widest shadow-lg hover:bg-emerald-900 transition-colors">
                        Save settings
                    </button>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
<script>
function previewStaffSettingsAvatar(input) {
    var preview = document.getElementById('staffSettingsAvatarPreview');
    var placeholder = document.getElementById('staffSettingsAvatarPlaceholder');
    if (!input.files || !input.files[0] || !preview) return;
    var reader = new FileReader();
    reader.onload = function (e) {
        preview.src = e.target.result;
        preview.classList.remove('hidden');
        if (placeholder) placeholder.classList.add('hidden');
    };
    reader.readAsDataURL(input.files[0]);
}
</script>
@endpush
