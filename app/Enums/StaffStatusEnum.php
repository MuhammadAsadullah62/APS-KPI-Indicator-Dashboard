<?php

namespace App\Enums;

enum StaffStatusEnum: string
{
    case OnTrack = 'on_track';

    case OffTrack = 'off_track';

    case AtRisk = 'at_risk';

    public function label(): string
    {
        return match ($this) {
            self::OnTrack => 'On track',
            self::OffTrack => 'Off track',
            self::AtRisk => 'At risk',
        };
    }

    public function chipClasses(): string
    {
        return match ($this) {
            self::OnTrack => 'border-emerald-100/80 bg-emerald-50 text-emerald-800',
            self::OffTrack => 'border-amber-100/80 bg-amber-50 text-amber-900',
            self::AtRisk => 'border-rose-100/80 bg-rose-50 text-rose-800',
        };
    }

    public static function fromAveragePercent(?float $percent): ?self
    {
        if ($percent === null) { return null; }
        if ($percent >= 85.0) { return self::OnTrack; }
        if ($percent >= 70.0) { return self::OffTrack; }
        return self::AtRisk;
    }
}
