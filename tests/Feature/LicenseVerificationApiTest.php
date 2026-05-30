<?php

namespace Tests\Feature;

use App\Models\License;
use App\Models\LicenseVerificationLog;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LicenseVerificationApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Configure mock allowed item IDs
        Setting::setValue('allowed_item_ids', '12345678,99999999');
    }

    /**
     * Test verifying and registering a new valid purchase code.
     */
    public function test_verifies_and_registers_new_valid_license(): void
    {
        $response = $this->postJson('/api/licenses/verify', [
            'purchase_code' => 'SIMULATE-VALID-KEY-123',
            'domain' => 'myproductsite.com',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonPath('buyer', 'mock_buyer')
            ->assertJsonPath('registered_domain', 'myproductsite.com');

        // Check it was saved to the licenses database
        $this->assertDatabaseHas('licenses', [
            'purchase_code' => 'SIMULATE-VALID-KEY-123',
            'domain' => 'myproductsite.com',
            'status' => 'active',
        ]);

        // Check audit log was created
        $this->assertDatabaseHas('license_verification_logs', [
            'purchase_code' => 'SIMULATE-VALID-KEY-123',
            'domain' => 'myproductsite.com',
            'status' => 'success',
        ]);
    }

    /**
     * Test verifying a purchase code that is already registered to the same domain.
     */
    public function test_verifies_already_registered_license_on_same_domain(): void
    {
        // Seed a license
        License::create([
            'purchase_code' => 'KEY-ABC-123',
            'domain' => 'active-domain.com',
            'buyer_username' => 'test_buyer',
            'item_id' => '12345678',
            'item_name' => 'Mock Product',
            'license_type' => 'Regular License',
            'purchase_date' => now()->subMonth(),
            'status' => 'active',
        ]);

        $response = $this->postJson('/api/licenses/verify', [
            'purchase_code' => 'KEY-ABC-123',
            'domain' => 'active-domain.com',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonPath('registered_domain', 'active-domain.com');

        $this->assertDatabaseCount('licenses', 1);
    }

    /**
     * Test verifying a purchase code on a different domain fails without old_domain parameter.
     */
    public function test_fails_verifying_license_on_different_domain(): void
    {
        // Seed a license bound to active-domain.com
        License::create([
            'purchase_code' => 'KEY-ABC-123',
            'domain' => 'active-domain.com',
            'buyer_username' => 'test_buyer',
            'item_id' => '12345678',
            'item_name' => 'Mock Product',
            'license_type' => 'Regular License',
            'purchase_date' => now()->subMonth(),
            'status' => 'active',
        ]);

        $response = $this->postJson('/api/licenses/verify', [
            'purchase_code' => 'KEY-ABC-123',
            'domain' => 'stolen-domain.com',
        ]);

        $response->assertStatus(400)
            ->assertJsonPath('status', false)
            ->assertJsonFragment([
                'registered_domain' => 'active-domain.com',
            ]);

        // Verify failure was logged
        $this->assertDatabaseHas('license_verification_logs', [
            'purchase_code' => 'KEY-ABC-123',
            'domain' => 'stolen-domain.com',
            'status' => 'failed',
        ]);
    }

    /**
     * Test auto-transfer of a license when the correct old_domain is supplied.
     */
    public function test_transfers_domain_when_correct_old_domain_supplied(): void
    {
        // Seed a license bound to active-domain.com
        License::create([
            'purchase_code' => 'KEY-ABC-123',
            'domain' => 'active-domain.com',
            'buyer_username' => 'test_buyer',
            'item_id' => '12345678',
            'item_name' => 'Mock Product',
            'license_type' => 'Regular License',
            'purchase_date' => now()->subMonth(),
            'status' => 'active',
        ]);

        $response = $this->postJson('/api/licenses/verify', [
            'purchase_code' => 'KEY-ABC-123',
            'domain' => 'transferred-domain.com',
            'old_domain' => 'active-domain.com', // Match existing binding
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonPath('registered_domain', 'transferred-domain.com');

        // Verify database is updated
        $this->assertDatabaseHas('licenses', [
            'purchase_code' => 'KEY-ABC-123',
            'domain' => 'transferred-domain.com',
        ]);

        $this->assertDatabaseMissing('licenses', [
            'purchase_code' => 'KEY-ABC-123',
            'domain' => 'active-domain.com',
        ]);
    }

    /**
     * Test that verification on localhost bypasses database registration.
     */
    public function test_localhost_bypasses_license_registration(): void
    {
        // Verify with a brand new code on localhost
        $response = $this->postJson('/api/licenses/verify', [
            'purchase_code' => 'SIMULATE-VALID-KEY-NEW',
            'domain' => 'localhost',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonPath('registered_domain', null);

        // Verify that it is NOT registered in the licenses database
        $this->assertDatabaseMissing('licenses', [
            'purchase_code' => 'SIMULATE-VALID-KEY-NEW',
        ]);

        // But it should write a success log
        $this->assertDatabaseHas('license_verification_logs', [
            'purchase_code' => 'SIMULATE-VALID-KEY-NEW',
            'domain' => 'localhost',
            'status' => 'success',
        ]);
    }

    /**
     * Test that verifying an already registered purchase code on localhost is allowed.
     */
    public function test_allows_already_registered_license_on_localhost(): void
    {
        // Seed a license bound to production-domain.com
        License::create([
            'purchase_code' => 'KEY-ABC-123',
            'domain' => 'production-domain.com',
            'buyer_username' => 'test_buyer',
            'item_id' => '12345678',
            'item_name' => 'Mock Product',
            'license_type' => 'Regular License',
            'purchase_date' => now()->subMonth(),
            'status' => 'active',
        ]);

        // Verify on localhost
        $response = $this->postJson('/api/licenses/verify', [
            'purchase_code' => 'KEY-ABC-123',
            'domain' => '127.0.0.1',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonPath('registered_domain', 'production-domain.com');

        // Database should still keep production-domain.com
        $this->assertDatabaseHas('licenses', [
            'purchase_code' => 'KEY-ABC-123',
            'domain' => 'production-domain.com',
        ]);
    }

    /**
     * Test that a revoked license fails verification.
     */
    public function test_revoked_license_fails_verification(): void
    {
        // Seed a revoked license
        License::create([
            'purchase_code' => 'KEY-REVOKED-123',
            'domain' => 'some-domain.com',
            'buyer_username' => 'test_buyer',
            'item_id' => '12345678',
            'item_name' => 'Mock Product',
            'license_type' => 'Regular License',
            'purchase_date' => now()->subMonth(),
            'status' => 'revoked', // Revoked status
        ]);

        $response = $this->postJson('/api/licenses/verify', [
            'purchase_code' => 'KEY-REVOKED-123',
            'domain' => 'some-domain.com',
        ]);

        $response->assertStatus(403)
            ->assertJsonPath('status', false)
            ->assertJsonFragment([
                'message' => 'License has been revoked. Please contact support.',
            ]);
    }
}
