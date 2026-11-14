<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Contracts\View\View;

class PublicMemberProfileController extends Controller
{
    public function show(User $user): View
    {
        abort_unless(
            $user->is_active && $user->hasVerifiedEmail() && $user->phone_verified_at !== null,
            404,
        );

        return view('pages.public-member-profile', compact('user'));
    }
}
