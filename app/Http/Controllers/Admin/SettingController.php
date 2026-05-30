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
            'primary_color' => Setting::getValue('primary_color', '#f67e39'),
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
            'primary_color' => ['required', 'string', 'regex:/^#[a-fA-F0-9]{6}$/'],
        ]);

        Setting::setValue('envato_api_key', $request->input('envato_api_key'));
        Setting::setValue('allowed_item_ids', $request->input('allowed_item_ids'));
        Setting::setValue('primary_color', $request->input('primary_color'));

        return back()->with('success', 'Settings updated successfully.');
    }
}
