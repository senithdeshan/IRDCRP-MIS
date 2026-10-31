<?php

namespace App\Http\Controllers;

use App\Models\StaffProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    public function index(Request $request): View
    {
        $profiles = StaffProfile::query()->orderBy('id')->get();
        $usersByEmail = User::query()->whereNotNull('email')->get()->keyBy(fn (User $user) => mb_strtolower($user->email));

        return view('user-management.index', [
            'profiles' => $profiles,
            'usersByEmail' => $usersByEmail,
            'canManageUsers' => $request->user()->isSuperAdmin() || in_array($request->user()->role, ['admin', 'administrator'], true),
        ]);
    }
}
