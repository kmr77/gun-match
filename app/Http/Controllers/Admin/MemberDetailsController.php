<?php

namespace App\Http\Controllers\Admin;

use App\Enums\MemberType;
use App\Enums\MemberVerificationStatus;
use App\Enums\MemberVerificationType;
use App\Http\Controllers\Controller;
use App\Models\AdminActivityLog;
use App\Models\User;
use Illuminate\Contracts\View\View;

class MemberDetailsController extends Controller
{
    public function __invoke(User $user): View
    {
        $user->load('reviewedBy:id,name');
        $activityLogs = AdminActivityLog::query()
            ->with(['actor:id,name,email'])
            ->where('target_user_id', $user->id)
            ->latest('id')
            ->limit(50)
            ->get();
        $verificationHistory = AdminActivityLog::query()
            ->with(['actor:id,name,email'])
            ->where('target_user_id', $user->id)
            ->where('action', 'member.verification_updated')
            ->latest('id')
            ->limit(50)
            ->get();

        $verificationStatuses = MemberVerificationStatus::cases();
        $verificationTypes = $user->member_type === MemberType::Individual
            ? [MemberVerificationType::Identity]
            : [MemberVerificationType::Business];

        return view('pages.admin-member-details', compact(
            'user',
            'activityLogs',
            'verificationHistory',
            'verificationStatuses',
            'verificationTypes',
        ));
    }
}
