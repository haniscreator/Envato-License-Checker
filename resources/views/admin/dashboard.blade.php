@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page_title', 'Analytics Dashboard')

@section('content')
<div class="space-y-8">
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Card 1: Total -->
        <div class="bg-white border border-slate-200 p-6 rounded-3xl shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Total Licenses</span>
                <span class="text-3xl font-extrabold text-slate-900 mt-2 block">{{ $stats['total_licenses'] }}</span>
            </div>
            <div class="h-12 w-12 rounded-2xl bg-indigo-50 flex items-center justify-center text-indigo-500">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                </svg>
            </div>
        </div>

        <!-- Card 2: Active -->
        <div class="bg-white border border-slate-200 p-6 rounded-3xl shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Active Licenses</span>
                <span class="text-3xl font-extrabold text-emerald-600 mt-2 block">{{ $stats['active_licenses'] }}</span>
            </div>
            <div class="h-12 w-12 rounded-2xl bg-emerald-50 flex items-center justify-center text-emerald-500">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>

        <!-- Card 3: Revoked -->
        <div class="bg-white border border-slate-200 p-6 rounded-3xl shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Revoked Licenses</span>
                <span class="text-3xl font-extrabold text-rose-600 mt-2 block">{{ $stats['revoked_licenses'] }}</span>
            </div>
            <div class="h-12 w-12 rounded-2xl bg-rose-50 flex items-center justify-center text-rose-500">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
        </div>

        <!-- Card 4: Checks 24h -->
        <div class="bg-white border border-slate-200 p-6 rounded-3xl shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">API Checks (24h)</span>
                <span class="text-3xl font-extrabold text-slate-900 mt-2 block">{{ $stats['checks_24h'] }}</span>
            </div>
            <div class="h-12 w-12 rounded-2xl bg-slate-100 flex items-center justify-center text-slate-500">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
            </div>
        </div>
    </div>

    <!-- Chart & Info Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- 7 Days API Traffic Chart -->
        <div class="bg-white border border-slate-200 rounded-3xl p-6 shadow-sm lg:col-span-2">
            <h3 class="text-lg font-bold text-slate-900 mb-2">API Traffic (Last 7 Days)</h3>
            <p class="text-xs text-slate-400 mb-6">Total successful and failed verification checks received.</p>

            <!-- Chart container -->
            <div class="h-64 flex items-end justify-between px-2 pt-4 relative">
                <!-- Background lines -->
                <div class="absolute inset-0 flex flex-col justify-between pointer-events-none border-b border-slate-100 pb-8">
                    <div class="border-t border-slate-100/80 w-full h-0"></div>
                    <div class="border-t border-slate-100/80 w-full h-0"></div>
                    <div class="border-t border-slate-100/80 w-full h-0"></div>
                </div>

                @php
                    $maxVal = 1;
                    foreach ($chartData as $d) {
                        $total = $d['success'] + $d['failed'];
                        if ($total > $maxVal) { $maxVal = $total; }
                    }
                @endphp

                <!-- Bars -->
                @foreach ($chartData as $data)
                    @php
                        $successHeight = $maxVal > 0 ? ($data['success'] / $maxVal) * 100 : 0;
                        $failedHeight = $maxVal > 0 ? ($data['failed'] / $maxVal) * 100 : 0;
                    @endphp
                    <div class="flex flex-col items-center flex-1 group z-10">
                        <div class="w-full flex items-end justify-center space-x-1.5 h-48 mb-2">
                            <!-- Success bar -->
                            <div style="height: {{ max($successHeight, 2) }}%;" class="w-4 bg-indigo-500 rounded-t-md transition-all duration-300 group-hover:bg-indigo-600 relative" title="Success: {{ $data['success'] }}">
                                <div class="absolute bottom-full left-1/2 -translate-x-1/2 bg-slate-900 text-white text-[10px] px-1.5 py-0.5 rounded shadow opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap mb-1 z-20 pointer-events-none">
                                    {{ $data['success'] }} OK
                                </div>
                            </div>
                            <!-- Failed bar -->
                            @if ($data['failed'] > 0)
                                <div style="height: {{ max($failedHeight, 2) }}%;" class="w-4 bg-rose-400 rounded-t-md transition-all duration-300 group-hover:bg-rose-500 relative" title="Failed: {{ $data['failed'] }}">
                                    <div class="absolute bottom-full left-1/2 -translate-x-1/2 bg-slate-900 text-white text-[10px] px-1.5 py-0.5 rounded shadow opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap mb-1 z-20 pointer-events-none">
                                        {{ $data['failed'] }} Failed
                                    </div>
                                </div>
                            @endif
                        </div>
                        <span class="text-xs font-semibold text-slate-500">{{ $data['label'] }}</span>
                    </div>
                @endforeach
            </div>

            <!-- Legend -->
            <div class="flex items-center space-x-4 justify-center mt-6 text-xs font-semibold">
                <div class="flex items-center">
                    <span class="h-3 w-3 bg-indigo-500 rounded-full mr-2"></span>
                    <span class="text-slate-600">Success Verification</span>
                </div>
                <div class="flex items-center">
                    <span class="h-3 w-3 bg-rose-400 rounded-full mr-2"></span>
                    <span class="text-slate-600">Failed Check / Blocked</span>
                </div>
            </div>
        </div>

        <!-- 24h Summary card -->
        <div class="bg-gradient-to-br from-slate-900 to-indigo-950 text-white border border-slate-800 rounded-3xl p-6 shadow-xl flex flex-col justify-between relative overflow-hidden">
            <div class="absolute right-[-10%] top-[-10%] h-32 w-32 rounded-full bg-indigo-500/10 blur-xl"></div>
            <div>
                <h3 class="text-lg font-bold">24h Verification Summary</h3>
                <p class="text-xs text-indigo-200 mt-1">Status of API queries in the past day.</p>

                <div class="mt-8 space-y-4">
                    <div>
                        <div class="flex justify-between text-sm font-semibold mb-1">
                            <span>Successful Activations</span>
                            <span>{{ $stats['checks_24h'] > 0 ? round(($stats['checks_success_24h'] / $stats['checks_24h']) * 100) : 0 }}%</span>
                        </div>
                        <div class="w-full bg-slate-800 h-2.5 rounded-full overflow-hidden">
                            <div class="bg-emerald-400 h-full rounded-full" style="width: {{ $stats['checks_24h'] > 0 ? ($stats['checks_success_24h'] / $stats['checks_24h']) * 100 : 0 }}%"></div>
                        </div>
                        <span class="text-[10px] text-slate-400 mt-1 block">{{ $stats['checks_success_24h'] }} requests allowed</span>
                    </div>

                    <div>
                        <div class="flex justify-between text-sm font-semibold mb-1">
                            <span>Failed Requests</span>
                            <span>{{ $stats['checks_24h'] > 0 ? round(($stats['checks_failed_24h'] / $stats['checks_24h']) * 100) : 0 }}%</span>
                        </div>
                        <div class="w-full bg-slate-800 h-2.5 rounded-full overflow-hidden">
                            <div class="bg-rose-400 h-full rounded-full" style="width: {{ $stats['checks_24h'] > 0 ? ($stats['checks_failed_24h'] / $stats['checks_24h']) * 100 : 0 }}%"></div>
                        </div>
                        <span class="text-[10px] text-slate-400 mt-1 block">{{ $stats['checks_failed_24h'] }} requests rejected</span>
                    </div>
                </div>
            </div>

            <div class="mt-8 border-t border-slate-800 pt-4 flex items-center justify-between text-xs text-slate-400">
                <span>Total processed:</span>
                <span class="font-bold text-white text-sm">{{ $stats['checks_24h'] }}</span>
            </div>
        </div>
    </div>

    <!-- Recent Logs and Licenses -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Licenses Card -->
        <div class="bg-white border border-slate-200 rounded-3xl p-6 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-base font-bold text-slate-900">Recent Licenses</h3>
                <a href="{{ route('admin.licenses.index') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-500">View All</a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 text-[10px] font-bold uppercase tracking-wider text-slate-400">
                            <th class="py-3">Purchase Code</th>
                            <th class="py-3">Domain</th>
                            <th class="py-3">Buyer</th>
                            <th class="py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs">
                        @forelse ($recentLicenses as $license)
                            <tr>
                                <td class="py-3 font-semibold text-slate-900 truncate max-w-[120px]">{{ $license->purchase_code }}</td>
                                <td class="py-3 text-slate-600 font-mono">{{ $license->domain }}</td>
                                <td class="py-3 text-slate-600">{{ $license->buyer_username }}</td>
                                <td class="py-3">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium {{ $license->status === 'active' ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">
                                        {{ ucfirst($license->status) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-4 text-center text-slate-400">No licenses registered yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Logs Card -->
        <div class="bg-white border border-slate-200 rounded-3xl p-6 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-base font-bold text-slate-900">Recent API Checks</h3>
                <a href="{{ route('admin.logs.index') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-500">View All</a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 text-[10px] font-bold uppercase tracking-wider text-slate-400">
                            <th class="py-3">Time</th>
                            <th class="py-3">Domain</th>
                            <th class="py-3">Code</th>
                            <th class="py-3">Result</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs">
                        @forelse ($recentLogs as $log)
                            <tr>
                                <td class="py-3 text-slate-400">{{ $log->created_at->diffForHumans() }}</td>
                                <td class="py-3 text-slate-600 font-mono truncate max-w-[120px]" title="{{ $log->domain }}">{{ $log->domain }}</td>
                                <td class="py-3 text-slate-500 font-mono truncate max-w-[100px]">{{ $log->purchase_code }}</td>
                                <td class="py-3">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium {{ $log->status === 'success' ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">
                                        {{ $log->status === 'success' ? 'Passed' : 'Blocked' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-4 text-center text-slate-400">No check logs recorded yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
