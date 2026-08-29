<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Enums\Wing;
use App\Http\Requests\StoreSectionHeadRequest;
use App\Http\Requests\UpdateSectionHeadRequest;
use App\Models\Media;
use App\Models\User;
use App\Support\AvatarService;
use App\Support\InstitutionalEmployeeId;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class SectionHeadController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $sectionHeadsQuery = User::query()
            ->where('role', UserRole::SectionHead)
            ->with(['avatarMedia', 'assignedDepartments'])
            ->orderBy('name');

        if ($user->isSectionHead()) {
            $sectionHeadsQuery->where('wing', $user->wing?->value);
        }

        $sectionHeads = $sectionHeadsQuery->get();

        $wingValuesTakenBySectionHeads = $sectionHeads
            ->pluck('wing')
            ->filter()
            ->map(fn (?Wing $w) => $w->value)
            ->all();

        $availableWingsForCreate = collect(Wing::cases())->filter(
            fn (Wing $w) => ! in_array($w->value, $wingValuesTakenBySectionHeads, true)
        )->values();

        $canRegisterSectionHead = $availableWingsForCreate->isNotEmpty();

        return view('section-head.index', [
            'sectionHeads' => $sectionHeads,
            'availableWingsForCreate' => $availableWingsForCreate,
            'canRegisterSectionHead' => $canRegisterSectionHead,
        ]);
    }

    public function store(StoreSectionHeadRequest $request): RedirectResponse
    {
        $departmentValues = $request->normalizedDepartments();

        $hasOther = $request->hasOtherDepartment();

        $wing = $request->enum('wing', Wing::class);

        $user = User::create([
            'name' => $request->string('name')->toString(),
            'employee_id' => InstitutionalEmployeeId::next($wing, true),
            'email' => $request->string('email')->toString(),
            'password' => Hash::make($request->string('password')->toString()),
            'role' => UserRole::SectionHead,
            'wing' => $wing,
            'title' => $request->filled('title') ? $request->string('title')->toString() : null,
            'department' => null,
            'other_department_label' => $hasOther ? trim($request->string('other_department_label')->toString()) : null,
        ]);

        $user->syncDepartments($departmentValues);

        AvatarService::replaceFor($user, $request->file('avatar'));

        return redirect()->route('sechead')->with('status', 'Section head registered. Employee ID: '.$user->employee_id);
    }

    public function update(UpdateSectionHeadRequest $request, User $user): RedirectResponse
    {
        abort_unless($user->isSectionHead(), 404);

        $departmentValues = $request->normalizedDepartments();

        $hasOther = $request->hasOtherDepartment();

        $data = [
            'name' => $request->string('name')->toString(),
            'email' => $request->string('email')->toString(),
            'wing' => $request->enum('wing', Wing::class),
            'title' => $request->filled('title') ? $request->string('title')->toString() : null,
            'other_department_label' => $hasOther ? trim($request->string('other_department_label')->toString()) : null,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->string('password')->toString());
        }

        $user->update($data);

        $user->syncDepartments($departmentValues);

        AvatarService::replaceFor($user, $request->file('avatar'));

        return redirect()->route('sechead')->with('status', 'Section head updated.');
    }

    public function destroy(User $user): RedirectResponse
    {
        abort_unless($user->isSectionHead(), 404);

        $user->mediaItems()->get()->each(fn (Media $m) => $m->deleteWithFile());
        $user->delete();

        return redirect()->route('sechead')->with('status', 'Section head removed.');
    }
}
