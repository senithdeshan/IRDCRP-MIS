<?php

namespace App\View\Components;

use App\Models\StaffProfile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\Component;
use Illuminate\View\View;

class AppLayout extends Component
{
    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        $layoutUser = Auth::user();
        $layoutStaffProfile = $layoutUser?->staffProfile;

        // Older accounts can still use the profile recorded against their official email.
        if ($layoutUser && ! $layoutStaffProfile) {
            $layoutStaffProfile = StaffProfile::query()
                ->whereNull('user_id')
                ->whereRaw('LOWER(official_email) = ?', [Str::lower(trim($layoutUser->email))])
                ->orderBy('id')
                ->first();
        }

        $layoutUserName = Str::squish((string) ($layoutStaffProfile?->name_with_initials
            ?: ($layoutUser?->isProtectedSuperAdmin() ? 'Super Admin' : $layoutUser?->name)));

        if ($layoutUserName === Str::lower($layoutUserName)) {
            $layoutUserName = Str::title($layoutUserName);
        }

        $layoutStaffType = $layoutStaffProfile?->staff_type
            ?: ($layoutUser?->isProtectedSuperAdmin() ? 'Super Administrator' : ($layoutUser?->designation
                ?: Str::headline($layoutUser?->role ?? 'Staff')));
        $layoutUserInitials = Str::of($layoutUserName)->explode(' ')->filter()
            ->map(fn (string $part) => Str::upper(Str::substr($part, 0, 1)))
            ->take(2)->implode('') ?: 'U';
        $layoutUserPhotoUrl = $layoutStaffProfile?->photo_path && Storage::disk('local')->exists($layoutStaffProfile->photo_path)
            ? route('staff-profiles.document', [$layoutStaffProfile, 'photo', 'v' => $layoutStaffProfile->updated_at?->timestamp])
            : null;

        return view('layouts.app', compact(
            'layoutUser', 'layoutUserName', 'layoutStaffType', 'layoutUserInitials', 'layoutUserPhotoUrl',
        ));
    }
}
