<?php

namespace Tests\Feature;

use App\Models\ContactInquiry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactInquiryTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_submit_a_general_site_inquiry(): void
    {
        $response = $this->from(route('contact'))->post(route('contact.store'), [
            'name' => '<b>問い合わせ者</b>',
            'email' => 'visitor@example.test',
            'subject' => 'サイトの質問',
            'message' => '<script>alert(1)</script>',
        ]);

        $response->assertRedirect(route('contact'))->assertSessionHas('status');
        $this->assertDatabaseHas('contact_inquiries', [
            'name' => '<b>問い合わせ者</b>',
            'email' => 'visitor@example.test',
            'subject' => 'サイトの質問',
            'message' => '<script>alert(1)</script>',
            'status' => 'pending',
            'responded_at' => null,
        ]);

        $this->get(route('contact'))->assertOk()
            ->assertSee('送信を受け付けました')
            ->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_contact_form_validates_required_fields_and_email_format(): void
    {
        $this->from(route('contact'))
            ->post(route('contact.store'), [
                'name' => '',
                'email' => 'not-an-email',
                'subject' => '',
                'message' => '',
            ])
            ->assertRedirect(route('contact'))
            ->assertSessionHasErrors(['name', 'email', 'subject', 'message']);

        $this->assertDatabaseCount('contact_inquiries', 0);
    }

    public function test_only_verified_admins_can_manage_inquiries_and_completed_status_records_time(): void
    {
        $inquiry = ContactInquiry::create([
            'name' => '問い合わせ者',
            'email' => 'visitor@example.test',
            'subject' => 'サイト運営への質問',
            'message' => '一般的なお問い合わせ内容です。',
        ]);
        $admin = User::factory()->administrator()->create();
        $member = User::factory()->create();
        $unverifiedAdmin = User::factory()->administrator()->unverified()->create();

        $this->get(route('admin.contact-inquiries.index'))->assertRedirect(route('login'));
        $this->actingAs($member)->get(route('admin.contact-inquiries.index'))->assertForbidden();
        $this->actingAs($unverifiedAdmin)->get(route('admin.contact-inquiries.index'))
            ->assertRedirect(route('verification.notice'));

        $this->actingAs($admin)
            ->get(route('admin.contact-inquiries.index'))
            ->assertOk()
            ->assertSee('サイト運営への質問')
            ->assertSee(route('admin.contact-inquiries.show', $inquiry));

        $this->get(route('admin.contact-inquiries.show', $inquiry))
            ->assertOk()
            ->assertSee('一般的なお問い合わせ内容です。')
            ->assertSee('未対応');

        $this->patch(route('admin.contact-inquiries.update', $inquiry), [
            'status' => 'in_progress',
        ])->assertRedirect(route('admin.contact-inquiries.show', $inquiry));

        $this->assertSame('in_progress', $inquiry->fresh()->status);
        $this->assertNull($inquiry->fresh()->responded_at);

        $this->patch(route('admin.contact-inquiries.update', $inquiry), [
            'status' => 'completed',
        ])->assertRedirect(route('admin.contact-inquiries.show', $inquiry));

        $this->assertSame('completed', $inquiry->fresh()->status);
        $this->assertNotNull($inquiry->fresh()->responded_at);

        $this->patch(route('admin.contact-inquiries.update', $inquiry), [
            'status' => 'invalid',
        ])->assertSessionHasErrors('status');
    }
}
