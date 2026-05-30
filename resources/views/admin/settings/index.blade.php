@extends('layouts.admin')

@section('title', 'Settings')
@section('page_title', 'System Settings')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Envato Configuration -->
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white border border-slate-200 rounded-3xl p-8 shadow-sm">
            <h3 class="text-lg font-bold text-slate-900 mb-2">Envato Integration</h3>
            <p class="text-xs text-slate-400 mb-6">Configure the API key and allowed products parameters here.</p>

            <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-6">
                @csrf

                <!-- Envato API Token -->
                <div>
                    <label for="envato_api_key" class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Envato API Personal Token</label>
                    <input type="password" id="envato_api_key" name="envato_api_key" value="{{ $settings['envato_api_key'] }}"
                        class="block w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm transition-all placeholder-slate-400"
                        placeholder="••••••••••••••••••••••••••••••••">
                    <span class="text-[10px] text-slate-400 mt-1 block">Generate this token at build.envato.com with permissions: "View and search Envato sites", "Verify purchases of your items".</span>
                </div>

                <!-- Allowed Item IDs -->
                <div>
                    <label for="allowed_item_ids" class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Allowed CodeCanyon Item IDs</label>
                    <input type="text" id="allowed_item_ids" name="allowed_item_ids" value="{{ $settings['allowed_item_ids'] }}"
                        class="block w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm transition-all placeholder-slate-400"
                        placeholder="e.g. 12345678, 87654321">
                    <span class="text-[10px] text-slate-400 mt-1 block">Comma-separated list of Item IDs. Only purchase codes belonging to these Item IDs will be verified. Leave blank to allow any of your items.</span>
                </div>

                <div class="pt-4 border-t border-slate-100 flex justify-end">
                    <button type="submit" class="inline-flex justify-center px-5 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold rounded-xl text-sm transition-all shadow-md shadow-indigo-600/10 active:scale-[0.98]">
                        Save Settings
                    </button>
                </div>
            </form>
        </div>

        <!-- Guide Box -->
        <div class="bg-indigo-50 border border-indigo-100 rounded-3xl p-6 shadow-sm">
            <h4 class="text-sm font-bold text-indigo-900 mb-2">How to verify licenses on client products?</h4>
            <p class="text-xs text-indigo-700 leading-relaxed mb-4">
                To check purchase codes from your main product (e.g. Healthy-AI), send a POST request from the client backend to your License API Server:
            </p>
            <div class="bg-slate-900 text-slate-300 font-mono text-[10px] p-4 rounded-2xl overflow-x-auto shadow-inner select-all leading-normal">
POST {{ route('admin.dashboard') }}/../api/licenses/verify
Content-Type: application/json

{
  "purchase_code": "CLIENT-PURCHASE-CODE",
  "domain": "clientwebsite.com",
  "old_domain": "previous-bound-domain.com" // (Optional, for auto-transfer)
}
            </div>
            <p class="text-xs text-indigo-700 leading-relaxed mt-4">
                <strong>Response format:</strong>
            </p>
            <div class="bg-slate-900 text-slate-350 font-mono text-[10px] p-4 rounded-2xl overflow-x-auto shadow-inner leading-normal">
{
  "status": true,
  "message": "License verified successfully.",
  "buyer": "buyer_username",
  "item_id": "12345678",
  "registered_domain": "clientwebsite.com"
}
            </div>
        </div>
    </div>

    <!-- Password Updates -->
    <div class="lg:col-span-1">
        <div class="bg-white border border-slate-200 rounded-3xl p-8 shadow-sm">
            <h3 class="text-lg font-bold text-slate-900 mb-2">Change Password</h3>
            <p class="text-xs text-slate-400 mb-6">Update the password for your administrator login.</p>

            <form action="{{ route('admin.password.update') }}" method="POST" class="space-y-4">
                @csrf

                <!-- Current Password -->
                <div>
                    <label for="current_password" class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Current Password</label>
                    <input type="password" id="current_password" name="current_password" required
                        class="block w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm transition-all"
                        placeholder="••••••••">
                </div>

                <!-- New Password -->
                <div>
                    <label for="new_password" class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">New Password</label>
                    <input type="password" id="new_password" name="new_password" required
                        class="block w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm transition-all"
                        placeholder="Min 8 characters">
                </div>

                <!-- Confirm Password -->
                <div>
                    <label for="new_password_confirmation" class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Confirm New Password</label>
                    <input type="password" id="new_password_confirmation" name="new_password_confirmation" required
                        class="block w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm transition-all"
                        placeholder="Confirm password">
                </div>

                <div class="pt-4 border-t border-slate-100 flex justify-end">
                    <button type="submit" class="w-full inline-flex justify-center py-2.5 px-4 bg-slate-900 hover:bg-slate-800 text-white font-semibold rounded-xl text-sm transition-all shadow-md shadow-slate-900/10 active:scale-[0.98]">
                        Update Password
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
