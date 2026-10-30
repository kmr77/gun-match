<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactInquiry;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ContactInquiryController extends Controller
{
    public function index(): View
    {
        $inquiries = ContactInquiry::query()->latest('id')->paginate(20);

        return view('pages.admin-contact-inquiries', compact('inquiries'));
    }

    public function show(ContactInquiry $contactInquiry): View
    {
        return view('pages.admin-contact-inquiry', compact('contactInquiry'));
    }

    public function update(Request $request, ContactInquiry $contactInquiry): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['pending', 'in_progress', 'completed'])],
        ]);

        $contactInquiry->status = $validated['status'];
        $contactInquiry->responded_at = $validated['status'] === 'completed'
            ? ($contactInquiry->responded_at ?? now())
            : null;
        $contactInquiry->save();

        return redirect()->route('admin.contact-inquiries.show', $contactInquiry)
            ->with('status', 'お問い合わせの対応状態を更新しました。');
    }
}
