@extends('layouts.admin')

@section('title', 'Licenses')
@section('page_title', 'Licenses Manager')

@section('content')
<div class="space-y-6">
    <!-- Filters and Actions -->
    <div class="bg-white border border-slate-200 rounded-3xl p-6 shadow-sm flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <!-- Search and Filter Form -->
        <form action="{{ route('admin.licenses.index') }}" method="GET" class="flex-1 flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </span>
                <input type="text" name="search" value="{{ request('search') }}"
                    class="block w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary text-sm transition-all placeholder-slate-400"
                    placeholder="Search by purchase code, domain, or buyer...">
            </div>

            <div class="w-full sm:w-48">
                <select name="status" onchange="this.form.submit()"
                    class="block w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary text-sm transition-all">
                    <option value="">All Statuses</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="revoked" {{ request('status') === 'revoked' ? 'selected' : '' }}>Revoked</option>
                </select>
            </div>

            @if (request('search') || request('status'))
                <a href="{{ route('admin.licenses.index') }}" class="inline-flex items-center justify-center px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-sm font-semibold transition-colors">
                    Clear Filters
                </a>
            @endif
        </form>
    </div>

    <!-- Licenses Table -->
    <div class="bg-white border border-slate-200 rounded-3xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-[10px] font-bold uppercase tracking-wider text-slate-400">
                        <th class="py-4 px-6">Purchase Code / Item</th>
                        <th class="py-4 px-6">Domain Binding</th>
                        <th class="py-4 px-6">Buyer</th>
                        <th class="py-4 px-6">Dates</th>
                        <th class="py-4 px-6">Status</th>
                        <th class="py-4 px-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs text-slate-600">
                    @forelse ($licenses as $license)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <!-- Purchase Code & Item -->
                            <td class="py-4 px-6">
                                <div class="font-mono font-bold text-slate-900 mb-1 select-all">{{ $license->purchase_code }}</div>
                                <div class="text-[10px] text-slate-500 font-semibold truncate max-w-[240px]" title="{{ $license->item_name }}">
                                    {{ $license->item_name }}
                                </div>
                                <div class="text-[9px] text-slate-400 font-semibold mt-1 space-x-1.5 flex items-center">
                                    <span class="bg-slate-100 text-slate-650 px-1.5 py-0.5 rounded border border-slate-200/50">{{ $license->license_type }}</span>
                                    <span class="text-slate-300">•</span>
                                    <span>ID: {{ $license->item_id }}</span>
                                </div>
                            </td>

                            <!-- Bound Domain -->
                            <td class="py-4 px-6">
                                <div class="flex items-center space-x-2">
                                    <span class="font-mono bg-slate-100 text-slate-700 px-2 py-1 rounded-md text-[11px] border border-slate-200/50">{{ $license->domain }}</span>
                                    <button type="button" onclick="openEditModal({{ $license->id }}, '{{ $license->domain }}')" class="text-slate-400 hover:text-primary transition-colors p-1" title="Edit Domain">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                        </svg>
                                    </button>
                                </div>
                            </td>

                            <!-- Buyer Username -->
                            <td class="py-4 px-6 font-medium text-slate-900">
                                {{ $license->buyer_username }}
                            </td>

                            <!-- Dates -->
                            <td class="py-4 px-6 text-slate-500 space-y-2">
                                <div>
                                    <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider block">Purchased:</span>
                                    <span class="font-semibold text-slate-700">{{ $license->purchase_date ? $license->purchase_date->format('M j, Y H:i') : 'Unknown' }}</span>
                                </div>
                                <div>
                                    <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider block">Registered:</span>
                                    <span class="font-semibold text-slate-700">{{ $license->created_at->format('M j, Y H:i') }}</span>
                                </div>
                                <div>
                                    <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider block">Last Checked:</span>
                                    <span class="font-semibold text-slate-700">{{ $license->last_checked_at ? $license->last_checked_at->format('M j, Y H:i') : 'Never' }}</span>
                                </div>
                            </td>

                            <!-- Status Badge -->
                            <td class="py-4 px-6">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold tracking-wide uppercase {{ $license->status === 'active' ? 'bg-emerald-50 text-emerald-700 border border-emerald-100' : 'bg-rose-50 text-rose-700 border border-rose-100' }}">
                                    {{ $license->status }}
                                </span>
                            </td>

                            <!-- Actions -->
                            <td class="py-4 px-6 text-right">
                                <div class="flex items-center justify-end space-x-2">
                                    <!-- Toggle Status Button (Triggers Confirm Modal) -->
                                    <button type="button" 
                                        onclick="openConfirmModal('{{ route('admin.licenses.toggle', $license) }}', 'POST', '{{ $license->status === 'active' ? 'Revoke License' : 'Activate License' }}', '{{ $license->status === 'active' ? 'Revoking this license will block this domain from passing verification checks.' : 'Activating this license will restore verification access.' }}', {{ $license->status === 'active' ? 'true' : 'false' }})" 
                                        class="inline-flex items-center justify-center px-2.5 py-1.5 rounded-lg border text-[11px] font-bold transition-all {{ $license->status === 'active' ? 'border-amber-200 text-amber-700 bg-amber-50 hover:bg-amber-100' : 'border-emerald-200 text-emerald-700 bg-emerald-50 hover:bg-emerald-100' }}">
                                        {{ $license->status === 'active' ? 'Revoke' : 'Activate' }}
                                    </button>

                                    <!-- Delete Button (Triggers Confirm Modal) -->
                                    <button type="button" 
                                        onclick="openConfirmModal('{{ route('admin.licenses.destroy', $license) }}', 'DELETE', 'Delete License Binding', 'Deleting this license removes it from the local database. If the site is still active, it will attempt to re-register on its next check. Use Revoke to block permanently.', true)" 
                                        class="inline-flex items-center justify-center p-1.5 border border-rose-200 text-rose-500 bg-rose-50 hover:bg-rose-100 rounded-lg transition-colors" title="Delete License">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 px-6 text-center text-slate-400">
                                <svg class="mx-auto h-12 w-12 text-slate-300 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                                </svg>
                                <span class="block font-medium text-sm text-slate-500 mb-1">No Licenses Found</span>
                                <span class="block text-xs">Try adjusting your search query or status filter.</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($licenses->hasPages())
            <div class="px-6 py-4 border-t border-slate-100 bg-slate-50">
                {{ $licenses->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Edit Domain Modal -->
<div id="edit-domain-modal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm transition-opacity" aria-hidden="true" onclick="closeEditModal()"></div>

        <!-- Modal Center Helper -->
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <!-- Modal Box -->
        <div class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-slate-100">
            <form id="edit-domain-form" method="POST" action="">
                @csrf
                @method('PUT')
                <div class="bg-white px-6 pt-6 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto shrink-0 flex items-center justify-center h-12 w-12 rounded-2xl bg-primary/10 text-primary sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-bold text-slate-900" id="modal-title">Edit Domain Binding</h3>
                            <div class="mt-2">
                                <p class="text-xs text-slate-400">Change the bound domain name for this purchase license. Old sites will no longer pass verification.</p>
                            </div>
                            <div class="mt-4">
                                <label for="modal-domain-input" class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Domain Name</label>
                                <input type="text" id="modal-domain-input" name="domain" required
                                    class="block w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary text-sm transition-all placeholder-slate-400"
                                    placeholder="clientdomain.com">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-slate-50 px-6 py-4 sm:px-6 sm:flex sm:flex-row-reverse gap-3 rounded-b-3xl border-t border-slate-100">
                    <button type="submit" class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-4 py-2 bg-primary hover:opacity-90 text-sm font-semibold text-white focus:outline-none focus:ring-2 focus:ring-primary sm:w-auto">
                        Save Changes
                    </button>
                    <button type="button" onclick="closeEditModal()" class="mt-3 w-full inline-flex justify-center rounded-xl border border-slate-200 px-4 py-2 bg-white text-sm font-semibold text-slate-700 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-primary sm:mt-0 sm:w-auto">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Confirm Action Modal (Revoke / Activate / Delete) -->
<div id="confirm-action-modal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm transition-opacity" aria-hidden="true" onclick="closeConfirmModal()"></div>

        <!-- Modal Center Helper -->
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <!-- Modal Box -->
        <div class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-slate-100">
            <form id="confirm-action-form" method="POST" action="">
                @csrf
                <input type="hidden" name="_method" id="confirm-method-input" value="POST">
                <div class="bg-white px-6 pt-6 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div id="confirm-icon-container" class="mx-auto shrink-0 flex items-center justify-center h-12 w-12 rounded-2xl bg-amber-50 text-amber-600 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-bold text-slate-900" id="confirm-modal-title">Confirm Action</h3>
                            <div class="mt-2">
                                <p class="text-xs text-slate-400" id="confirm-modal-description">Please enter your administrator password to confirm this action.</p>
                            </div>
                            <div class="mt-4">
                                <label for="confirm-password-input" class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Admin Password</label>
                                <input type="password" id="confirm-password-input" name="password" required
                                    class="block w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary text-sm transition-all"
                                    placeholder="Enter password">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-slate-50 px-6 py-4 sm:px-6 sm:flex sm:flex-row-reverse gap-3 rounded-b-3xl border-t border-slate-100">
                    <button type="submit" id="confirm-submit-button" class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-4 py-2 bg-amber-600 hover:bg-amber-700 text-sm font-semibold text-white focus:outline-none focus:ring-2 focus:ring-amber-500 sm:w-auto">
                        Confirm Action
                    </button>
                    <button type="button" onclick="closeConfirmModal()" class="mt-3 w-full inline-flex justify-center rounded-xl border border-slate-200 px-4 py-2 bg-white text-sm font-semibold text-slate-700 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-primary sm:mt-0 sm:w-auto">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function openEditModal(id, currentDomain) {
        const modal = document.getElementById('edit-domain-modal');
        const form = document.getElementById('edit-domain-form');
        const input = document.getElementById('modal-domain-input');

        form.action = `{{ route('admin.licenses.index') }}/${id}`;
        input.value = currentDomain;

        modal.classList.remove('hidden');
    }

    function closeEditModal() {
        const modal = document.getElementById('edit-domain-modal');
        modal.classList.add('hidden');
    }

    function openConfirmModal(actionUrl, method, title, description, isDanger = false) {
        const modal = document.getElementById('confirm-action-modal');
        const form = document.getElementById('confirm-action-form');
        const methodInput = document.getElementById('confirm-method-input');
        const modalTitle = document.getElementById('confirm-modal-title');
        const modalDesc = document.getElementById('confirm-modal-description');
        const submitBtn = document.getElementById('confirm-submit-button');
        const iconContainer = document.getElementById('confirm-icon-container');

        form.action = actionUrl;
        methodInput.value = method;
        modalTitle.textContent = title;
        modalDesc.textContent = description;

        if (isDanger) {
            submitBtn.className = "w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-4 py-2 bg-rose-600 hover:bg-rose-700 text-sm font-semibold text-white focus:outline-none focus:ring-2 focus:ring-rose-500 sm:w-auto";
            iconContainer.className = "mx-auto shrink-0 flex items-center justify-center h-12 w-12 rounded-2xl bg-rose-50 text-rose-600 sm:mx-0 sm:h-10 sm:w-10";
        } else {
            submitBtn.className = "w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-4 py-2 bg-amber-600 hover:bg-amber-700 text-sm font-semibold text-white focus:outline-none focus:ring-2 focus:ring-amber-500 sm:w-auto";
            iconContainer.className = "mx-auto shrink-0 flex items-center justify-center h-12 w-12 rounded-2xl bg-amber-50 text-amber-600 sm:mx-0 sm:h-10 sm:w-10";
        }

        // Reset password input
        document.getElementById('confirm-password-input').value = '';

        modal.classList.remove('hidden');
    }

    function closeConfirmModal() {
        const modal = document.getElementById('confirm-action-modal');
        modal.classList.add('hidden');
    }
</script>
@endsection
