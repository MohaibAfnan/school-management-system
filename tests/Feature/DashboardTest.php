<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page()
    {
        $response = $this->get(route('dashboard'));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_visit_the_dashboard()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('dashboard'));
        $response->assertOk();
    }

    public function test_authenticated_users_resolve_their_school_when_the_tenant_session_key_is_missing()
    {
        $user = User::factory()->create();
        $this->actingAs($user)->withSession(['active_school_id' => null]);

        $this->get(route('dashboard'))->assertOk();
        $this->assertSame($user->school_id, session('active_school_id'));
    }
}
