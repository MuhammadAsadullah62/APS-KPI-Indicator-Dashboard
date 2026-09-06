<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Enums\Wing;
use App\Http\Requests\StoreFacultyRequest;
use App\Http\Requests\UpdateFacultyRequest;
use App\Models\Media;
use App\Models\User;
use App\Support\AvatarService;
use App\Support\InstitutionalEmployeeId;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class FacultyController extends Controller
{
    public function index(Request $request): View
    {
        $query = User::role(UserRole::Faculty->value)
            ->with(['avatarMedia', 'assignedDepartments'])
            ->orderBy('name');

        if ($request->user()?->isSectionHead()) {
            $headWing = $request->user()->wing?->value;
            if ($headWing) {
                $query->where('wing', $headWing);
            } else {
                $query->whereRaw('0 = 1');
            }
        }

        $facultyMembers = $query->get();

        return view('faculty.index', [
            'facultyMembers' => $facultyMembers,
        ]);
    }

    public function store(StoreFacultyRequest $request): RedirectResponse
    {
        $wing = $request->user()->isSectionHead()
            ? $request->user()->wing
            : $request->enum('wing', Wing::class);

        abort_if($request->user()->isSectionHead() && ! $wing, 403);

        $employeeId = InstitutionalEmployeeId::next($wing, false);

        $departmentValues = $request->normalizedDepartments();

        $hasOther = $request->hasOtherDepartment();

        $user = User::create([
            'name' => $request->string('name')->toString(),
            'employee_id' => $employeeId,
            'email' => $request->string('email')->toString(),
            'password' => Hash::make($request->string('password')->toString()),
            'wing' => $wing,
            'other_department_label' => $hasOther ? trim($request->string('other_department_label')->toString()) : null,
            'title' => $request->filled('title') ? $request->string('title')->toString() : null,
        ]);

        $user->assignRole(UserRole::Faculty->value);
        $user->syncDepartments($departmentValues);

        AvatarService::replaceFor($user, $request->file('avatar'));

        return redirect()->route('teachermanagement')->with('status', 'Faculty member registered. Employee ID: '.$user->employee_id);
    }

    public function update(UpdateFacultyRequest $request, User $user): RedirectResponse
    {
        abort_unless($user->isFaculty(), 404);

        if ($request->user()->isSectionHead()) {
            abort_unless(
                $user->wing?->value === $request->user()->wing?->value,
                403
            );
        }

        $wing = $request->user()->isSectionHead()
            ? $request->user()->wing
            : $request->enum('wing', Wing::class);

        abort_if($request->user()->isSectionHead() && ! $wing, 403);

        $departmentValues = $request->normalizedDepartments();

        $hasOther = $request->hasOtherDepartment();

        $data = [
            'name' => $request->string('name')->toString(),
            'email' => $request->string('email')->toString(),
            'wing' => $wing,
            'other_department_label' => $hasOther ? trim($request->string('other_department_label')->toString()) : null,
            'title' => $request->filled('title') ? $request->string('title')->toString() : null,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->string('password')->toString());
        }

        $user->update($data);

        $user->syncDepartments($departmentValues);

        AvatarService::replaceFor($user, $request->file('avatar'));

        return redirect()->route('teachermanagement')->with('status', 'Faculty record updated.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        abort_unless($user->isFaculty(), 404);

        if ($request->user()->isSectionHead()) {
            abort_unless(
                $user->wing?->value === $request->user()->wing?->value,
                403
            );
        }

        $user->mediaItems()->get()->each(fn (Media $m) => $m->deleteWithFile());
        $user->delete();

        return redirect()->route('teachermanagement')->with('status', 'Faculty member removed.');
    }
}
