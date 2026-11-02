<?php

namespace App\Http\Controllers;

use App\Models\AbuseReport;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MemberReportController extends Controller
{
    public function create(): View
    {
        return view('pages.report-create', [
            'types' => AbuseReport::TYPE_LABELS,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'type' => ['required', Rule::in(array_keys(AbuseReport::TYPE_LABELS))],
            'target_information' => ['required', 'string', 'max:255'],
            'details' => ['required', 'string', 'max:10000'],
        ], [
            'type.required' => '通報の種類を選択してください。',
            'type.in' => '有効な通報の種類を選択してください。',
            'target_information.required' => '通報対象の情報を入力してください。',
            'target_information.max' => '通報対象の情報は255文字以内で入力してください。',
            'details.required' => '通報理由・詳細を入力してください。',
            'details.max' => '通報理由・詳細は10,000文字以内で入力してください。',
        ]);

        AbuseReport::create([
            ...$validated,
            'reporter_user_id' => $request->user()->id,
        ]);

        return redirect()->route('mypage.reports.create')
            ->with('status', '通報を受け付けました。運営が内容を確認します。');
    }
}
