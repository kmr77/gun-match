<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminActivityLog;
use App\Models\User;
use Illuminate\Contracts\View\View;

class MemberDetailsController extends Controller
{
    public function __invoke(User $user): View
    {
        $activityLogs = AdminActivityLog::query()
            ->with(['actor:id,name,email'])
            ->where('target_user_id', $user->id)
            ->latest('id')
            ->limit(50)
            ->get();

        return view('pages.admin-member-details', compact('user', 'activityLogs'));
    }
}
