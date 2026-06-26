@extends('admin.layouts.app')

@section('title', 'Dashboard')
@section('header_title', 'Dashboard Overview')

@php $activeTab = 'dashboard'; @endphp

@section('content')
<div class="space-y-8">
    <!-- Welcome Banner -->
    <div class="bg-gradient-to-r from-wedding-charcoal-dark to-[#2c1215] p-6 sm:p-8 rounded-3xl text-white shadow-xl relative overflow-hidden flex flex-col sm:flex-row items-center justify-between gap-4 border border-wedding-pink-dark/20 animate-fadeIn">
        <div class="z-10 space-y-2 max-w-xl w-full">
            <span class="px-3 py-1 bg-wedding-pink-dark/25 border border-wedding-pink-dark/30 text-wedding-pink-light text-xs font-semibold uppercase tracking-wider rounded-full inline-block">
                CMS Center
            </span>
            <h3 class="text-xl sm:text-2xl font-black tracking-tight mt-2">Welcome Back, <span id="welcome-name">Super Admin</span>! 👏</h3>
            <p class="text-gray-300 text-sm leading-relaxed font-medium">
                Manage your categories, custom fonts, invitation layouts, and canvas vectors. Monitor user invites and template downloads effortlessly.
            </p>
        </div>
        <div class="z-10 flex gap-4 w-full sm:w-auto">
            <a href="{{ route('admin.templates.editor') }}" class="flex items-center justify-center gap-2 px-5 py-3 bg-wedding-gold-accent hover:bg-wedding-gold-dark text-wedding-charcoal-dark text-sm font-black rounded-2xl shadow-lg shadow-wedding-gold-accent/20 transition-all duration-300 transform hover:-translate-y-0.5 w-full sm:w-auto">
                <i data-lucide="plus-circle" class="w-5 h-5 text-wedding-charcoal-dark shrink-0"></i>
                Add Template
            </a>
        </div>
        <div class="absolute right-0 bottom-0 opacity-10 pointer-events-none transform translate-y-12 hidden sm:block">
            <i data-lucide="palette" class="w-96 h-96 text-wedding-gold-accent"></i>
        </div>
    </div>

    <!-- Tab Switcher -->
    <div class="flex gap-2 p-1 bg-[#FFF5F6]/45 border border-[#FFCAD2]/40 rounded-2xl w-fit shadow-xs overflow-x-auto">
        <button onclick="switchTab('general')" id="tab-btn-general" class="tab-btn flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl text-xs font-bold transition-all duration-300 bg-wedding-charcoal-dark text-wedding-gold-light shadow-md">
            <i data-lucide="activity" class="w-3.5 h-3.5"></i>
            <span>General Overview</span>
        </button>
        <button onclick="switchTab('revenue')" id="tab-btn-revenue" class="tab-btn flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl text-xs font-bold transition-all duration-300 text-gray-500 hover:text-wedding-charcoal-dark hover:bg-white/50">
            <i data-lucide="coins" class="w-3.5 h-3.5"></i>
            <span>Revenue Dashboard</span>
        </button>
    </div>

    <!-- Loading Indicator -->
    <div id="dashboard-loading" class="flex flex-col items-center justify-center min-h-[40vh] gap-3">
        <div class="w-12 h-12 border-4 border-wedding-pink-medium border-t-wedding-pink-dark rounded-full animate-spin"></div>
        <p class="text-sm font-medium text-wedding-pink-dark">Assembling your wedding dashboard...</p>
    </div>

    <!-- General Tab Panel -->
    <div id="panel-general" class="tab-panel space-y-8 hidden">
        <!-- Stats Cards Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Total Users -->
            <div class="bg-wedding-card border border-wedding-pink-medium/30 p-6 rounded-2xl flex items-center justify-between shadow-xs hover:shadow-md transition-all duration-300">
                <div class="space-y-1">
                    <p class="text-[10px] text-gray-500 font-extrabold uppercase tracking-wider">Total Users</p>
                    <h4 id="stat-total-users" class="text-3xl font-extrabold text-wedding-charcoal-dark tracking-tight">-</h4>
                </div>
                <div class="p-4 rounded-2xl bg-wedding-pink-light text-wedding-pink-dark border border-wedding-pink-medium/30">
                    <i data-lucide="users" class="w-6 h-6 text-wedding-pink-dark"></i>
                </div>
            </div>
            <!-- Total Templates -->
            <div class="bg-wedding-card border border-wedding-gold-accent/30 p-6 rounded-2xl flex items-center justify-between shadow-xs hover:shadow-md transition-all duration-300">
                <div class="space-y-1">
                    <p class="text-[10px] text-gray-500 font-extrabold uppercase tracking-wider">Total Templates</p>
                    <h4 id="stat-total-templates" class="text-3xl font-extrabold text-wedding-charcoal-dark tracking-tight">-</h4>
                </div>
                <div class="p-4 rounded-2xl bg-wedding-gold-light text-wedding-gold-dark border border-wedding-gold-accent/30">
                    <i data-lucide="palette" class="w-6 h-6 text-wedding-gold-dark"></i>
                </div>
            </div>
            <!-- Active Categories -->
            <div class="bg-wedding-card border border-green-500/20 p-6 rounded-2xl flex items-center justify-between shadow-xs hover:shadow-md transition-all duration-300">
                <div class="space-y-1">
                    <p class="text-[10px] text-gray-500 font-extrabold uppercase tracking-wider">Active Categories</p>
                    <h4 id="stat-active-categories" class="text-3xl font-extrabold text-wedding-charcoal-dark tracking-tight">-</h4>
                </div>
                <div class="p-4 rounded-2xl bg-green-50 text-green-600 border border-green-200">
                    <i data-lucide="folder-heart" class="w-6 h-6 text-green-600"></i>
                </div>
            </div>
            <!-- Premium Invitations -->
            <div class="bg-wedding-card border border-purple-500/20 p-6 rounded-2xl flex items-center justify-between shadow-xs hover:shadow-md transition-all duration-300">
                <div class="space-y-1">
                    <p class="text-[10px] text-gray-500 font-extrabold uppercase tracking-wider">Premium Invitations</p>
                    <h4 id="stat-premium-templates" class="text-3xl font-extrabold text-wedding-charcoal-dark tracking-tight">-</h4>
                </div>
                <div class="p-4 rounded-2xl bg-purple-50 text-purple-600 border border-purple-200">
                    <i data-lucide="sparkles" class="w-6 h-6 text-purple-600"></i>
                </div>
            </div>
            <!-- Invitations Created -->
            <div class="bg-wedding-card border border-blue-500/20 p-6 rounded-2xl flex items-center justify-between shadow-xs hover:shadow-md transition-all duration-300">
                <div class="space-y-1">
                    <p class="text-[10px] text-gray-500 font-extrabold uppercase tracking-wider">Total Created</p>
                    <h4 id="stat-total-created" class="text-3xl font-extrabold text-wedding-charcoal-dark tracking-tight">-</h4>
                </div>
                <div class="p-4 rounded-2xl bg-blue-50 text-blue-600 border border-blue-200">
                    <i data-lucide="download" class="w-6 h-6 text-blue-600"></i>
                </div>
            </div>
            <!-- User Drafts -->
            <div class="bg-wedding-card border border-amber-500/20 p-6 rounded-2xl flex items-center justify-between shadow-xs hover:shadow-md transition-all duration-300">
                <div class="space-y-1">
                    <p class="text-[10px] text-gray-500 font-extrabold uppercase tracking-wider">User Drafts</p>
                    <h4 id="stat-total-drafts" class="text-3xl font-extrabold text-wedding-charcoal-dark tracking-tight">-</h4>
                </div>
                <div class="p-4 rounded-2xl bg-amber-50 text-amber-600 border border-amber-200">
                    <i data-lucide="file-text" class="w-6 h-6 text-amber-600"></i>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 lg:gap-8">
            <!-- User Growth Chart -->
            <div class="bg-wedding-card border border-wedding-pink-medium/20 p-6 sm:p-8 rounded-3xl shadow-xs space-y-4 relative overflow-hidden">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
                    <div>
                        <h4 class="text-sm sm:text-base font-bold text-wedding-charcoal-dark tracking-tight">Active User Growth</h4>
                        <p class="text-[10px] sm:text-xs text-gray-500 font-semibold">Monthly registration count trend</p>
                    </div>
                    <div class="relative w-full sm:w-auto">
                        <select onchange="updateGrowthRange(this.value)" id="growth-range-select" class="appearance-none pl-8 pr-8 py-1.5 bg-[#FFF0F2] hover:bg-[#FFE5E8] border border-[#FFCAD2] text-wedding-pink-dark text-xs font-bold rounded-xl shadow-xs transition-colors focus:outline-none cursor-pointer w-full sm:w-auto">
                            <option value="6m">Last 6 Months</option>
                            <option value="12m">Last 12 Months</option>
                            <option value="this_year">This Year</option>
                            <option value="last_year">Last Year</option>
                        </select>
                        <i data-lucide="calendar" class="w-3.5 h-3.5 text-wedding-pink-dark absolute left-2.5 top-1/2 transform -translate-y-1/2 pointer-events-none"></i>
                        <i data-lucide="chevron-down" class="w-3 h-3 text-wedding-pink-dark absolute right-2.5 top-1/2 transform -translate-y-1/2 pointer-events-none"></i>
                    </div>
                </div>
                <!-- Growth SVG Chart -->
                <div class="h-56 sm:h-64 w-full relative flex items-end pt-4" id="growth-chart-container">
                    <!-- SVG gets dynamically written here -->
                </div>
            </div>

            <!-- Distribution Chart -->
            <div class="bg-wedding-card border border-wedding-pink-medium/20 p-6 sm:p-8 rounded-3xl shadow-xs space-y-4 relative overflow-hidden">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
                    <div>
                        <h4 class="text-sm sm:text-base font-bold text-wedding-charcoal-dark tracking-tight">Template Distribution</h4>
                        <p class="text-[10px] sm:text-xs text-gray-500 font-semibold">Number of layouts by categories</p>
                    </div>
                    <div class="relative w-full sm:w-auto">
                        <select onchange="updateDistRange(this.value)" id="dist-range-select" class="appearance-none pl-8 pr-8 py-1.5 bg-[#FFF0F2] hover:bg-[#FFE5E8] border border-[#FFCAD2] text-wedding-pink-dark text-xs font-bold rounded-xl shadow-xs transition-colors focus:outline-none cursor-pointer w-full sm:w-auto">
                            <option value="this_month">This Month</option>
                            <option value="last_month">Last Month</option>
                            <option value="this_year">This Year</option>
                            <option value="all_time">All Time</option>
                        </select>
                        <i data-lucide="calendar" class="w-3.5 h-3.5 text-wedding-pink-dark absolute left-2.5 top-1/2 transform -translate-y-1/2 pointer-events-none"></i>
                        <i data-lucide="chevron-down" class="w-3 h-3 text-wedding-pink-dark absolute right-2.5 top-1/2 transform -translate-y-1/2 pointer-events-none"></i>
                    </div>
                </div>
                <!-- Distribution SVG Chart -->
                <div class="h-56 sm:h-64 w-full relative flex items-end pt-4" id="dist-chart-container">
                    <!-- SVG gets dynamically written here -->
                </div>
            </div>
        </div>

        <!-- Lists Row -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 lg:gap-8">
            <!-- Activity Log -->
            <div class="bg-wedding-card border border-wedding-pink-medium/20 p-6 rounded-3xl shadow-xs lg:col-span-2 space-y-4">
                <div class="flex justify-between items-center">
                    <h4 class="text-sm sm:text-base font-bold text-wedding-charcoal-dark tracking-tight">Recent Activity Log</h4>
                    <a href="{{ route('admin.audit-logs') }}" class="text-xs text-wedding-pink-dark font-semibold hover:underline flex items-center gap-0.5">
                        View all log <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-wedding-pink-dark shrink-0"></i>
                    </a>
                </div>
                <div id="recent-activities-list" class="divide-y divide-wedding-pink-medium/15">
                    <!-- Dynamic Activity Logs -->
                </div>
            </div>

            <!-- Top Templates -->
            <div class="bg-wedding-card border border-wedding-pink-medium/20 p-6 rounded-3xl shadow-xs space-y-4">
                <h4 class="text-sm sm:text-base font-bold text-wedding-charcoal-dark tracking-tight">Top Templates</h4>
                <div id="top-templates-list" class="space-y-4">
                    <!-- Dynamic top templates -->
                </div>
            </div>
        </div>
    </div>

    <!-- Revenue Tab Panel -->
    <div id="panel-revenue" class="tab-panel space-y-8 hidden">
        <!-- Stats Cards Grid -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4 sm:gap-6">
            <div class="bg-wedding-card border border-emerald-500/20 p-4 sm:p-6 rounded-2xl flex flex-col sm:flex-row items-center justify-between gap-2 shadow-xs">
                <div class="space-y-1 text-center sm:text-left">
                    <p class="text-[8px] sm:text-[10px] text-gray-500 font-extrabold uppercase tracking-wider">Total Revenue</p>
                    <h4 id="stat-total-revenue" class="text-base sm:text-2xl font-extrabold text-wedding-charcoal-dark tracking-tight">₹-</h4>
                </div>
                <div class="p-2 sm:p-3 rounded-xl bg-emerald-50 text-emerald-600 border border-emerald-200 shadow-xs">
                    <i data-lucide="coins" class="w-5 h-5 text-emerald-600"></i>
                </div>
            </div>
            <div class="bg-wedding-card border border-blue-500/20 p-4 sm:p-6 rounded-2xl flex flex-col sm:flex-row items-center justify-between gap-2 shadow-xs">
                <div class="space-y-1 text-center sm:text-left">
                    <p class="text-[8px] sm:text-[10px] text-gray-500 font-extrabold uppercase tracking-wider">Monthly Revenue</p>
                    <h4 id="stat-monthly-revenue" class="text-base sm:text-2xl font-extrabold text-wedding-charcoal-dark tracking-tight">₹-</h4>
                </div>
                <div class="p-2 sm:p-3 rounded-xl bg-blue-50 text-blue-600 border border-blue-200 shadow-xs">
                    <i data-lucide="trending-up" class="w-5 h-5 text-blue-600"></i>
                </div>
            </div>
            <div class="bg-wedding-card border border-wedding-pink-medium/30 p-4 sm:p-6 rounded-2xl flex flex-col sm:flex-row items-center justify-between gap-2 shadow-xs">
                <div class="space-y-1 text-center sm:text-left">
                    <p class="text-[8px] sm:text-[10px] text-gray-500 font-extrabold uppercase tracking-wider">Paid Subscribers</p>
                    <h4 id="stat-paid-subscribers" class="text-base sm:text-2xl font-extrabold text-wedding-charcoal-dark tracking-tight">-</h4>
                </div>
                <div class="p-2 sm:p-3 rounded-xl bg-wedding-pink-light text-wedding-pink-dark border border-wedding-pink-medium/30 shadow-xs">
                    <i data-lucide="users" class="w-5 h-5 text-wedding-pink-dark"></i>
                </div>
            </div>
            <div class="bg-wedding-card border border-purple-500/20 p-4 sm:p-6 rounded-2xl flex flex-col sm:flex-row items-center justify-between gap-2 shadow-xs">
                <div class="space-y-1 text-center sm:text-left">
                    <p class="text-[8px] sm:text-[10px] text-gray-500 font-extrabold uppercase tracking-wider">Active Trials</p>
                    <h4 id="stat-active-trials" class="text-base sm:text-2xl font-extrabold text-wedding-charcoal-dark tracking-tight">-</h4>
                </div>
                <div class="p-2 sm:p-3 rounded-xl bg-purple-50 text-purple-600 border border-purple-200 shadow-xs">
                    <i data-lucide="sparkles" class="w-5 h-5 text-purple-600"></i>
                </div>
            </div>
            <div class="bg-wedding-card border border-red-500/20 p-4 sm:p-6 rounded-2xl flex flex-col sm:flex-row items-center justify-between gap-2 shadow-xs">
                <div class="space-y-1 text-center sm:text-left">
                    <p class="text-[8px] sm:text-[10px] text-gray-500 font-extrabold uppercase tracking-wider">Churn Rate</p>
                    <h4 id="stat-churn-rate" class="text-base sm:text-2xl font-extrabold text-wedding-charcoal-dark tracking-tight">-%</h4>
                </div>
                <div class="p-2 sm:p-3 rounded-xl bg-red-50 text-red-600 border border-red-200 shadow-xs">
                    <i data-lucide="percent" class="w-5 h-5 text-red-600"></i>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 lg:gap-8">
            <!-- Subscription Growth Chart -->
            <div class="bg-wedding-card border border-wedding-pink-medium/20 p-6 sm:p-8 rounded-3xl shadow-xs lg:col-span-2 space-y-4 relative overflow-hidden">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
                    <div>
                        <h4 class="text-sm sm:text-base font-bold text-wedding-charcoal-dark tracking-tight">Subscription Growth</h4>
                        <p class="text-[10px] sm:text-xs text-gray-500 font-semibold">Active subscribers over the past 6 months</p>
                    </div>
                    <div class="relative w-full sm:w-auto">
                        <select onchange="updateSubGrowthRange(this.value)" id="sub-growth-range-select" class="appearance-none pl-8 pr-8 py-1.5 bg-[#FFF0F2] hover:bg-[#FFE5E8] border border-[#FFCAD2] text-wedding-pink-dark text-xs font-bold rounded-xl shadow-xs transition-colors focus:outline-none cursor-pointer w-full sm:w-auto">
                            <option value="6m">Last 6 Months</option>
                            <option value="12m">Last 12 Months</option>
                            <option value="this_year">This Year</option>
                            <option value="last_year">Last Year</option>
                        </select>
                        <i data-lucide="calendar" class="w-3.5 h-3.5 text-wedding-pink-dark absolute left-2.5 top-1/2 transform -translate-y-1/2 pointer-events-none"></i>
                        <i data-lucide="chevron-down" class="w-3 h-3 text-wedding-pink-dark absolute right-2.5 top-1/2 transform -translate-y-1/2 pointer-events-none"></i>
                    </div>
                </div>
                <!-- Subscription SVG Chart -->
                <div class="h-56 sm:h-64 w-full relative flex items-end pt-4" id="sub-growth-chart-container">
                    <!-- SVG gets dynamically written here -->
                </div>
            </div>

            <!-- Mini stats checklist -->
            <div class="bg-wedding-card border border-wedding-pink-medium/20 p-6 rounded-3xl shadow-xs space-y-4">
                <h4 class="text-sm sm:text-base font-bold text-wedding-charcoal-dark tracking-tight">Transaction Overview</h4>
                <div class="divide-y divide-wedding-pink-medium/15 text-xs font-bold text-wedding-charcoal-dark">
                    <div class="py-3.5 flex justify-between">
                        <span class="text-gray-500">Monthly Subscriptions</span>
                        <span id="stat-monthly-subs-count">-</span>
                    </div>
                    <div class="py-3.5 flex justify-between">
                        <span class="text-gray-500">Yearly Subscriptions</span>
                        <span id="stat-yearly-subs-count">-</span>
                    </div>
                    <div class="py-3.5 flex justify-between">
                        <span class="text-gray-500">Single Purchases</span>
                        <span id="stat-single-purchases-count">-</span>
                    </div>
                    <div class="py-3.5 flex justify-between">
                        <span class="text-gray-500">Total Transactions</span>
                        <span id="stat-total-txns-count">-</span>
                    </div>
                    <div class="py-3.5 flex justify-between">
                        <span class="text-gray-500">Success Ratio</span>
                        <span id="stat-success-ratio">-</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Transactions Log -->
        <div class="bg-wedding-card border border-wedding-pink-medium/20 p-6 rounded-3xl shadow-xs space-y-4">
            <h4 class="text-sm sm:text-base font-bold text-wedding-charcoal-dark tracking-tight">Recent Transaction Records</h4>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs font-semibold text-wedding-charcoal-dark">
                    <thead>
                        <tr class="border-b border-wedding-pink-medium/35 text-gray-500">
                            <th class="py-3 px-2">Transaction ID</th>
                            <th class="py-3 px-2">User Email</th>
                            <th class="py-3 px-2">Type</th>
                            <th class="py-3 px-2">Amount</th>
                            <th class="py-3 px-2">Status</th>
                            <th class="py-3 px-2">Time</th>
                        </tr>
                    </thead>
                    <tbody id="transactions-table-body">
                        <!-- Dynamic Transaction Records -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Variables holding active settings
    let userGrowthRange = '6m';
    let distributionRange = 'this_month';
    let subGrowthRange = '6m';

    // Set administrator display name
    if (window.CurrentUser && window.CurrentUser.displayName) {
        document.getElementById('welcome-name').innerText = window.CurrentUser.displayName;
    }

    // Initialize layout display
    switchTab('general');

    // Polling triggers every 10 seconds to sync stats
    loadDashboardData();
    setInterval(loadDashboardData, 10000);

    function switchTab(tab) {
        // Toggle tab buttons
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('bg-wedding-charcoal-dark', 'text-wedding-gold-light', 'shadow-md');
            btn.classList.add('text-gray-500', 'hover:text-wedding-charcoal-dark', 'hover:bg-white/50');
        });
        document.getElementById(`tab-btn-${tab}`).classList.add('bg-wedding-charcoal-dark', 'text-wedding-gold-light', 'shadow-md');
        document.getElementById(`tab-btn-${tab}`).classList.remove('text-gray-500', 'hover:text-wedding-charcoal-dark', 'hover:bg-white/50');

        // Toggle panels
        document.querySelectorAll('.tab-panel').forEach(panel => panel.classList.add('hidden'));
        document.getElementById(`panel-${tab}`).classList.remove('hidden');
    }

    function updateGrowthRange(val) {
        userGrowthRange = val;
        loadDashboardData();
    }

    function updateDistRange(val) {
        distributionRange = val;
        loadDashboardData();
    }

    function updateSubGrowthRange(val) {
        subGrowthRange = val;
        loadDashboardData();
    }

    async function loadDashboardData() {
        try {
            const headers = {
                'x-user-id': window.CurrentUser ? window.CurrentUser.id : 'admin_super',
                'Content-Type': 'application/json'
            };

            const [resSummary, resCharts, resSubSummary, resTxSummary, resTxList] = await Promise.all([
                fetch(`/api/analytics/summary`, { headers }).then(r => r.ok ? r.json() : null),
                fetch(`/api/analytics/charts?userGrowthRange=${userGrowthRange}&distributionRange=${distributionRange}`, { headers }).then(r => r.ok ? r.json() : null),
                fetch(`/api/analytics/subscription-summary?subGrowthRange=${subGrowthRange}`, { headers }).then(r => r.ok ? r.json() : null),
                fetch(`/api/transactions/stats/summary`, { headers }).then(r => r.ok ? r.json() : null),
                fetch(`/api/transactions`, { headers }).then(r => r.ok ? r.json() : null),
            ]);

            // Hide loading indicator
            document.getElementById('dashboard-loading').classList.add('hidden');
            document.getElementById('panel-general').classList.remove('hidden');

            // Render stats data
            if (resSummary) {
                const counters = resSummary.counters || {};
                document.getElementById('stat-total-users').innerText = counters.totalUsers ?? 0;
                document.getElementById('stat-total-templates').innerText = counters.totalTemplates ?? 0;
                document.getElementById('stat-active-categories').innerText = counters.totalCategories ?? 0;
                document.getElementById('stat-premium-templates').innerText = counters.premiumTemplates ?? 0;
                document.getElementById('stat-total-created').innerText = counters.totalInvitations ?? 0;
                document.getElementById('stat-total-drafts').innerText = counters.totalDrafts ?? 0;

                // Render activities
                let actHTML = '';
                (resSummary.recentActivities || []).forEach(act => {
                    actHTML += `
                        <div class="py-4 first:pt-0 last:pb-0 flex gap-4 items-start">
                            <div class="w-8 h-8 rounded-full bg-wedding-pink-light text-wedding-pink-dark flex items-center justify-center shrink-0 border border-wedding-pink-medium/20 shadow-xs">
                                <i data-lucide="calendar" class="w-4 h-4 text-wedding-pink-dark"></i>
                            </div>
                            <div class="space-y-1 flex-1 min-w-0">
                                <p class="text-sm font-semibold text-wedding-charcoal-dark">${act.action}</p>
                                <div class="flex gap-2 text-xs text-gray-500 font-medium">
                                    <span class="font-semibold text-wedding-pink-dark">${act.user}</span>
                                    <span>•</span>
                                    <span>${act.time}</span>
                                </div>
                            </div>
                        </div>
                    `;
                });
                document.getElementById('recent-activities-list').innerHTML = actHTML || '<p class="text-xs text-gray-400 font-bold py-4">No recent activities logged.</p>';

                // Render popular templates
                let tplHTML = '';
                (resSummary.topTemplates || []).forEach(tpl => {
                    tplHTML += `
                        <div class="p-4 bg-wedding-pink-light/35 border border-wedding-pink-medium/25 rounded-2xl flex items-center justify-between gap-2">
                            <div class="space-y-1 flex-1 min-w-0">
                                <h5 class="text-sm font-bold text-wedding-charcoal-dark truncate">${tpl.name}</h5>
                                <span class="px-2 py-0.5 bg-wedding-pink-light text-wedding-pink-dark text-[9px] font-bold rounded-md uppercase border border-wedding-pink-medium/30">
                                    ${tpl.isPremium ? 'Premium' : 'Free'}
                                </span>
                            </div>
                            <div class="flex items-center gap-1 text-wedding-pink-dark text-xs font-semibold bg-wedding-pink-light px-3 py-1.5 rounded-xl border border-wedding-pink-medium/20 shadow-xs shrink-0">
                                <i data-lucide="download" class="w-3.5 h-3.5 text-wedding-pink-dark"></i>
                                <span>${tpl.downloads} downloads</span>
                            </div>
                        </div>
                    `;
                });
                document.getElementById('top-templates-list').innerHTML = tplHTML || '<p class="text-xs text-gray-400 font-bold">No downloaded templates.</p>';
            }

            // Render charts
            if (resCharts) {
                renderGrowthChart(resCharts.userGrowthTrend || []);
                renderDistributionChart(resCharts.categoryDistribution || []);
            }

            // Render subscription metrics
            if (resSubSummary) {
                const totalActive = resSubSummary.totalSubscribers - resSubSummary.activeTrials;
                document.getElementById('stat-paid-subscribers').innerText = totalActive >= 0 ? totalActive : 0;
                document.getElementById('stat-active-trials').innerText = resSubSummary.activeTrials ?? 0;
                document.getElementById('stat-monthly-revenue').innerText = `₹${resSubSummary.monthlyRevenue ?? 0}`;
                document.getElementById('stat-churn-rate').innerText = `${resSubSummary.churnRate ?? 0}%`;

                renderSubGrowthChart(resSubSummary.growthTrend || []);
            }

            // Render transaction stats
            if (resTxSummary) {
                document.getElementById('stat-total-revenue').innerText = `₹${resTxSummary.totalRevenue ?? 0}`;
                document.getElementById('stat-monthly-subs-count').innerText = resTxSummary.monthlySubscriptions ?? 0;
                document.getElementById('stat-yearly-subs-count').innerText = resTxSummary.yearlySubscriptions ?? 0;
                document.getElementById('stat-single-purchases-count').innerText = resTxSummary.singlePurchases ?? 0;
                document.getElementById('stat-total-txns-count').innerText = resTxSummary.totalTransactions ?? 0;
                
                const ratio = resTxSummary.totalTransactions > 0 
                    ? Math.round((resTxSummary.successfulTransactions / resTxSummary.totalTransactions) * 100)
                    : 100;
                document.getElementById('stat-success-ratio').innerText = `${ratio}%`;
            }

            // Render transaction records table
            if (resTxList) {
                let txHTML = '';
                resTxList.slice(0, 8).forEach(tx => {
                    const isSuccess = tx.status === 'success';
                    const badgeClass = isSuccess 
                        ? 'bg-green-50 text-green-700 border border-green-200' 
                        : 'bg-red-50 text-red-700 border border-red-200';
                    const time = tx.timestamp ? new Date(tx.timestamp).toLocaleString('en-GB', { hour: '2-digit', minute: '2-digit', day: '2-digit', month: '2-digit' }) : '-';
                    txHTML += `
                        <tr class="border-b border-wedding-pink-medium/15 hover:bg-wedding-pink-light/10">
                            <td class="py-3 px-2 font-mono text-[11px]">${tx.id.substring(0, 8)}...</td>
                            <td class="py-3 px-2 truncate max-w-[120px]">${tx.userEmail ?? 'anonymous'}</td>
                            <td class="py-3 px-2 capitalize">${tx.type === 'subscription' ? 'Subscription' : 'Card Buy'}</td>
                            <td class="py-3 px-2 font-extrabold text-wedding-pink-dark">₹${tx.amount}</td>
                            <td class="py-3 px-2">
                                <span class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase ${badgeClass}">${tx.status}</span>
                            </td>
                            <td class="py-3 px-2 text-gray-500 font-semibold">${time}</td>
                        </tr>
                    `;
                });
                document.getElementById('transactions-table-body').innerHTML = txHTML || '<tr><td colspan="6" class="text-center py-4 text-gray-400">No transaction logs.</td></tr>';
            }

            // Refresh icons on updated HTML content
            lucide.createIcons();

        } catch (e) {
            console.error('Failed to load dashboard statistics:', e);
        }
    }

    // Chart plotting using SVG paths in JavaScript

    function renderGrowthChart(data) {
        const container = document.getElementById('growth-chart-container');
        if (!data || data.length === 0) {
            container.innerHTML = '<p class="text-xs text-gray-400 py-8 text-center font-bold">No growth metrics found.</p>';
            return;
        }

        const maxVal = Math.max(...data.map(d => d.users), 5);
        const maxScaleY = Math.ceil(maxVal / 5) * 5;
        const scale = 130 / maxScaleY;
        const widthSpacing = data.length > 1 ? 435 / (data.length - 1) : 435;

        let points = [];
        data.forEach((d, idx) => {
            const x = 45 + idx * widthSpacing;
            const y = 170 - d.users * scale;
            points.push(`${x},${y}`);
        });

        const areaPath = `M 45,170 L ${points.join(' L ')} L 480,170 Z`;
        const linePath = `M ${points.join(' L ')}`;

        let gridHTML = '';
        [0, 1/3, 2/3, 1].forEach(frac => {
            const val = Math.round(maxScaleY * frac);
            const y = 170 - val * scale;
            gridHTML += `
                <line x1="45" y1="${y}" x2="480" y2="${y}" stroke="${frac === 0 ? '#E6DFE1' : '#F1EAEC'}" stroke-width="${frac === 0 ? '1.5' : '1'}" ${frac === 0 ? '' : 'stroke-dasharray="4 4"'} />
                <text x="30" y="${y + 4}" font-size="9.5" font-weight="bold" fill="#6B5E62" text-anchor="end">${val}</text>
            `;
        });

        let nodesHTML = '';
        data.forEach((d, idx) => {
            const x = 45 + idx * widthSpacing;
            const y = 170 - d.users * scale;
            nodesHTML += `
                <circle cx="${x}" cy="${y}" r="4" fill="#FF3E5C" stroke="#FFF" stroke-width="1.5" />
                <text x="${x}" y="${y - 12}" font-size="9" font-weight="black" fill="#FF3E5C" text-anchor="middle">${d.users}</text>
                <text x="${x}" y="190" font-size="10" font-weight="bold" fill="#6B5E62" text-anchor="middle">${d.month}</text>
            `;
        });

        container.innerHTML = `
            <svg class="w-full h-full" viewBox="0 0 500 200">
                <defs>
                    <linearGradient id="chartGrad" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0%" stop-color="#FF3E5C" stop-opacity="0.2" />
                        <stop offset="100%" stop-color="#FF3E5C" stop-opacity="0.0" />
                    </linearGradient>
                </defs>
                ${gridHTML}
                <path d="${areaPath}" fill="url(#chartGrad)" />
                <path d="${linePath}" fill="none" stroke="#FF3E5C" stroke-width="3" stroke-linecap="round" />
                ${nodesHTML}
            </svg>
        `;
    }

    function renderDistributionChart(data) {
        const container = document.getElementById('dist-chart-container');
        if (!data || data.length === 0) {
            container.innerHTML = '<p class="text-xs text-gray-400 py-8 text-center font-bold">No template distributions.</p>';
            return;
        }

        const maxVal = Math.max(...data.map(d => d.count), 4);
        const maxScaleY = Math.ceil(maxVal / 4) * 4;
        const scale = 110 / maxScaleY;
        const spacing = 430 / Math.max(1, data.length);

        let gridHTML = '';
        for (let i = 0; i <= 4; i++) {
            const val = (maxScaleY / 4) * i;
            const y = 150 - val * scale;
            gridHTML += `
                <line x1="45" y1="${y}" x2="480" y2="${y}" stroke="${i === 0 ? '#E6DFE1' : '#F1EAEC'}" stroke-width="${i === 0 ? '1.5' : '1'}" ${i === 0 ? '' : 'stroke-dasharray="4 4"'} />
                <text x="30" y="${y + 4}" font-size="9.5" font-weight="bold" fill="#6B5E62" text-anchor="end">${val}</text>
            `;
        }

        let barsHTML = '';
        data.forEach((d, idx) => {
            const x = 52 + idx * spacing;
            const barWidth = Math.max(6, Math.min(18, spacing - 10));
            const barHeight = d.count * scale;
            const y = 150 - barHeight;
            const color = idx % 2 === 0 ? '#FF3E5C' : '#F7C566';
            barsHTML += `
                <rect x="${x}" y="${y}" width="${barWidth}" height="${barHeight}" fill="${color}" rx="3" />
                <text x="${x + barWidth / 2}" y="${y - 6}" font-size="9.5" font-weight="black" fill="#161112" text-anchor="middle">${d.count}</text>
                <text x="${x + barWidth / 2}" y="165" font-size="9" font-weight="bold" fill="#6B5E62" text-anchor="end" transform="rotate(-30, ${x + barWidth / 2}, 165)">${d.name.length > 10 ? d.name.substring(0, 8) + '...' : d.name}</text>
            `;
        });

        container.innerHTML = `
            <svg class="w-full h-full" viewBox="0 0 500 210">
                ${gridHTML}
                ${barsHTML}
            </svg>
        `;
    }

    function renderSubGrowthChart(data) {
        const container = document.getElementById('sub-growth-chart-container');
        if (!data || data.length === 0) {
            container.innerHTML = '<p class="text-xs text-gray-400 py-8 text-center font-bold">No subscriber data.</p>';
            return;
        }

        const maxVal = Math.max(...data.map(d => d.subscribers), 5);
        const maxScaleY = Math.ceil(maxVal / 5) * 5;
        const scale = 130 / maxScaleY;
        const widthSpacing = data.length > 1 ? 435 / (data.length - 1) : 435;

        let points = [];
        data.forEach((d, idx) => {
            const x = 45 + idx * widthSpacing;
            const y = 170 - d.subscribers * scale;
            points.push(`${x},${y}`);
        });

        const areaPath = `M 45,170 L ${points.join(' L ')} L 480,170 Z`;
        const linePath = `M ${points.join(' L ')}`;

        let gridHTML = '';
        [0, 1/3, 2/3, 1].forEach(frac => {
            const val = Math.round(maxScaleY * frac);
            const y = 170 - val * scale;
            gridHTML += `
                <line x1="45" y1="${y}" x2="480" y2="${y}" stroke="${frac === 0 ? '#E6DFE1' : '#F1EAEC'}" stroke-width="${frac === 0 ? '1.5' : '1'}" ${frac === 0 ? '' : 'stroke-dasharray="4 4"'} />
                <text x="30" y="${y + 4}" font-size="9.5" font-weight="bold" fill="#6B5E62" text-anchor="end">${val}</text>
            `;
        });

        let nodesHTML = '';
        data.forEach((d, idx) => {
            const x = 45 + idx * widthSpacing;
            const y = 170 - d.subscribers * scale;
            nodesHTML += `
                <circle cx="${x}" cy="${y}" r="4" fill="#F7C566" stroke="#FFF" stroke-width="1.5" />
                <text x="${x}" y="${y - 12}" font-size="9" font-weight="black" fill="#C9943B" text-anchor="middle">${d.subscribers}</text>
                <text x="${x}" y="190" font-size="10" font-weight="bold" fill="#6B5E62" text-anchor="middle">${d.month}</text>
            `;
        });

        container.innerHTML = `
            <svg class="w-full h-full" viewBox="0 0 500 200">
                <defs>
                    <linearGradient id="subChartGrad" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0%" stop-color="#F7C566" stop-opacity="0.2" />
                        <stop offset="100%" stop-color="#F7C566" stop-opacity="0.0" />
                    </linearGradient>
                </defs>
                ${gridHTML}
                <path d="${areaPath}" fill="url(#subChartGrad)" />
                <path d="${linePath}" fill="none" stroke="#F7C566" stroke-width="3" stroke-linecap="round" />
                ${nodesHTML}
            </svg>
        `;
    }
</script>
@endpush
