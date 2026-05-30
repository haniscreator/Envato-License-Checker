<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\License;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LicenseController extends Controller
{
    /**
     * Display a listing of licenses.
     */
    public function index(Request $request)
    {
        $query = License::query();

        // Handle Search
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('purchase_code', 'like', "%{$search}%")
                  ->orWhere('domain', 'like', "%{$search}%")
                  ->orWhere('buyer_username', 'like', "%{$search}%")
                  ->orWhere('item_name', 'like', "%{$search}%")
                  ->orWhere('item_id', 'like', "%{$search}%");
            });
        }

        // Handle Status Filter
        if ($status = $request->input('status')) {
            if (in_array($status, ['active', 'revoked'])) {
                $query->where('status', $status);
            }
        }

        $licenses = $query->latest()->paginate(15)->withQueryString();

        return view('admin.licenses.index', compact('licenses'));
    }

    /**
     * Update the domain for a specific license.
     */
    public function update(Request $request, License $license)
    {
        $request->validate([
            'domain' => ['required', 'string'],
        ]);

        // Normalize domain
        $domain = trim($request->input('domain'));
        $domain = strtolower($domain);
        $domain = preg_replace('/^https?:\/\//i', '', $domain);
        $domain = explode('/', $domain)[0];
        $domain = explode(':', $domain)[0];
        if (str_starts_with($domain, 'www.')) {
            $domain = substr($domain, 4);
        }

        $license->update([
            'domain' => $domain,
        ]);

        return back()->with('success', 'License domain updated successfully.');
    }

    /**
     * Toggle the active/revoked status of a license.
     */
    public function toggleStatus(Request $request, License $license)
    {
        $request->validate([
            'password' => ['required', 'string'],
        ]);

        if (!Hash::check($request->input('password'), Auth::user()->password)) {
            return back()->withErrors(['password' => 'Incorrect administrator password. Action aborted.']);
        }

        $newStatus = $license->status === 'active' ? 'revoked' : 'active';
        $license->update(['status' => $newStatus]);

        return back()->with('success', "License status changed to {$newStatus} successfully.");
    }

    /**
     * Remove the license.
     */
    public function destroy(Request $request, License $license)
    {
        $request->validate([
            'password' => ['required', 'string'],
        ]);

        if (!Hash::check($request->input('password'), Auth::user()->password)) {
            return back()->withErrors(['password' => 'Incorrect administrator password. Action aborted.']);
        }

        $license->delete();

        return back()->with('success', 'License deleted successfully. The purchase code is now free to be registered again.');
    }
}
