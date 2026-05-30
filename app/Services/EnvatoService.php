<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EnvatoService
{
    /**
     * Verify an Envato purchase code.
     *
     * @param string $purchaseCode
     * @return array
     */
    public function verifyPurchaseCode(string $purchaseCode): array
    {
        $purchaseCode = trim($purchaseCode);

        // Handle simulation/mock codes
        if ($this->shouldSimulate($purchaseCode)) {
            return $this->getSimulatedResponse($purchaseCode);
        }

        // Fetch API Key from settings or environment
        $apiKey = Setting::getValue('envato_api_key') ?: env('ENVATO_API_KEY');

        if (empty($apiKey)) {
            Log::warning("Envato API Key is not configured. Falling back to simulation for code: {$purchaseCode}");
            return [
                'success' => false,
                'error' => 'Envato API token not configured on server.',
            ];
        }

        try {
            $response = Http::withToken($apiKey)
                ->withHeaders([
                    'User-Agent' => 'LicenseChecker/1.0',
                ])
                ->timeout(10)
                ->get("https://api.envato.com/v3/market/author/sale", [
                    'code' => $purchaseCode,
                ]);

            if ($response->status() === 404) {
                return [
                    'success' => false,
                    'error' => 'Purchase code not found or invalid.',
                ];
            }

            if ($response->status() === 401 || $response->status() === 403) {
                Log::error("Envato API unauthorized or forbidden status: " . $response->status() . " Response: " . $response->body());
                return [
                    'success' => false,
                    'error' => 'Envato API token is invalid or unauthorized.',
                ];
            }

            if (!$response->successful()) {
                Log::error("Envato API error status: " . $response->status() . " Response: " . $response->body());
                return [
                    'success' => false,
                    'error' => 'Failed to connect to Envato API.',
                ];
            }

            $data = $response->json();

            // Check if essential fields exist in response
            if (!isset($data['item']['id']) || !isset($data['buyer'])) {
                Log::error("Envato API response format invalid: " . json_encode($data));
                return [
                    'success' => false,
                    'error' => 'Invalid response format from Envato API.',
                ];
            }

            return [
                'success' => true,
                'buyer_username' => $data['buyer'],
                'item_id' => (string) $data['item']['id'],
                'item_name' => $data['item']['name'] ?? 'Unknown Item',
                'license_type' => $data['license'] ?? 'Regular License',
                'purchase_date' => $data['sold_at'] ?? now()->toIso8601String(),
            ];

        } catch (\Exception $e) {
            Log::error("Envato API request failed: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Connection error while contacting Envato API.',
            ];
        }
    }

    /**
     * Determine if a purchase code should trigger a simulated response.
     */
    protected function shouldSimulate(string $purchaseCode): bool
    {
        return str_starts_with(strtoupper($purchaseCode), 'SIMULATE-') || app()->environment('testing');
    }

    /**
     * Get simulated mock responses for local testing.
     */
    protected function getSimulatedResponse(string $purchaseCode): array
    {
        $upperCode = strtoupper($purchaseCode);

        if ($upperCode === 'SIMULATE-INVALID') {
            return [
                'success' => false,
                'error' => 'Invalid simulated purchase code.',
            ];
        }

        // Get allowed item IDs to make mock response align with settings
        $allowedIdsStr = Setting::getValue('allowed_item_ids') ?: env('ALLOWED_ITEM_IDS', '12345678');
        $allowedIds = array_filter(array_map('trim', explode(',', $allowedIdsStr)));
        $itemId = !empty($allowedIds) ? head($allowedIds) : '12345678';

        return [
            'success' => true,
            'buyer_username' => 'mock_buyer',
            'item_id' => $itemId,
            'item_name' => 'Healthy-AI - Premium AI Monolith',
            'license_type' => 'Regular License',
            'purchase_date' => now()->subMonths(2)->toIso8601String(),
        ];
    }
}
