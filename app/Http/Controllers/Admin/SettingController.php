<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    /**
     * Display the settings page.
     */
    public function index()
    {
        $settings = [
            'envato_api_key' => Setting::getValue('envato_api_key', ''),
            'allowed_item_ids' => Setting::getValue('allowed_item_ids', ''),
        ];

        return view('admin.settings.index', compact('settings'));
    }

    /**
     * Update the settings.
     */
    public function update(Request $request)
    {
        $request->validate([
            'envato_api_key' => ['nullable', 'string'],
            'allowed_item_ids' => ['nullable', 'string'],
        ]);

        Setting::setValue('envato_api_key', $request->input('envato_api_key'));
        Setting::setValue('allowed_item_ids', $request->input('allowed_item_ids'));

        return back()->with('success', 'Settings updated successfully.');
    }
}
