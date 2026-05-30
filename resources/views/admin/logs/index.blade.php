@extends('layouts.admin')

@section('title', 'Verification Logs')
@section('page_title', 'API Verification Logs')

@section('content')
<div class="space-y-6">
    <!-- Filters and Purge Actions -->
    <div class="bg-white border border-slate-200 rounded-3xl p-6 shadow-sm flex flex-col xl:flex-row xl:items-center xl:justify-between gap-4">
        <!-- Search and Filter Form -->
        <form action="{{ route('admin.logs.index') }}" method="GET" class="flex-1 flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </span>
                <input type="text" name="search" value="{{ request('search') }}"
                    class="block w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary text-sm transition-all placeholder-slate-400"
                    placeholder="Search by code, domain, IP, message...">
            </div>

            <div class="w-full sm:w-48">
                <select name="status" onchange="this.form.submit()"
                    class="block w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary text-sm transition-all">
                    <option value="">All Results</option>
                    <option value="success" {{ request('status') === 'success' ? 'selected' : '' }}>Success (Passed)</option>
                    <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed (Blocked)</option>
                </select>
            </div>

            @if (request('search') || request('status'))
                <a href="{{ route('admin.logs.index') }}" class="inline-flex items-center justify-center px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-sm font-semibold transition-colors">
                    Clear Filters
                </a>
            @endif
        </form>

        <!-- Purge buttons -->
        <div class="flex items-center space-x-3 shrink-0">
            <!-- Clear older than 30 days -->
            <form action="{{ route('admin.logs.clear-old') }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to clear log entries older than 30 days?');">
                @csrf
                <button type="submit" class="inline-flex items-center justify-center px-4 py-2.5 border border-slate-200 hover:bg-slate-50 text-slate-750 font-semibold text-xs rounded-xl transition-all">
                    Purge >30 Days Logs
                </button>
            </form>

            <!-- Clear all logs -->
            <form action="{{ route('admin.logs.clear-all') }}" method="POST" class="inline" onsubmit="return confirm('Are you absolutely sure you want to clear ALL logs? This action is irreversible.');">
                @csrf
                <button type="submit" class="inline-flex items-center justify-center px-4 py-2.5 border border-rose-200 bg-rose-50 hover:bg-rose-100 text-rose-700 font-semibold text-xs rounded-xl transition-all">
                    Clear All Logs
                </button>
            </form>
        </div>
    </div>

    <!-- Logs Table -->
    <div class="bg-white border border-slate-200 rounded-3xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-[10px] font-bold uppercase tracking-wider text-slate-400">
                        <th class="py-4 px-6">Timestamp</th>
                        <th class="py-4 px-6">Domain</th>
                        <th class="py-4 px-6">Purchase Code</th>
                        <th class="py-4 px-6">Client IP</th>
                        <th class="py-4 px-6">Result</th>
                        <th class="py-4 px-6">Response Message</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs text-slate-600">
                    @forelse ($logs as $log)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <!-- Time -->
                            <td class="py-4 px-6 text-slate-500 whitespace-nowrap">
                                {{ $log->created_at->format('M j, Y H:i:s') }}
                                <span class="block text-[10px] text-slate-450">{{ $log->created_at->diffForHumans() }}</span>
                            </td>

                            <!-- Domain -->
                            <td class="py-4 px-6 font-mono font-bold text-slate-800">
                                {{ $log->domain }}
                            </td>

                            <!-- Purchase Code -->
                            <td class="py-4 px-6 font-mono text-slate-500 select-all">
                                {{ $log->purchase_code }}
                            </td>

                            <!-- Client IP -->
                            <td class="py-4 px-6 text-slate-600 font-mono">
                                {{ $log->ip_address }}
                            </td>

                            <!-- Result Badge -->
                            <td class="py-4 px-6">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $log->status === 'success' ? 'bg-emerald-50 text-emerald-700 border border-emerald-100' : 'bg-rose-50 text-rose-700 border border-rose-100' }}">
                                    {{ $log->status === 'success' ? 'Passed' : 'Blocked' }}
                                </span>
                            </td>

                            <!-- Message -->
                            <td class="py-4 px-6 text-slate-500 max-w-xs truncate" title="{{ $log->message }}">
                                {{ $log->message ?: 'No additional info' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 px-6 text-center text-slate-400">
                                <svg class="mx-auto h-12 w-12 text-slate-300 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                                </svg>
                                <span class="block font-medium text-sm text-slate-500 mb-1">No Logs Recorded</span>
                                <span class="block text-xs">Verification requests will show up here.</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($logs->hasPages())
            <div class="px-6 py-4 border-t border-slate-100 bg-slate-50">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
