<?php

namespace App\Support;

use App\Enums\UserRole;

/**
 * Single source of truth for the role → permission matrix.
 *
 * The {@see \Database\Seeders\RolePermissionSeeder} builds the Spatie
 * roles/permissions from this map, and {@see \Tests\Feature\AuthorizationTest}
 * asserts the HTTP layer matches it. Wing- and ownership-scoped rules that a
 * flat permission cannot express live in {@see \App\Models\User} and the
 * policies in {@see \App\Policies}.
 */
final class Rbac
{
    /**
     * Every permission the app checks, grouped only for readability.
     *
     * @var list<string>
     */
    public const PERMISSIONS = [
        // Dashboard + shared pages
        'dashboard.view',
        'reports.view',
        'metricpages.view',        // quantitative / qualitative observation pages
        // System settings
        'settings.view',
        'settings.overview',       // the leadership stats + recent users panel
        'settings.updateOwnAvatar',
        'settings.updateOwnProfile',
        // Section head directory
        'sectionheads.view',
        'sectionheads.manage',
        'sectionheads.delete',
        // Faculty directory
        'faculty.view',
        'faculty.manage',
        'faculty.delete',
        // Observations
        'observations.view',
        'observations.record',
        // Admin
        'adminpanel.view',
        'pulse.view',
    ];

    /**
     * Role → permissions. Mirrors the pre-Spatie behaviour exactly.
     *
     * @return array<string, list<string>>
     */
    public static function matrix(): array
    {
        $leadership = [
            'dashboard.view',
            'reports.view',
            'settings.view',
            'settings.overview',
            'sectionheads.view',
            'sectionheads.manage',
            'sectionheads.delete',
            'faculty.view',
            'faculty.manage',
            'faculty.delete',
            'observations.view',
            'observations.record',
            'adminpanel.view',
            'pulse.view',
        ];

        return [
            // Admin has the full leadership set plus the admin panel / pulse.
            // Leadership is deliberately redirected away from the quant/qual
            // metric pages, so 'metricpages.view' is NOT granted here.
            UserRole::Admin->value => $leadership,
            UserRole::Principal->value => $leadership,
            UserRole::SectionHead->value => [
                'dashboard.view',
                'reports.view',
                'metricpages.view',
                'settings.view',
                'settings.updateOwnAvatar',
                'settings.updateOwnProfile',
                'sectionheads.view',
                'faculty.view',
                'faculty.manage',
                'faculty.delete',
                'observations.view',
                'observations.record',
            ],
            UserRole::Faculty->value => [
                'dashboard.view',
                'reports.view',
                'metricpages.view',
                'settings.view',
                'settings.updateOwnAvatar',
                'settings.updateOwnProfile',
            ],
        ];
    }
}
