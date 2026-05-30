<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LicenseVerificationLog;
use Illuminate\Http\Request;

class LogController extends Controller
{
    /**
     * Display a listing of verification logs.
     */
    public function index(Request $request)
    {
        $query = LicenseVerificationLog::query();

        // Handle Search
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('purchase_code', 'like', "%{$search}%")
                  ->orWhere('domain', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%")
                  ->orWhere('message', 'like', "%{$search}%");
            });
        }

        // Handle Status Filter
        if ($status = $request->input('status')) {
            if (in_array($status, ['success', 'failed'])) {
                $query->where('status', $status);
            }
        }

        $logs = $query->latest()->paginate(25)->withQueryString();

        return view('admin.logs.index', compact('logs'));
    }

    /**
     * Clear old logs (older than 30 days).
     */
    public function clearOld()
    {
        $deleted = LicenseVerificationLog::where('created_at', '<', now()->subDays(30))->delete();

        return back()->with('success', "Cleared {$deleted} log entries older than 30 days.");
    }

    /**
     * Clear all logs.
     */
    public function clearAll()
    {
        LicenseVerificationLog::truncate();

        return back()->with('success', 'All verification logs cleared successfully.');
    }
}
