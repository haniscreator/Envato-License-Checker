<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\License;
use App\Models\LicenseVerificationLog;
use App\Models\Setting;
use App\Services\EnvatoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LicenseVerificationController extends Controller
{
    /**
     * Verify an Envato purchase code and bind it to a domain.
     *
     * POST /api/licenses/verify
     */
    public function verify(Request $request, EnvatoService $envatoService)
    {
        // 1. Validate request parameters
        $validator = Validator::make($request->all(), [
            'purchase_code' => ['required', 'string'],
            'domain' => ['required', 'string'],
            'old_domain' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error: ' . implode(', ', $validator->errors()->all()),
            ], 400);
        }

        $purchaseCode = trim($request->input('purchase_code'));
        $rawDomain = $request->input('domain');
        $rawOldDomain = $request->input('old_domain');

        $domain = $this->normalizeDomain($rawDomain);
        $oldDomain = $this->normalizeDomain($rawOldDomain);
        $ipAddress = $request->ip() ?: '127.0.0.1';

        $isLocal = $this->isLocalhost($domain);

        // 2. Find existing record in local database
        $license = License::where('purchase_code', $purchaseCode)->first();

        if ($license) {
            // If the license is revoked, reject immediately
            if ($license->status !== 'active') {
                $this->logCheck($purchaseCode, $domain, $ipAddress, 'failed', 'License has been revoked by admin.');
                return response()->json([
                    'status' => false,
                    'message' => 'License has been revoked. Please contact support.',
                ], 403);
            }

            // If client is running on localhost, bypass binding check but return valid
            if ($isLocal) {
                $this->logCheck($purchaseCode, $domain, $ipAddress, 'success', 'Localhost bypass verification for already registered purchase code.');
                return response()->json([
                    'status' => true,
                    'message' => 'Verified on localhost.',
                    'buyer' => $license->buyer_username,
                    'item_id' => $license->item_id,
                    'item_name' => $license->item_name,
                    'license_type' => $license->license_type,
                    'purchase_date' => $license->purchase_date->toIso8601String(),
                    'registered_domain' => $license->domain,
                ]);
            }

            // Check if the domain matches
            if ($license->domain === $domain) {
                $license->update(['last_checked_at' => now()]);
                $this->logCheck($purchaseCode, $domain, $ipAddress, 'success', 'License verified successfully.');
                return response()->json([
                    'status' => true,
                    'message' => 'License verified successfully.',
                    'buyer' => $license->buyer_username,
                    'item_id' => $license->item_id,
                    'item_name' => $license->item_name,
                    'license_type' => $license->license_type,
                    'purchase_date' => $license->purchase_date->toIso8601String(),
                    'registered_domain' => $license->domain,
                ]);
            }

            // If domain does not match, check if we can perform self-service auto-transfer
            if (!empty($oldDomain) && $license->domain === $oldDomain) {
                // Update bound domain in database
                $license->update([
                    'domain' => $domain,
                    'last_checked_at' => now(),
                ]);

                $this->logCheck(
                    $purchaseCode,
                    $domain,
                    $ipAddress,
                    'success',
                    "License successfully auto-transferred from {$oldDomain} to {$domain}."
                );

                return response()->json([
                    'status' => true,
                    'message' => 'License successfully transferred to new domain.',
                    'buyer' => $license->buyer_username,
                    'item_id' => $license->item_id,
                    'item_name' => $license->item_name,
                    'license_type' => $license->license_type,
                    'purchase_date' => $license->purchase_date->toIso8601String(),
                    'registered_domain' => $domain,
                ]);
            }

            // Domain mismatch and no valid transfer input
            $this->logCheck(
                $purchaseCode,
                $domain,
                $ipAddress,
                'failed',
                "Domain mismatch. Request domain: {$domain}, Registered domain: {$license->domain}."
            );

            return response()->json([
                'status' => false,
                'message' => 'This purchase code is already registered to another domain. To transfer, please specify your old domain name.',
                'registered_domain' => $license->domain,
            ], 400);
        }

        // 3. Code does not exist in local database. Check with Envato API.
        $envatoResult = $envatoService->verifyPurchaseCode($purchaseCode);

        if (!$envatoResult['success']) {
            $this->logCheck($purchaseCode, $domain, $ipAddress, 'failed', $envatoResult['error']);
            return response()->json([
                'status' => false,
                'message' => $envatoResult['error'],
            ], 400);
        }

        // Verify item_id matches allowed item_ids (from settings)
        $allowedIdsStr = Setting::getValue('allowed_item_ids') ?: env('ALLOWED_ITEM_IDS', '');
        if (!empty($allowedIdsStr)) {
            $allowedIds = array_filter(array_map('trim', explode(',', $allowedIdsStr)));
            if (!empty($allowedIds) && !in_array($envatoResult['item_id'], $allowedIds)) {
                $msg = "Purchase code belongs to a different item ID: {$envatoResult['item_id']}. Allowed IDs: " . implode(', ', $allowedIds);
                $this->logCheck($purchaseCode, $domain, $ipAddress, 'failed', $msg);
                return response()->json([
                    'status' => false,
                    'message' => 'Purchase code belongs to a different product.',
                ], 400);
            }
        }

        // If verification is on localhost, we return success but do NOT register/bind the license to DB
        if ($isLocal) {
            $this->logCheck($purchaseCode, $domain, $ipAddress, 'success', 'Localhost temporary bypass check for new valid license.');
            return response()->json([
                'status' => true,
                'message' => 'Verified on localhost. License not registered yet.',
                'buyer' => $envatoResult['buyer_username'],
                'item_id' => $envatoResult['item_id'],
                'item_name' => $envatoResult['item_name'],
                'license_type' => $envatoResult['license_type'],
                'purchase_date' => $envatoResult['purchase_date'],
                'registered_domain' => null,
            ]);
        }

        // Save registration to licenses table
        $newLicense = License::create([
            'purchase_code' => $purchaseCode,
            'domain' => $domain,
            'buyer_username' => $envatoResult['buyer_username'],
            'item_id' => $envatoResult['item_id'],
            'item_name' => $envatoResult['item_name'],
            'license_type' => $envatoResult['license_type'],
            'purchase_date' => $envatoResult['purchase_date'],
            'status' => 'active',
            'last_checked_at' => now(),
        ]);

        $this->logCheck($purchaseCode, $domain, $ipAddress, 'success', 'New license registered and verified successfully.');

        return response()->json([
            'status' => true,
            'message' => 'License registered and verified successfully.',
            'buyer' => $newLicense->buyer_username,
            'item_id' => $newLicense->item_id,
            'item_name' => $newLicense->item_name,
            'license_type' => $newLicense->license_type,
            'purchase_date' => $newLicense->purchase_date->toIso8601String(),
            'registered_domain' => $newLicense->domain,
        ]);
    }

    /**
     * Normalize domain name.
     */
    protected function normalizeDomain(?string $domain): string
    {
        if (empty($domain)) {
            return '';
        }

        $domain = trim($domain);
        $domain = strtolower($domain);

        // Remove protocols (http://, https://)
        $domain = preg_replace('/^https?:\/\//i', '', $domain);

        // Remove path and query string (e.g. mydomain.com/sub/dir?p=1 -> mydomain.com)
        $domain = explode('/', $domain)[0];

        // Remove port numbers (e.g. mydomain.com:8000 -> mydomain.com)
        $domain = explode(':', $domain)[0];

        // Remove leading 'www.'
        if (str_starts_with($domain, 'www.')) {
            $domain = substr($domain, 4);
        }

        return $domain;
    }

    /**
     * Check if domain is localhost/local development.
     */
    protected function isLocalhost(string $domain): bool
    {
        if (empty($domain)) {
            return false;
        }

        // Exact matches
        if ($domain === 'localhost' || $domain === '127.0.0.1' || $domain === '::1') {
            return true;
        }

        // Local TLDs: .local, .test, .localhost, .invalid
        if (preg_match('/\.local$|\.test$|\.localhost$|\.invalid$/i', $domain)) {
            return true;
        }

        // Single word domains (no dots), e.g. "my-macbook" or "local-pc"
        if (!str_contains($domain, '.')) {
            return true;
        }

        return false;
    }

    /**
     * Helper to write verification logs.
     */
    protected function logCheck(string $purchaseCode, string $domain, string $ipAddress, string $status, string $message): void
    {
        LicenseVerificationLog::create([
            'purchase_code' => $purchaseCode,
            'domain' => $domain,
            'ip_address' => $ipAddress,
            'status' => $status,
            'message' => $message,
        ]);
    }
}
