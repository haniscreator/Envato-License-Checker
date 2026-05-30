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

    /**
     * Test updating settings including primary_color with valid values.
     */
    public function test_updating_settings_succeeds_with_valid_primary_color(): void
    {
        $this->actingAs($this->admin);

        $response = $this->post('/admin/settings', [
            'envato_api_key' => 'NEW-API-KEY-VALUE',
            'allowed_item_ids' => '1111,2222',
            'primary_color' => '#123456',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();
        $this->assertDatabaseHas('settings', ['key' => 'primary_color', 'value' => '#123456']);
        $this->assertDatabaseHas('settings', ['key' => 'envato_api_key', 'value' => 'NEW-API-KEY-VALUE']);
    }

    /**
     * Test updating settings with invalid primary_color fails.
     */
    public function test_updating_settings_fails_with_invalid_primary_color(): void
    {
        $this->actingAs($this->admin);

        // Test missing color
        $response = $this->post('/admin/settings', [
            'primary_color' => '',
        ]);
        $response->assertSessionHasErrors(['primary_color']);

        // Test invalid format (no # prefix)
        $response = $this->post('/admin/settings', [
            'primary_color' => '123456',
        ]);
        $response->assertSessionHasErrors(['primary_color']);

        // Test invalid format (invalid character)
        $response = $this->post('/admin/settings', [
            'primary_color' => '#12345g',
        ]);
        $response->assertSessionHasErrors(['primary_color']);

        // Test invalid length
        $response = $this->post('/admin/settings', [
            'primary_color' => '#123',
        ]);
        $response->assertSessionHasErrors(['primary_color']);
    }
}
