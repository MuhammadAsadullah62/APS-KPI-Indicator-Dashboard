<?php

namespace App\Models;

use App\Enums\Department;
use App\Enums\UserRole;
use App\Enums\Wing;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, HasRoles, Notifiable;

    /**
     * Keep the Spatie role in lock-step with the `role` enum column, which
     * stays the single "primary role" the UI and factories depend on.
     */
    protected static function booted(): void
    {
        static::saved(function (User $user): void {
            if (! $user->wasRecentlyCreated && ! $user->wasChanged('role')) {
                return;
            }

            $value = $user->role instanceof UserRole ? $user->role->value : (string) $user->role;
            if ($value === '') {
                return;
            }

            try {
                if ($user->roles->pluck('name')->all() !== [$value]) {
                    $user->syncRoles([$value]);
                }
            } catch (\Spatie\Permission\Exceptions\RoleDoesNotExist) {
                // Roles not seeded yet (e.g. a bare test DB) — nothing to sync.
            } catch (\Illuminate\Database\QueryException) {
                // Permission tables not migrated yet (mid-deploy window) — skip.
            }
        });
    }

    protected $fillable = [
        'name',
        'email',
        'employee_id',
        'role',
        'title',
        'password',
        'wing',
        'department',
        'other_department_label',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'wing' => Wing::class,
            'department' => Department::class,
        ];
    }

    public function assignedDepartments(): HasMany
    {
        return $this->hasMany(UserDepartment::class);
    }

    /**
     * Multi-department assignments (section heads and faculty) via `user_departments`.
     *
     * @return Attribute<list<string>, never>
     */
    protected function departments(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->relationLoaded('assignedDepartments')) {
                    return $this->assignedDepartments->pluck('department')->values()->all();
                }

                return $this->assignedDepartments()->pluck('department')->all();
            },
        );
    }

    /**
     * @param  list<string>  $departmentValues
     */
    public function syncDepartments(array $departmentValues): void
    {
        $this->assignedDepartments()->delete();
        foreach ($departmentValues as $value) {
            if (! is_string($value) || $value === '') {
                continue;
            }
            $this->assignedDepartments()->create(['department' => $value]);
        }
    }

    public function mediaItems(): MorphMany
    {
        return $this->morphMany(Media::class, 'mediable');
    }

    public function avatarMedia(): MorphOne
    {
        return $this->morphOne(Media::class, 'mediable')
            ->where('collection_name', 'avatar')
            ->latestOfMany();
    }

    public function observationsReceived(): HasMany
    {
        return $this->hasMany(Observation::class, 'observee_id');
    }

    public function observationsConducted(): HasMany
    {
        return $this->hasMany(Observation::class, 'observer_id');
    }

    public function canOpenObservationsPortalForObservee(User $observee): bool
    {
        return $this->canObserveUser($observee);
    }

    public function initials(): string
    {
        $parts = preg_split('/\s+/', trim($this->name)) ?: [];

        $letters = '';
        foreach (array_slice($parts, 0, 2) as $part) {
            $letters .= Str::upper(Str::substr($part, 0, 1));
        }

        return $letters !== '' ? $letters : '?';
    }

    public function avatarUrl(): ?string
    {
        $media = $this->relationLoaded('avatarMedia')
            ? $this->getRelation('avatarMedia')
            : $this->avatarMedia()->first();

        return $media instanceof Media ? $media->url() : null;
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    public function isPrincipal(): bool
    {
        if ($this->role instanceof UserRole) {
            return $this->role === UserRole::Principal;
        }

        $raw = $this->getRawOriginal('role');

        return is_string($raw) && UserRole::tryFrom($raw) === UserRole::Principal;
    }

    public function isSectionHead(): bool
    {
        return $this->role === UserRole::SectionHead;
    }

    public function isFaculty(): bool
    {
        return $this->role === UserRole::Faculty;
    }

    public function canAccessQuantQualObservationPages(): bool
    {
        return $this->can('metricpages.view');
    }

    public function departmentsLabelForDisplay(): string
    {
        if ($this->isFaculty() || $this->isSectionHead()) {
            $labels = collect($this->departments)->map(function ($v) {
                if (! is_string($v)) {
                    return null;
                }
                if ($v === Department::Other->value) {
                    return filled($this->other_department_label)
                        ? 'Other ('.$this->other_department_label.')'
                        : Department::Other->label();
                }

                return Department::tryFrom($v)?->label();
            })->filter()->values();

            if ($labels->isNotEmpty()) {
                return $labels->implode(', ');
            }

            return $this->department?->label() ?? '—';
        }

        return $this->department?->label() ?? '—';
    }

    public function canAccessSystemSettings(): bool
    {
        return $this->can('settings.view');
    }

    public function canViewSystemSettingsOverview(): bool
    {
        return $this->can('settings.overview');
    }

    public function canAccessObservations(): bool
    {
        return $this->can('observations.view')
            && (! $this->isSectionHead() || $this->wing !== null);
    }

    public function canObserveUser(User $observee): bool
    {
        if (! $this->can('observations.record')) {
            return false;
        }

        if ($this->isAdmin() || $this->isPrincipal()) {
            return $observee->isSectionHead() || $observee->isFaculty();
        }

        if ($this->isSectionHead() && $this->wing !== null) {
            return $observee->isFaculty()
                && $observee->wing === $this->wing;
        }

        return false;
    }

    public function canEditObservation(\App\Models\Observation $observation): bool
    {
        if ($this->isAdmin() || $this->isPrincipal()) {
            return true;
        }

        return $this->id === $observation->observer_id;
    }

    public static function departmentValuesAssignedToSectionHeads(): Collection
    {
        $staffIds = static::query()
            ->whereIn('role', [UserRole::SectionHead, UserRole::Faculty])
            ->pluck('id');

        return UserDepartment::query()
            ->whereIn('user_id', $staffIds)
            ->pluck('department')
            ->filter(fn ($v) => is_string($v) && $v !== '')
            ->unique()
            ->values();
    }

    public static function departmentsForObservationContext(): Collection
    {
        $values = static::departmentValuesAssignedToSectionHeads();
        $departments = $values
            ->map(fn (string $v) => Department::tryFrom($v))
            ->filter()
            ->unique(fn (Department $d) => $d->value)
            ->sortBy(fn (Department $d) => $d->label())
            ->values();

        if ($departments->isEmpty()) {
            return collect(Department::cases())->sortBy(fn (Department $d) => $d->label())->values();
        }

        return $departments;
    }

    public static function observationDepartmentAllowedValues(): array
    {
        return static::departmentsForObservationContext()
            ->map(fn (Department $d) => $d->value)
            ->values()
            ->all();
    }
}
