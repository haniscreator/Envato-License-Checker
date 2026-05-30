<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\License;
use App\Models\LicenseVerificationLog;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard.
     */
    public function index()
    {
        $stats = [
            'total_licenses' => License::count(),
            'active_licenses' => License::where('status', 'active')->count(),
            'revoked_licenses' => License::where('status', 'revoked')->count(),
            'checks_24h' => LicenseVerificationLog::where('created_at', '>=', now()->subDay())->count(),
            'checks_success_24h' => LicenseVerificationLog::where('created_at', '>=', now()->subDay())->where('status', 'success')->count(),
            'checks_failed_24h' => LicenseVerificationLog::where('created_at', '>=', now()->subDay())->where('status', 'failed')->count(),
        ];

        // Gather data for a simple chart representation (past 7 days check activity)
        $chartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $dateStr = $date->format('Y-m-d');
            $dateLabel = $date->format('M d');

            $successCount = LicenseVerificationLog::whereDate('created_at', $dateStr)->where('status', 'success')->count();
            $failedCount = LicenseVerificationLog::whereDate('created_at', $dateStr)->where('status', 'failed')->count();

            $chartData[] = [
                'label' => $dateLabel,
                'success' => $successCount,
                'failed' => $failedCount,
            ];
        }

        $recentLogs = LicenseVerificationLog::latest()->take(5)->get();
        $recentLicenses = License::latest()->take(5)->get();

        return view('admin.dashboard', compact('stats', 'chartData', 'recentLogs', 'recentLicenses'));
    }
}
