<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AbuseReport;
use App\Models\AdminActivityLog;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ReportController extends Controller
{
    public function index(): View
    {
        $reports = AbuseReport::query()
            ->with('reporter:id,name,email')
            ->latest('id')
            ->paginate(20);

        return view('pages.admin-reports', compact('reports'));
    }

    public function show(AbuseReport $report): View
    {
        $report->load(['reporter:id,name,email', 'handler:id,name,email']);

        return view('pages.admin-report', compact('report'));
    }

    public function update(Request $request, AbuseReport $report): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(array_keys(AbuseReport::STATUS_LABELS))],
            'admin_note' => ['nullable', 'string', 'max:10000'],
        ], [
            'status.required' => '対応状態を選択してください。',
            'status.in' => '有効な対応状態を選択してください。',
            'admin_note.max' => '管理者用メモは10,000文字以内で入力してください。',
        ]);

        DB::transaction(function () use ($request, $report, $validated): void {
            $lockedReport = AbuseReport::query()->lockForUpdate()->findOrFail($report->id);
            $previousStatus = $lockedReport->status;
            $memoChanged = $lockedReport->admin_note !== ($validated['admin_note'] ?? null);
            $actor = $request->user();

            $lockedReport->status = $validated['status'];
            $lockedReport->admin_note = $validated['admin_note'] ?? null;
            $lockedReport->handled_at = now();
            $lockedReport->handled_by_user_id = $actor->id;
            $lockedReport->save();

            AdminActivityLog::create([
                'actor_user_id' => $actor->id,
                'target_user_id' => $lockedReport->reporter_user_id,
                'action' => 'report.updated',
                'details' => [
                    'report_id' => $lockedReport->id,
                    'type' => $lockedReport->type,
                    'from_status' => $previousStatus,
                    'to_status' => $lockedReport->status,
                    'admin_note_changed' => $memoChanged,
                ],
                'ip_address' => $request->ip(),
            ]);
        });

        return redirect()->route('admin.reports.show', $report)
            ->with('status', '通報の対応内容を記録しました。');
    }
}
