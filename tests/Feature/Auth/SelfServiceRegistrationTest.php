<?php

namespace Tests\Feature\Auth;

use App\Models\EmailOtp;
use App\Models\School;
use App\Models\SchoolSubscription;
use App\Models\User;
use App\Notifications\EmailOtpNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class SelfServiceRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_creates_pending_workspace_and_sends_hashed_otp(): void
    {
        Notification::fake();
        $response = $this->post(route('register.store'), [
            'school_name' => 'Northstar Academy', 'name' => 'Morgan Owner', 'email' => 'OWNER@EXAMPLE.COM',
            'password' => 'Strong-passphrase-42!', 'password_confirmation' => 'Strong-passphrase-42!', 'terms' => '1',
        ]);
        $response->assertRedirect(route('register.verify', absolute: false));
        $owner = User::withoutGlobalScopes()->where('email', 'owner@example.com')->firstOrFail();
        $this->assertNotNull($owner->school_id);
        $this->assertNull($owner->email_verified_at);
        $this->assertDatabaseHas('school_subscriptions', ['school_id' => $owner->school_id, 'status' => 'pending', 'trial_ends_at' => null]);
        $this->assertDatabaseHas('email_otps', ['user_id' => $owner->id]);
        Notification::assertSentTo($owner, EmailOtpNotification::class);
    }

    public function test_valid_otp_verifies_email_starts_seven_day_trial_and_authenticates_owner(): void
    {
        Notification::fake();
        $this->post(route('register.store'), [
            'school_name' => 'Northstar Academy', 'name' => 'Morgan Owner', 'email' => 'owner@example.com',
            'password' => 'Strong-passphrase-42!', 'password_confirmation' => 'Strong-passphrase-42!', 'terms' => '1',
        ]);
        $owner = User::withoutGlobalScopes()->where('email', 'owner@example.com')->firstOrFail();
        $notification = Notification::sent($owner, EmailOtpNotification::class)->first();
        $code = (string) (new \ReflectionProperty($notification, 'code'))->getValue($notification);
        $this->post(route('register.verify.store'), ['code' => $code])->assertRedirect(route('dashboard', absolute: false));
        $this->assertAuthenticatedAs($owner);
        $this->get(route('dashboard'))->assertOk();
        $this->assertNotNull($owner->fresh()->email_verified_at);
        $subscription = SchoolSubscription::query()->where('school_id', $owner->school_id)->firstOrFail();
        $this->assertSame('trialing', $subscription->status);
        $this->assertTrue($subscription->trial_ends_at->between(now()->addDays(7)->subMinute(), now()->addDays(7)->addMinute()));
    }

    public function test_invalid_otp_cannot_activate_the_trial(): void
    {
        Notification::fake();
        $this->post(route('register.store'), [
            'school_name' => 'Northstar Academy', 'name' => 'Morgan Owner', 'email' => 'owner@example.com',
            'password' => 'Strong-passphrase-42!', 'password_confirmation' => 'Strong-passphrase-42!', 'terms' => '1',
        ]);
        $this->from(route('register.verify'))->post(route('register.verify.store'), ['code' => '000000'])->assertSessionHasErrors('code');
        $this->assertGuest();
        $this->assertDatabaseHas('school_subscriptions', ['status' => 'pending', 'trial_ends_at' => null]);
    }
}
