<?php

namespace Tests\Feature;

use App\Models\License;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LicenseAdminActionsTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected License $license;

    protected function setUp(): void
    {
        parent::setUp();

        // Create an admin user
        $this->admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@mydomain.com',
            'password' => Hash::make('CorrectPassword123!'),
        ]);

        // Create a mock license
        $this->license = License::create([
            'purchase_code' => 'KEY-TEST-123',
            'domain' => 'myproductsite.com',
            'buyer_username' => 'buyer_username',
            'item_id' => '12345678',
            'item_name' => 'Test Product',
            'license_type' => 'Regular License',
            'purchase_date' => now()->subMonth(),
            'status' => 'active',
        ]);
    }

    /**
     * Test guest cannot perform admin actions.
     */
    public function test_guest_cannot_toggle_status_or_delete(): void
    {
        // Try toggle status
        $response = $this->post("/admin/licenses/{$this->license->id}/toggle", [
            'password' => 'CorrectPassword123!',
        ]);
        $response->assertRedirect('/login');

        // Try delete
        $response = $this->delete("/admin/licenses/{$this->license->id}", [
            'password' => 'CorrectPassword123!',
        ]);
        $response->assertRedirect('/login');

        // Database stays unchanged
        $this->assertEquals('active', $this->license->fresh()->status);
        $this->assertDatabaseHas('licenses', ['id' => $this->license->id]);
    }

    /**
     * Test toggling status with empty or incorrect password.
     */
    public function test_toggling_status_fails_with_invalid_password(): void
    {
        $this->actingAs($this->admin);

        // Empty password
        $response = $this->post("/admin/licenses/{$this->license->id}/toggle", []);
        $response->assertSessionHasErrors(['password']);
        $this->assertEquals('active', $this->license->fresh()->status);

        // Incorrect password
        $response = $this->post("/admin/licenses/{$this->license->id}/toggle", [
            'password' => 'WrongPassword!',
        ]);
        $response->assertSessionHasErrors(['password']);
        $this->assertEquals('active', $this->license->fresh()->status);
    }

    /**
     * Test toggling status succeeds with correct password.
     */
    public function test_toggling_status_succeeds_with_correct_password(): void
    {
        $this->actingAs($this->admin);

        $response = $this->post("/admin/licenses/{$this->license->id}/toggle", [
            'password' => 'CorrectPassword123!',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();
        $this->assertEquals('revoked', $this->license->fresh()->status);

        // Toggle back to active
        $response = $this->post("/admin/licenses/{$this->license->id}/toggle", [
            'password' => 'CorrectPassword123!',
        ]);
        $this->assertEquals('active', $this->license->fresh()->status);
    }

    /**
     * Test deleting license fails with invalid password.
     */
    public function test_deleting_license_fails_with_invalid_password(): void
    {
        $this->actingAs($this->admin);

        // Incorrect password
        $response = $this->delete("/admin/licenses/{$this->license->id}", [
            'password' => 'WrongPassword!',
        ]);
        $response->assertSessionHasErrors(['password']);
        $this->assertDatabaseHas('licenses', ['id' => $this->license->id]);
    }

    /**
     * Test deleting license succeeds with correct password.
     */
    public function test_deleting_license_succeeds_with_correct_password(): void
    {
        $this->actingAs($this->admin);

        $response = $this->delete("/admin/licenses/{$this->license->id}", [
            'password' => 'CorrectPassword123!',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();
        $this->assertDatabaseMissing('licenses', ['id' => $this->license->id]);
    }
}
