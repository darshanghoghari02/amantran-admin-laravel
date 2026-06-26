@extends('admin.layouts.app')

@section('title', 'Subscription Settings')
@section('header_title', 'Subscription Settings')

@php $activeTab = 'subscriptions'; @endphp

@section('content')
<div class="space-y-8 animate-fadeIn">
    <!-- Tab Selectors -->
    <div class="flex gap-2 p-1 bg-[#FFF5F6]/45 border border-[#FFCAD2]/40 rounded-2xl w-fit shadow-xs">
        <button
            onclick="switchSubTab('config')"
            id="tab-btn-config"
            class="tab-sub-btn flex items-center gap-2 px-5 py-2.5 rounded-xl text-xs font-bold transition-all duration-300 bg-wedding-charcoal-dark text-wedding-gold-light shadow-md"
        >
            <i data-lucide="sparkles" class="w-3.5 h-3.5"></i>
            Plan Configuration
        </button>
        <button
            onclick="switchSubTab('analytics')"
            id="tab-btn-analytics"
            class="tab-sub-btn flex items-center gap-2 px-5 py-2.5 rounded-xl text-xs font-bold transition-all duration-300 text-gray-500 hover:text-wedding-charcoal-dark hover:bg-white/50"
        >
            <i data-lucide="users" class="w-3.5 h-3.5"></i>
            Subscribers & Analytics
        </button>
    </div>

    <!-- CONFIG TAB PANEL -->
    <div id="sub-panel-config" class="sub-tab-panel space-y-8">
        <!-- Overview Banner -->
        <div class="bg-gradient-to-r from-wedding-charcoal-dark to-[#2c1215] p-6 sm:p-8 rounded-3xl border border-wedding-pink-dark/20 text-white flex flex-col md:flex-row gap-6 justify-between items-start md:items-center shadow-xl">
            <div class="space-y-1 flex-1">
                <h3 class="text-lg sm:text-2xl font-black text-wedding-gold-light tracking-tight flex items-center gap-2">
                    <i data-lucide="sparkles" class="w-6 h-6 text-wedding-gold-accent fill-wedding-gold-accent animate-pulse"></i>
                    Premium Paywall Configuration
                </h3>
                <p class="text-xs text-gray-300 max-w-2xl font-medium leading-relaxed">
                    Configure premium membership details. Define paywall gates by setting category-wide overrides, listing features, or selecting specific individual templates included under each subscription package.
                </p>
            </div>
            <div id="create-plan-btn-container">
                <!-- Dynamically populated based on permission -->
            </div>
        </div>

        <!-- Quick Statistics Summary -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
            <div class="bg-gradient-to-br from-amber-500/10 to-amber-600/5 border border-amber-500/20 rounded-2xl p-4 flex flex-col justify-center">
                <span class="text-[10px] font-bold text-amber-800 uppercase tracking-wider block">Premium templates</span>
                <span id="stat-premium-templates" class="text-2xl font-black text-amber-700 mt-1">-</span>
            </div>
            <div class="bg-gradient-to-br from-gray-500/10 to-gray-600/5 border border-gray-500/20 rounded-2xl p-4 flex flex-col justify-center">
                <span class="text-[10px] font-bold text-gray-800 uppercase tracking-wider block">Free templates</span>
                <span id="stat-free-templates" class="text-2xl font-black text-gray-700 mt-1">-</span>
            </div>
            <div class="bg-gradient-to-br from-blue-500/10 to-blue-600/5 border border-blue-500/20 rounded-2xl p-4 flex flex-col justify-center">
                <span class="text-[10px] font-bold text-blue-800 uppercase tracking-wider block">In Monthly Pass</span>
                <span id="stat-in-monthly" class="text-2xl font-black text-blue-700 mt-1">-</span>
            </div>
            <div class="bg-gradient-to-br from-purple-500/10 to-purple-600/5 border border-purple-500/20 rounded-2xl p-4 flex flex-col justify-center">
                <span class="text-[10px] font-bold text-purple-800 uppercase tracking-wider block">In Yearly Pass</span>
                <span id="stat-in-yearly" class="text-2xl font-black text-purple-700 mt-1">-</span>
            </div>
            <div class="bg-gradient-to-br from-amber-600/10 to-amber-700/5 border border-amber-600/20 rounded-2xl p-4 flex flex-col justify-center col-span-2 sm:col-span-1">
                <span class="text-[10px] font-bold text-amber-900 uppercase tracking-wider block">Avg Single Buy Price</span>
                <span id="stat-avg-single-price" class="text-2xl font-black text-amber-800 mt-1 font-mono">₹-</span>
            </div>
        </div>

        <div id="plans-loading" class="flex flex-col items-center justify-center min-h-[30vh] gap-3">
            <div class="w-10 h-10 border-4 border-wedding-pink-medium border-t-wedding-pink-dark rounded-full animate-spin"></div>
            <p class="text-xs font-semibold text-wedding-pink-dark">Querying active paywalls...</p>
        </div>

        <!-- Plans Grid -->
        <div id="plans-grid" class="grid grid-cols-1 lg:grid-cols-2 gap-8 hidden">
            <!-- Dynamic Plans cards -->
        </div>
    </div>

    <!-- ANALYTICS TAB PANEL -->
    <div id="sub-panel-analytics" class="sub-tab-panel space-y-8 hidden">
        <div id="analytics-loading" class="flex flex-col items-center justify-center min-h-[30vh] gap-3">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-wedding-pink-dark"></div>
            <p class="text-xs text-gray-400 font-bold">Aggregating subscriber details...</p>
        </div>

        <div id="analytics-content" class="space-y-8 hidden">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-white border border-wedding-pink-medium/20 p-6 rounded-2xl flex items-center justify-between shadow-xs">
                    <div class="space-y-1">
                        <p class="text-xs text-gray-500 font-extrabold uppercase tracking-wider">Total Subscribers</p>
                        <h4 id="analytics-total-subs" class="text-3xl font-extrabold text-wedding-charcoal-dark tracking-tight">-</h4>
                    </div>
                    <div class="p-4 rounded-2xl bg-wedding-pink-light text-wedding-pink-dark border border-wedding-pink-medium/20 shadow-xs">
                        <i data-lucide="users" class="w-6 h-6"></i>
                    </div>
                </div>
                <div class="bg-white border border-wedding-pink-medium/20 p-6 rounded-2xl flex items-center justify-between shadow-xs">
                    <div class="space-y-1">
                        <p class="text-xs text-gray-500 font-extrabold uppercase tracking-wider">Active Trials</p>
                        <h4 id="analytics-active-trials" class="text-3xl font-extrabold text-wedding-charcoal-dark tracking-tight">-</h4>
                    </div>
                    <div class="p-4 rounded-2xl bg-amber-50 text-amber-600 border border-amber-200 shadow-xs">
                        <i data-lucide="trending-up" class="w-6 h-6"></i>
                    </div>
                </div>
                <div class="bg-white border border-wedding-pink-medium/20 p-6 rounded-2xl flex items-center justify-between shadow-xs">
                    <div class="space-y-1">
                        <p class="text-xs text-gray-500 font-extrabold uppercase tracking-wider">Monthly Revenue</p>
                        <h4 id="analytics-monthly-rev" class="text-3xl font-extrabold text-wedding-charcoal-dark tracking-tight font-mono">₹-</h4>
                    </div>
                    <div class="p-4 rounded-2xl bg-emerald-50 text-emerald-600 border border-emerald-200 shadow-xs">
                        <i data-lucide="coins" class="w-6 h-6"></i>
                    </div>
                </div>
                <div class="bg-white border border-wedding-pink-medium/20 p-6 rounded-2xl flex items-center justify-between shadow-xs">
                    <div class="space-y-1">
                        <p class="text-xs text-gray-500 font-extrabold uppercase tracking-wider">Churn Rate</p>
                        <h4 id="analytics-churn-rate" class="text-3xl font-extrabold text-wedding-charcoal-dark tracking-tight">-%</h4>
                    </div>
                    <div class="p-4 rounded-2xl bg-indigo-50 text-indigo-600 border border-indigo-200 shadow-xs">
                        <i data-lucide="percent" class="w-6 h-6"></i>
                    </div>
                </div>
            </div>

            <!-- Growth trend chart -->
            <div class="bg-white border border-wedding-pink-medium/20 p-8 rounded-3xl shadow-xs space-y-4">
                <h4 class="text-lg font-bold text-wedding-charcoal-dark tracking-tight">Subscription Growth Trend</h4>
                <p class="text-xs text-gray-500 font-semibold">Historical dataset representing total active memberships</p>
                
                <div class="h-64 w-full relative flex items-end pt-4" id="analytics-chart-container">
                    <!-- SVG Chart written dynamically -->
                </div>
            </div>

            <!-- Subscribers Table Search & Filter -->
            <div class="bg-white rounded-[24px] border border-wedding-pink-medium/10 p-5 shadow-xs flex flex-col sm:flex-row gap-4 items-center justify-between">
                <div class="relative flex-1 w-full">
                    <i data-lucide="search" class="w-4 h-4 text-gray-400 absolute left-4 top-1/2 transform -translate-y-1/2"></i>
                    <input
                        type="text"
                        id="subscriber-search"
                        oninput="filterSubscribers()"
                        placeholder="Search subscribers by name, email, or phone..."
                        className="w-full pl-11 pr-4 py-3 bg-[#FFF5F6]/30 border border-[#FFCAD2]/55 rounded-2xl text-wedding-charcoal-dark placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-wedding-pink-dark/25 focus:bg-white text-sm font-semibold transition-all"
                    />
                </div>
                <div class="flex gap-4 items-center">
                    <select
                        id="subscriber-status-filter"
                        onchange="filterSubscribers()"
                        className="px-4 py-3 bg-gray-50 border border-wedding-pink-medium/20 rounded-2xl text-wedding-charcoal-dark focus:outline-none focus:ring-2 focus:ring-wedding-pink-dark/20 text-sm font-semibold cursor-pointer"
                    >
                        <option value="">All Statuses</option>
                        <option value="active">Active Plan</option>
                        <option value="trial">Free Trial</option>
                        <option value="cancelled">Auto-renew Cancelled</option>
                        <option value="expired">Expired</option>
                    </select>

                    <div id="manual-assign-btn-container">
                        <!-- Rendered based on permission -->
                    </div>
                </div>
            </div>

            <!-- Subscriber Table -->
            <div class="bg-white rounded-[28px] border border-wedding-pink-medium/10 shadow-xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse min-w-[900px]">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-100">
                                <th class="px-6 py-4 text-[10px] font-black text-gray-500 uppercase tracking-wider">Subscriber</th>
                                <th class="px-6 py-4 text-[10px] font-black text-gray-500 uppercase tracking-wider">Active Plan</th>
                                <th class="px-6 py-4 text-[10px] font-black text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-4 text-[10px] font-black text-gray-500 uppercase tracking-wider">Expiry Date</th>
                                <th class="px-6 py-4 text-[10px] font-black text-gray-500 uppercase tracking-wider">Billing History</th>
                                <th class="px-6 py-4 text-[10px] font-black text-gray-500 uppercase tracking-wider text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="subscribers-list-body" class="divide-y divide-gray-100">
                            <!-- Dynamic Subscribers -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Plan Overlay Modal -->
    <div id="create-plan-modal" class="fixed inset-0 bg-wedding-charcoal-dark/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden animate-fadeIn">
        <div class="bg-wedding-bg border border-wedding-pink-medium/40 w-full max-w-2xl rounded-3xl shadow-2xl overflow-hidden animate-slideUp max-h-[90vh] flex flex-col">
            <div class="p-6 bg-wedding-charcoal-dark text-white flex justify-between items-center">
                <h4 class="font-bold text-lg text-wedding-gold-light">Create New Subscription Plan</h4>
                <button type="button" onclick="closeCreateModal()" class="text-gray-400 hover:text-white font-bold text-sm bg-wedding-charcoal-light px-3 py-1.5 rounded-xl">✕</button>
            </div>
            
            <form onsubmit="handleCreatePlan(event)" class="p-6 space-y-5 overflow-y-auto flex-1">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-wedding-charcoal-light uppercase tracking-wider">Plan ID / Key</label>
                        <input type="text" id="new-plan-id" placeholder="e.g. quarterly_pass" class="w-full px-4 py-3 rounded-2xl bg-white border border-wedding-pink-medium/40 text-sm focus:ring-2 focus:ring-wedding-pink-dark/20 focus:outline-none" required />
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-wedding-charcoal-light uppercase tracking-wider">Plan Title</label>
                        <input type="text" id="new-plan-name" placeholder="e.g. Quarterly Premium" class="w-full px-4 py-3 rounded-2xl bg-white border border-wedding-pink-medium/40 text-sm focus:ring-2 focus:ring-wedding-pink-dark/20 focus:outline-none" required />
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-wedding-charcoal-light uppercase tracking-wider">Price (₹)</label>
                        <input type="number" id="new-plan-price" min="0" value="0" class="w-full px-4 py-3 rounded-2xl bg-white border border-wedding-pink-medium/40 text-sm font-bold focus:ring-2 focus:ring-wedding-pink-dark/20 focus:outline-none" required />
                    </div>
                    <div class="space-y-1.5 animate-fadeIn">
                        <label class="text-xs font-bold text-wedding-charcoal-light uppercase tracking-wider">Duration Type</label>
                        <select id="new-plan-duration-type" onchange="handleNewDurationTypeChange(this.value)" class="w-full px-4 py-3 rounded-2xl bg-white border border-wedding-pink-medium/40 text-sm focus:ring-2 focus:ring-wedding-pink-dark/20 focus:outline-none font-semibold">
                            <option value="1day">1 Day</option>
                            <option value="weekly">Weekly (7 Days)</option>
                            <option value="monthly" selected>Monthly (30 Days)</option>
                            <option value="yearly">Yearly (365 Days)</option>
                            <option value="custom">Custom (Fixed Dates)</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 hidden" id="new-plan-custom-dates">
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-wedding-charcoal-light uppercase tracking-wider">Start Date</label>
                        <input type="date" id="new-plan-start-date" class="w-full px-4 py-3 rounded-2xl bg-white border border-wedding-pink-medium/40 text-sm" />
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-wedding-charcoal-light uppercase tracking-wider">End Date</label>
                        <input type="date" id="new-plan-end-date" class="w-full px-4 py-3 rounded-2xl bg-white border border-wedding-pink-medium/40 text-sm" />
                    </div>
                </div>

                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-wedding-charcoal-light uppercase tracking-wider">Description</label>
                    <textarea id="new-plan-desc" placeholder="Plan summary description..." rows="2" class="w-full px-4 py-3 rounded-2xl bg-white border border-wedding-pink-medium/40 text-sm focus:outline-none resize-none"></textarea>
                </div>

                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-wedding-charcoal-light uppercase tracking-wider">Features (Comma-separated)</label>
                    <input type="text" id="new-plan-features" placeholder="e.g. Unlimited PDFs, Ad-free, Custom Fonts" class="w-full px-4 py-3 rounded-2xl bg-white border border-wedding-pink-medium/40 text-sm focus:outline-none" />
                </div>

                <div class="flex items-center justify-between p-3.5 bg-gray-50 border border-wedding-pink-medium/20 rounded-2xl">
                    <div>
                        <h5 class="text-xs font-black text-wedding-charcoal-dark uppercase tracking-wider">Plan Accessibility</h5>
                        <p class="text-[10px] text-gray-500 font-semibold mt-0.5">Toggle plan availability on active devices</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" id="new-plan-active" checked class="sr-only peer" />
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-wedding-pink-dark"></div>
                    </label>
                </div>

                <div class="space-y-4 pt-4 border-t border-wedding-pink-medium/10">
                    <h5 class="text-xs font-black text-wedding-charcoal-dark uppercase tracking-wider flex items-center gap-1.5">
                        <i data-lucide="layers" class="w-4 h-4 text-wedding-pink-dark"></i> Included Template Categories
                    </h5>
                    <div id="new-plan-categories" class="flex gap-2 flex-wrap bg-gray-50/50 p-4 border border-wedding-pink-medium/15 rounded-2xl max-h-[120px] overflow-y-auto">
                        <!-- Populated dynamically -->
                    </div>
                </div>

                <div class="pt-4 border-t border-wedding-pink-medium/20 flex justify-end gap-3">
                    <button type="button" onclick="closeCreateModal()" class="px-5 py-3 rounded-2xl bg-wedding-pink-light/40 border border-wedding-pink-medium/30 text-wedding-charcoal-light hover:bg-wedding-pink-light/80 hover:text-wedding-pink-dark text-sm font-bold transition-all duration-300">Cancel</button>
                    <button type="submit" id="create-submit-btn" class="px-6 py-3 rounded-2xl bg-wedding-pink-dark text-white text-sm font-bold shadow-lg">Create Plan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Manual Assign Modal -->
    <div id="manual-assign-modal" class="fixed inset-0 bg-wedding-charcoal-dark/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden animate-fadeIn">
        <div class="bg-wedding-bg border border-wedding-pink-medium/40 w-full max-w-md rounded-3xl shadow-2xl overflow-hidden animate-slideUp">
            <div class="p-6 bg-wedding-charcoal-dark text-white flex justify-between items-center">
                <h4 class="font-bold text-lg text-wedding-gold-light">Manual Subscription Assigner</h4>
                <button type="button" onclick="closeAssignModal()" class="text-gray-400 hover:text-white font-bold text-sm bg-wedding-charcoal-light px-3 py-1.5 rounded-xl">✕</button>
            </div>
            
            <form onsubmit="handleManualAssign(event)" class="p-6 space-y-4">
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-wedding-charcoal-light uppercase tracking-wider">Select Mobile User</label>
                    <select id="assign-user-select" class="w-full px-4 py-3 rounded-2xl bg-white border border-wedding-pink-medium/40 text-sm font-semibold focus:outline-none" required>
                        <option value="">-- Select App User --</option>
                    </select>
                </div>

                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-wedding-charcoal-light uppercase tracking-wider">Select Subscription Plan</label>
                    <select id="assign-plan-select" onchange="handleAssignPlanChange(this.value)" class="w-full px-4 py-3 rounded-2xl bg-white border border-wedding-pink-medium/40 text-sm font-semibold focus:outline-none">
                        <!-- Populated dynamically -->
                    </select>
                </div>

                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-wedding-charcoal-light uppercase tracking-wider">Amount Paid (₹)</label>
                    <input type="number" id="assign-amount" value="0" class="w-full px-4 py-3 rounded-2xl bg-white border border-wedding-pink-medium/40 text-sm font-semibold focus:outline-none" required />
                </div>

                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-wedding-charcoal-light uppercase tracking-wider">Expiration Date</label>
                    <input type="date" id="assign-expiry" class="w-full px-4 py-3 rounded-2xl bg-white border border-wedding-pink-medium/40 text-sm font-semibold focus:outline-none" required />
                </div>

                <div class="pt-4 border-t border-wedding-pink-medium/20 flex justify-end gap-3">
                    <button type="button" onclick="closeAssignModal()" class="px-5 py-3 rounded-2xl bg-gray-100 text-sm font-bold">Cancel</button>
                    <button type="submit" id="assign-submit-btn" class="px-6 py-3 rounded-2xl bg-wedding-charcoal-dark text-wedding-gold-light text-sm font-bold shadow-lg">Assign Plan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let activeSubTab = 'config';
    let plansList = [];
    let categoriesList = [];
    let templatesList = [];
    
    // Analytics states
    let analyticsStats = null;
    let subscribersList = [];
    let allAppUsers = [];
    let expandedSubId = null;

    const headers = {
        'x-user-id': window.CurrentUser ? window.CurrentUser.id : 'admin_super',
        'Content-Type': 'application/json'
    };

    function hasPermission(perm) {
        if (!window.CurrentUser) return false;
        const rId = window.CurrentUser.roleId || window.CurrentUser.role || 'viewer';
        if (rId === 'super_admin') return true;
        if (window.CurrentUser.permissions && window.CurrentUser.permissions.includes('*')) return true;
        return window.CurrentUser.permissions && window.CurrentUser.permissions.includes(perm);
    }

    document.addEventListener('DOMContentLoaded', () => {
        if (hasPermission('subscriptions.create')) {
            document.getElementById('create-plan-btn-container').innerHTML = `
                <button
                    onclick="openCreateModal()"
                    class="flex items-center gap-2 px-5 py-3 bg-wedding-pink-dark hover:bg-wedding-pink-hover text-white text-xs font-extrabold rounded-2xl shadow-lg transition-all duration-300 transform hover:-translate-y-0.5 whitespace-nowrap"
                >
                    <i data-lucide="plus-circle" class="w-5 h-5"></i>
                    Create Plan
                </button>
            `;
            lucide.createIcons({ nodeList: [document.getElementById('create-plan-btn-container')] });
        }

        fetchInitialData();
    });

    function switchSubTab(tab) {
        activeSubTab = tab;
        document.querySelectorAll('.tab-sub-btn').forEach(btn => {
            btn.classList.remove('bg-wedding-charcoal-dark', 'text-wedding-gold-light', 'shadow-md');
            btn.classList.add('text-gray-500', 'hover:text-wedding-charcoal-dark', 'hover:bg-white/50');
        });
        document.getElementById(`tab-btn-${tab}`).classList.add('bg-wedding-charcoal-dark', 'text-wedding-gold-light', 'shadow-md');
        document.getElementById(`tab-btn-${tab}`).classList.remove('text-gray-500', 'hover:text-wedding-charcoal-dark', 'hover:bg-white/50');

        document.querySelectorAll('.sub-tab-panel').forEach(p => p.classList.add('hidden'));
        document.getElementById(`sub-panel-${tab}`).classList.remove('hidden');

        if (tab === 'analytics') {
            fetchAnalyticsData();
        }
    }

    async function fetchInitialData() {
        try {
            const [resPlans, resCats, resTpls] = await Promise.all([
                fetch(`/api/subscriptions`, { headers }).then(r => r.json()),
                fetch(`/api/categories`, { headers }).then(r => r.json()),
                fetch(`/api/templates`, { headers }).then(r => r.json())
            ]);

            plansList = Array.isArray(resPlans) ? resPlans : [];
            categoriesList = Array.isArray(resCats) ? resCats : [];
            templatesList = Array.isArray(resTpls) ? resTpls : [];

            // Update stats quick info
            const premiumCount = templatesList.filter(t => t.isPremium).length;
            const freeCount = templatesList.filter(t => !t.isPremium).length;
            const inMonthlyCount = templatesList.filter(t => t.isPremium && t.includedInMonthlyPlan).length;
            const inYearlyCount = templatesList.filter(t => t.isPremium && t.includedInYearlyPlan).length;
            
            const purchasablePremium = templatesList.filter(t => t.isPremium && t.singlePurchasePrice && t.singlePurchasePrice > 0);
            const avgPrice = purchasablePremium.length > 0 
                ? Math.round(purchasablePremium.reduce((sum, t) => sum + (t.singlePurchasePrice || 0), 0) / purchasablePremium.length)
                : 0;

            document.getElementById('stat-premium-templates').innerText = premiumCount;
            document.getElementById('stat-free-templates').innerText = freeCount;
            document.getElementById('stat-in-monthly').innerText = inMonthlyCount;
            document.getElementById('stat-in-yearly').innerText = inYearlyCount;
            document.getElementById('stat-avg-single-price').innerText = `₹${avgPrice}`;

            renderPlansConfig();
        } catch (e) {
            console.error('Failed to load subscriptions metadata', e);
            Toast.show('Error loading paywalls config.', 'error');
        }
    }

    function renderPlansConfig() {
        document.getElementById('plans-loading').classList.add('hidden');
        const grid = document.getElementById('plans-grid');
        grid.classList.remove('hidden');

        if (plansList.length === 0) {
            grid.innerHTML = '<p class="text-center text-gray-400 font-bold py-8 col-span-2">No subscription plans configured.</p>';
            return;
        }

        let html = '';
        plansList.forEach(plan => {
            const featuresStr = Array.isArray(plan.features) ? plan.features.join(', ') : (plan.features || '');
            const includedCats = plan.includedCategories || [];
            const includedTpls = plan.includedTemplateIds || [];

            const isMonthly = plan.id === 'monthly';
            const isYearly = plan.id === 'yearly';
            const badgeColor = isMonthly 
                ? 'bg-blue-50 border-blue-200 text-blue-700' 
                : isYearly
                ? 'bg-purple-50 border-purple-200 text-purple-700'
                : 'bg-emerald-50 border-emerald-200 text-emerald-700';
            
            const badgeLabel = isMonthly ? 'Monthly Pass' : isYearly ? 'Yearly Pass' : 'Custom Plan';

            // Build categories list
            let catsHTML = '';
            categoriesList.forEach(cat => {
                const isIncluded = includedCats.includes(cat.id);
                catsHTML += `
                    <button
                        type="button"
                        onclick="togglePlanCategory('${plan.id}', '${cat.id}')"
                        class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all border ${
                            isIncluded 
                                ? 'bg-blue-50 border-blue-500 text-blue-700 font-black shadow-xs' 
                                : 'bg-wedding-bg border-wedding-pink-medium/55 text-wedding-charcoal-light/95 hover:bg-wedding-pink-light/35'
                        }"
                    >
                        ${cat.name}
                    </button>
                `;
            });

            // Build templates list
            let tplsHTML = '';
            const premiumTemplates = templatesList.filter(t => t.isPremium);
            if (premiumTemplates.length === 0) {
                tplsHTML = '<p class="text-[10px] text-gray-400 font-bold">No premium layouts created yet.</p>';
            } else {
                premiumTemplates.forEach(tpl => {
                    const isIncluded = includedTpls.includes(tpl.id);
                    tplsHTML += `
                        <button
                            type="button"
                            onclick="togglePlanTemplate('${plan.id}', '${tpl.id}')"
                            class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all border ${
                                isIncluded 
                                    ? 'bg-blue-50 border-blue-500 text-blue-700 font-black shadow-xs' 
                                    : 'bg-wedding-bg border-wedding-pink-medium/55 text-wedding-charcoal-light/95 hover:bg-wedding-pink-light/35'
                            }"
                        >
                            ${tpl.name}
                        </button>
                    `;
                });
            }

            let actionsHTML = '';
            if (hasPermission('subscriptions.delete')) {
                actionsHTML += `
                    <button
                        onclick="handleDeletePlan('${plan.id}', '${plan.name}')"
                        class="flex items-center gap-1.5 px-3.5 py-2 border border-red-200 bg-red-50 hover:bg-red-100 text-red-600 text-xs font-bold rounded-xl shadow-xs transition-all duration-200"
                    >
                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i> Delete Plan
                    </button>
                `;
            }

            let saveButtonHTML = '';
            if (hasPermission('subscriptions.edit') || hasPermission('subscriptions.manage_pricing')) {
                saveButtonHTML += `
                    <button
                        onclick="handleSavePlan('${plan.id}')"
                        id="save-btn-${plan.id}"
                        class="flex items-center gap-2 px-5 py-3 bg-wedding-charcoal-dark hover:bg-wedding-charcoal-light text-wedding-gold-light hover:text-white text-xs font-extrabold rounded-2xl shadow transition-all duration-300"
                    >
                        <i data-lucide="save" class="w-4 h-4 text-wedding-pink-medium"></i> Save settings
                    </button>
                `;
            }

            html += `
                <div class="bg-white border border-wedding-pink-medium/40 rounded-3xl shadow-sm hover:shadow-md transition-shadow duration-300 overflow-hidden flex flex-col justify-between">
                    <div class="p-6 sm:p-8 space-y-6">
                        <div class="flex justify-between items-center pb-4 border-b border-wedding-pink-medium/10">
                            <div>
                                <h4 class="text-lg font-black text-wedding-charcoal-dark tracking-tight">${plan.name}</h4>
                                <p class="text-xs text-gray-400 font-mono mt-0.5">plan_id: ${plan.id}</p>
                            </div>
                            <span class="px-3 py-1 border text-[10px] font-black rounded-lg uppercase tracking-wider ${badgeColor}">
                                ${badgeLabel}
                            </span>
                        </div>

                        <div class="space-y-4">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="space-y-1.5">
                                    <label class="text-xs font-bold text-wedding-charcoal-light uppercase tracking-wider">Plan Title</label>
                                    <input type="text" id="plan-name-${plan.id}" value="${plan.name}" class="w-full px-4 py-3 rounded-2xl bg-gray-50 border border-wedding-pink-medium/30 text-wedding-charcoal-dark text-sm focus:outline-none focus:ring-2 focus:ring-wedding-pink-dark/20 focus:bg-white font-medium transition-all" />
                                </div>
                                <div class="space-y-1.5">
                                    <label class="text-xs font-bold text-wedding-charcoal-light uppercase tracking-wider">Price (₹ / cycle)</label>
                                    <input type="number" id="plan-price-${plan.id}" value="${plan.price}" min="0" class="w-full px-4 py-3 rounded-2xl bg-gray-50 border border-wedding-pink-medium/30 text-wedding-charcoal-dark text-sm focus:outline-none focus:ring-2 focus:ring-wedding-pink-dark/20 focus:bg-white font-bold transition-all" />
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="space-y-1.5">
                                    <label class="text-xs font-bold text-wedding-charcoal-light uppercase tracking-wider">Duration Type</label>
                                    <select id="plan-durtype-${plan.id}" onchange="handlePlanDurtypeChange('${plan.id}', this.value)" class="w-full px-4 py-3 rounded-2xl bg-gray-50 border border-wedding-pink-medium/30 text-wedding-charcoal-dark text-sm focus:outline-none focus:ring-2 focus:ring-wedding-pink-dark/20 focus:bg-white font-medium transition-all">
                                        <option value="1day" ${plan.durationType === '1day' ? 'selected' : ''}>1 Day</option>
                                        <option value="weekly" ${plan.durationType === 'weekly' ? 'selected' : ''}>Weekly (7 Days)</option>
                                        <option value="monthly" ${plan.durationType === 'monthly' ? 'selected' : ''}>Monthly (30 Days)</option>
                                        <option value="yearly" ${plan.durationType === 'yearly' ? 'selected' : ''}>Yearly (365 Days)</option>
                                        <option value="custom" ${plan.durationType === 'custom' ? 'selected' : ''}>Custom (Fixed Dates)</option>
                                    </select>
                                </div>
                                <div class="space-y-1.5 flex flex-col justify-end">
                                    <div class="px-4 py-3 rounded-2xl bg-gray-50 border border-wedding-pink-medium/20 text-wedding-charcoal-light text-sm font-semibold">
                                        Duration: <span id="plan-durdays-preview-${plan.id}">${plan.durationType === 'custom' ? 'Defined by dates' : `${plan.durationDays || 30} days`}</span>
                                    </div>
                                </div>
                            </div>

                            <div id="plan-custom-dates-${plan.id}" class="grid grid-cols-1 sm:grid-cols-2 gap-4 ${plan.durationType === 'custom' ? '' : 'hidden'}">
                                <div class="space-y-1.5">
                                    <label class="text-xs font-bold text-wedding-charcoal-light uppercase tracking-wider">Start Date</label>
                                    <input type="date" id="plan-start-${plan.id}" value="${plan.customStartDate ? plan.customStartDate.substring(0, 10) : ''}" class="w-full px-4 py-3 rounded-2xl bg-gray-50 border border-wedding-pink-medium/30 text-wedding-charcoal-dark text-sm" />
                                </div>
                                <div class="space-y-1.5">
                                    <label class="text-xs font-bold text-wedding-charcoal-light uppercase tracking-wider">End Date</label>
                                    <input type="date" id="plan-end-${plan.id}" value="${plan.customEndDate ? plan.customEndDate.substring(0, 10) : ''}" class="w-full px-4 py-3 rounded-2xl bg-gray-50 border border-wedding-pink-medium/30 text-wedding-charcoal-dark text-sm" />
                                </div>
                            </div>

                            <div class="space-y-1.5">
                                <label class="text-xs font-bold text-wedding-charcoal-light uppercase tracking-wider">Description</label>
                                <textarea id="plan-desc-${plan.id}" rows="2" class="w-full px-4 py-3 rounded-2xl bg-gray-50 border border-wedding-pink-medium/30 text-wedding-charcoal-dark text-sm focus:outline-none focus:ring-2 focus:ring-wedding-pink-dark/20 focus:bg-white transition-all resize-none">${plan.description || ''}</textarea>
                            </div>

                            <div class="space-y-1.5">
                                <label class="text-xs font-bold text-wedding-charcoal-light uppercase tracking-wider">Features (Comma-separated)</label>
                                <input type="text" id="plan-features-${plan.id}" value="${featuresStr}" class="w-full px-4 py-3 rounded-2xl bg-gray-50 border border-wedding-pink-medium/30 text-wedding-charcoal-dark text-sm focus:outline-none focus:ring-2 focus:ring-wedding-pink-dark/20 focus:bg-white font-medium transition-all" />
                            </div>

                            <div class="flex items-center justify-between p-3.5 bg-gray-50 border border-wedding-pink-medium/10 rounded-2xl">
                                <div>
                                    <h5 class="text-xs font-black text-wedding-charcoal-dark uppercase tracking-wider">Plan Accessibility</h5>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" id="plan-active-${plan.id}" ${plan.isActive !== false ? 'checked' : ''} class="sr-only peer" />
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-wedding-pink-dark"></div>
                                </label>
                            </div>

                            <div class="space-y-4 pt-4 border-t border-wedding-pink-medium/10">
                                <h5 class="text-xs font-black text-wedding-charcoal-dark uppercase tracking-wider flex items-center gap-1.5">
                                    <i data-lucide="layers" class="w-4 h-4 text-wedding-pink-dark"></i> Included Template Categories
                                </h5>
                                <div class="flex gap-2 flex-wrap bg-gray-50/50 p-4 border border-wedding-pink-medium/10 rounded-2xl max-h-[140px] overflow-y-auto">
                                    ${catsHTML}
                                </div>

                                <h5 class="text-xs font-black text-wedding-charcoal-dark uppercase tracking-wider flex items-center gap-1.5 pt-2">
                                    <i data-lucide="file-text" class="w-4 h-4 text-wedding-pink-dark"></i> Included Specific Premium Templates
                                </h5>
                                <div class="flex gap-2 flex-wrap bg-gray-50/50 p-4 border border-wedding-pink-medium/10 rounded-2xl max-h-[160px] overflow-y-auto">
                                    ${tplsHTML}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50 p-6 border-t border-wedding-pink-medium/10 flex items-center justify-between">
                        ${actionsHTML}
                        ${saveButtonHTML}
                    </div>
                </div>
            `;
        });

        grid.innerHTML = html;
        lucide.createIcons({ nodeList: [grid] });
    }

    function togglePlanCategory(planId, catId) {
        const plan = plansList.find(p => p.id === planId);
        if (!plan) return;
        if (!plan.includedCategories) plan.includedCategories = [];
        
        if (plan.includedCategories.includes(catId)) {
            plan.includedCategories = plan.includedCategories.filter(c => c !== catId);
        } else {
            plan.includedCategories.push(catId);
        }
        renderPlansConfig();
    }

    function togglePlanTemplate(planId, tplId) {
        const plan = plansList.find(p => p.id === planId);
        if (!plan) return;
        if (!plan.includedTemplateIds) plan.includedTemplateIds = [];

        if (plan.includedTemplateIds.includes(tplId)) {
            plan.includedTemplateIds = plan.includedTemplateIds.filter(t => t !== tplId);
        } else {
            plan.includedTemplateIds.push(tplId);
        }
        renderPlansConfig();
    }

    function handlePlanDurtypeChange(planId, value) {
        const datesDiv = document.getElementById(`plan-custom-dates-${planId}`);
        const daysPreview = document.getElementById(`plan-durdays-preview-${planId}`);
        const plan = plansList.find(p => p.id === planId);
        
        if (value === 'custom') {
            datesDiv.classList.remove('hidden');
            daysPreview.innerText = 'Defined by dates';
            if (plan) plan.durationDays = 0;
        } else {
            datesDiv.classList.add('hidden');
            let days = 30;
            if (value === '1day') days = 1;
            else if (value === 'weekly') days = 7;
            else if (value === 'monthly') days = 30;
            else if (value === 'yearly') days = 365;
            daysPreview.innerText = `${days} days`;
            if (plan) plan.durationDays = days;
        }
        if (plan) plan.durationType = value;
    }

    async function handleSavePlan(planId) {
        if (!hasPermission('subscriptions.edit') && !hasPermission('subscriptions.manage_pricing')) {
            Toast.show('Access Denied. Missing permissions.', 'warning');
            return;
        }

        const plan = plansList.find(p => p.id === planId);
        if (!plan) return;

        const name = document.getElementById(`plan-name-${planId}`).value.trim();
        const price = Number(document.getElementById(`plan-price-${planId}`).value);
        const description = document.getElementById(`plan-desc-${planId}`).value.trim();
        const featuresRaw = document.getElementById(`plan-features-${planId}`).value;
        const features = featuresRaw ? featuresRaw.split(',').map(f => f.trim()).filter(Boolean) : [];
        const isActive = document.getElementById(`plan-active-${planId}`).checked;
        const durationType = document.getElementById(`plan-durtype-${planId}`).value;
        const customStartDate = durationType === 'custom' ? document.getElementById(`plan-start-${planId}`).value : null;
        const customEndDate = durationType === 'custom' ? document.getElementById(`plan-end-${planId}`).value : null;

        const payload = {
            name,
            price,
            description,
            features,
            isActive,
            durationType,
            durationDays: plan.durationDays,
            customStartDate,
            customEndDate,
            includedCategories: plan.includedCategories || [],
            includedTemplateIds: plan.includedTemplateIds || []
        };

        const btn = document.getElementById(`save-btn-${planId}`);
        btn.disabled = true;
        btn.innerHTML = `<i data-lucide="refresh-cw" class="w-4 h-4 animate-spin"></i> Saving...`;
        lucide.createIcons({ nodeList: [btn] });

        try {
            const res = await fetch(`/api/subscriptions/${planId}`, {
                method: 'PUT',
                headers,
                body: JSON.stringify(payload)
            });

            if (res.ok) {
                btn.innerHTML = `<i data-lucide="check" class="w-4 h-4 text-green-400 stroke-[3]"></i> Saved!`;
                lucide.createIcons({ nodeList: [btn] });
                Toast.show('Subscription plan saved successfully!', 'success');
                setTimeout(() => {
                    fetchInitialData();
                }, 1500);
            } else {
                Toast.show('Failed to save subscription properties.', 'error');
                renderPlansConfig();
            }
        } catch (e) {
            console.error(e);
            Toast.show('Network error. Failed to save.', 'error');
            renderPlansConfig();
        }
    }

    async function handleDeletePlan(planId, planName) {
        if (!hasPermission('subscriptions.delete')) {
            Toast.show('Access Denied. You lack the "subscriptions.delete" permission.', 'warning');
            return;
        }
        if (!confirm(`Are you sure you want to delete the "${planName}" subscription plan?`)) return;

        try {
            const res = await fetch(`/api/subscriptions/${planId}`, {
                method: 'DELETE',
                headers: { 'x-user-id': window.CurrentUser ? window.CurrentUser.id : 'admin_super' }
            });
            if (res.ok) {
                Toast.show(`Plan "${planName}" deleted successfully!`, 'success');
                fetchInitialData();
            } else {
                const err = await res.json();
                Toast.show(err.error || 'Failed to delete plan.', 'error');
            }
        } catch (e) {
            console.error(e);
            Toast.show('Failed to delete plan.', 'error');
        }
    }

    // ANALYTICS & SUBSCRIBERS
    async function fetchAnalyticsData() {
        document.getElementById('analytics-loading').classList.remove('hidden');
        document.getElementById('analytics-content').classList.add('hidden');

        try {
            const [resSummary, resSubs, resUsers] = await Promise.all([
                fetch(`/api/analytics/subscription-summary`, { headers }).then(r => r.json()),
                fetch(`/api/user-subscriptions`, { headers }).then(r => r.json()),
                fetch(`/api/users/app-users`, { headers }).then(r => r.json())
            ]);

            analyticsStats = resSummary;
            subscribersList = Array.isArray(resSubs) ? resSubs : [];
            allAppUsers = Array.isArray(resUsers) ? resUsers : [];

            document.getElementById('analytics-loading').classList.add('hidden');
            document.getElementById('analytics-content').classList.remove('hidden');

            // Render stats
            document.getElementById('analytics-total-subs').innerText = analyticsStats.totalSubscribers ?? 0;
            document.getElementById('analytics-active-trials').innerText = analyticsStats.activeTrials ?? 0;
            document.getElementById('analytics-monthly-rev').innerText = `₹${analyticsStats.monthlyRevenue ?? 0}`;
            document.getElementById('analytics-churn-rate').innerText = `${analyticsStats.churnRate ?? 0}%`;

            renderGrowthTrendChart();
            renderSubscribersList();

            // Populate manual assign app users
            const selectUser = document.getElementById('assign-user-select');
            selectUser.innerHTML = '<option value="">-- Select App User --</option>';
            allAppUsers.forEach(u => {
                const opt = document.createElement('option');
                opt.value = u.id;
                opt.innerText = `${u.displayName || 'App User'} (${u.email || u.phone || 'no email/phone'})`;
                selectUser.appendChild(opt);
            });

            // Populate manual assign plans
            const selectPlan = document.getElementById('assign-plan-select');
            selectPlan.innerHTML = '';
            plansList.forEach(p => {
                const opt = document.createElement('option');
                opt.value = p.id;
                opt.innerText = `${p.name} (₹${p.price})`;
                selectPlan.appendChild(opt);
            });
            if (plansList.length > 0) {
                document.getElementById('assign-amount').value = plansList[0].price;
            }

            if (hasPermission('subscriptions.create')) {
                document.getElementById('manual-assign-btn-container').innerHTML = `
                    <button
                        onclick="openAssignModal()"
                        class="flex items-center gap-2 px-5 py-3 bg-wedding-charcoal-dark hover:bg-wedding-charcoal-light text-wedding-gold-light hover:text-white text-xs font-extrabold rounded-2xl shadow transition-all duration-300"
                    >
                        <i data-lucide="plus-circle" class="w-4 h-4"></i>
                        Manual Assign
                    </button>
                `;
                lucide.createIcons({ nodeList: [document.getElementById('manual-assign-btn-container')] });
            }

        } catch (e) {
            console.error(e);
            Toast.show('Failed to fetch subscription analytics.', 'error');
        }
    }

    function renderGrowthTrendChart() {
        const container = document.getElementById('analytics-chart-container');
        if (!analyticsStats || !analyticsStats.growthTrend || analyticsStats.growthTrend.length === 0) {
            container.innerHTML = '<p class="text-xs text-gray-400 py-8 text-center font-bold">No subscriber data.</p>';
            return;
        }

        const trend = analyticsStats.growthTrend;
        const maxSubs = Math.max(...trend.map(t => t.subscribers), 5);
        const maxScaleY = Math.ceil(maxSubs / 5) * 5;
        const scale = 130 / maxScaleY;
        const spacing = trend.length > 1 ? 435 / (trend.length - 1) : 435;

        let gridHTML = '';
        [0, 1/3, 2/3, 1].forEach(frac => {
            const val = Math.round(maxScaleY * frac);
            const y = 170 - val * scale;
            gridHTML += `
                <line x1="45" y1="${y}" x2="480" y2="${y}" stroke="${frac === 0 ? '#E6DFE1' : '#F1EAEC'}" stroke-width="${frac === 0 ? '1.5' : '1'}" ${frac === 0 ? '' : 'stroke-dasharray="4 4"'} />
                <text x="30" y="${y + 4}" font-size="9.5" font-weight="bold" fill="#6B5E62" text-anchor="end">${val}</text>
            `;
        });

        let points = [];
        trend.forEach((d, idx) => {
            const x = 45 + idx * spacing;
            const y = 170 - d.subscribers * scale;
            points.push(`${x},${y}`);
        });

        const areaPath = `M 45,170 L ${points.join(' L ')} L 480,170 Z`;
        const linePath = `M ${points.join(' L ')}`;

        let nodesHTML = '';
        trend.forEach((d, idx) => {
            const x = 45 + idx * spacing;
            const y = 170 - d.subscribers * scale;
            nodesHTML += `
                <circle cx="${x}" cy="${y}" r="4" fill="#F94C66" stroke="#FFF" stroke-width="1.5" />
                <text x="${x}" y="${y - 12}" font-size="9" font-weight="black" fill="#F94C66" text-anchor="middle">${d.subscribers}</text>
                <text x="${x}" y="190" font-size="10" font-weight="bold" fill="#6B5E62" text-anchor="middle">${d.month}</text>
            `;
        });

        container.innerHTML = `
            <svg class="w-full h-full" viewBox="0 0 500 200">
                <defs>
                    <linearGradient id="trendGrad" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0%" stop-color="#F94C66" stop-opacity="0.2" />
                        <stop offset="100%" stop-color="#F94C66" stop-opacity="0.0" />
                    </linearGradient>
                </defs>
                ${gridHTML}
                <path d="${areaPath}" fill="url(#trendGrad)" />
                <path d="${linePath}" fill="none" stroke="#F94C66" stroke-width="3" stroke-linecap="round" />
                ${nodesHTML}
            </svg>
        `;
    }

    function renderSubscribersList() {
        const body = document.getElementById('subscribers-list-body');
        const query = document.getElementById('subscriber-search').value.toLowerCase();
        const statusVal = document.getElementById('subscriber-status-filter').value;

        const filtered = subscribersList.filter(sub => {
            const matchesQuery = 
                (sub.email && sub.email.toLowerCase().includes(query)) ||
                (sub.displayName && sub.displayName.toLowerCase().includes(query)) ||
                (sub.phone && sub.phone.includes(query));

            const matchesStatus = 
                !statusVal || 
                (sub.activeSubscription && sub.activeSubscription.status === statusVal) ||
                (!sub.activeSubscription && statusVal === 'expired');

            return matchesQuery && matchesStatus;
        });

        if (filtered.length === 0) {
            body.innerHTML = `
                <tr>
                    <td colSpan="6" class="py-16 text-center text-gray-400">
                        <i data-lucide="users" class="w-10 h-10 text-gray-200 mx-auto mb-2"></i>
                        <p class="text-sm font-bold text-gray-500">No subscribers found matching criteria.</p>
                    </td>
                </tr>
            `;
            lucide.createIcons({ nodeList: [body] });
            return;
        }

        const statusColors = {
            active: 'bg-green-50 text-green-700 border-green-200',
            trial: 'bg-amber-50 text-amber-700 border-amber-200',
            cancelled: 'bg-purple-50 text-purple-700 border-purple-200',
            expired: 'bg-gray-50 text-gray-500 border-gray-200'
        };

        let html = '';
        filtered.forEach(sub => {
            const active = sub.activeSubscription;
            const statusLabel = active ? active.status : 'expired';
            const statusClass = statusColors[statusLabel] || 'bg-gray-50 text-gray-500 border-gray-200';
            const expiry = active && active.expiryDate ? new Date(active.expiryDate).toLocaleDateString('en-IN') : '—';
            const isExpanded = expandedSubId === sub.userId;

            let cancelBtnHTML = '';
            if (active && active.status !== 'cancelled' && active.status !== 'expired' && hasPermission('subscriptions.edit')) {
                cancelBtnHTML += `
                    <button
                        onclick="cancelRenew('${sub.userId}')"
                        class="px-3 py-1.5 border border-red-200 bg-red-50 hover:bg-red-100 text-red-600 text-xs font-bold rounded-xl shadow-xs transition-all duration-200"
                    >
                        Cancel Renew
                    </button>
                `;
            }

            // Expanded drawers
            let drawerHTML = '';
            if (isExpanded) {
                let historyRows = '';
                sub.history.forEach(hist => {
                    historyRows += `
                        <tr>
                            <td class="px-4 py-2.5 capitalize">${hist.planType}</td>
                            <td class="px-4 py-2.5">
                                <span class="px-1.5 py-0.5 rounded text-[9px] uppercase border ${statusColors[hist.status] || 'bg-gray-50 text-gray-500'}">
                                    ${hist.status}
                                </span>
                            </td>
                            <td class="px-4 py-2.5 font-mono">${new Date(hist.startDate).toLocaleDateString('en-IN')}</td>
                            <td class="px-4 py-2.5 font-mono">${new Date(hist.expiryDate).toLocaleDateString('en-IN')}</td>
                            <td class="px-4 py-2.5 font-mono">₹${hist.amountPaid}</td>
                        </tr>
                    `;
                });

                let txnRows = '';
                sub.transactions.forEach(txn => {
                    txnRows += `
                        <tr>
                            <td class="px-4 py-2.5 font-mono">${new Date(txn.timestamp).toLocaleDateString('en-IN')}</td>
                            <td class="px-4 py-2.5 uppercase font-bold text-[10px]">${txn.planId}</td>
                            <td class="px-4 py-2.5 font-mono text-emerald-600">₹${txn.amount}</td>
                            <td class="px-4 py-2.5 italic text-gray-400">${txn.details || 'Subscription checkout'}</td>
                        </tr>
                    `;
                });

                drawerHTML = `
                    <tr class="bg-gray-50/50">
                        <td colSpan="6" class="p-6 border-y border-gray-100">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                <div class="space-y-3">
                                    <h5 class="text-xs font-black text-wedding-charcoal-dark uppercase tracking-wider flex items-center gap-1.5">
                                        <i data-lucide="layers" class="w-4 h-4 text-wedding-pink-dark"></i> Plan History Records
                                    </h5>
                                    <div class="bg-white border border-gray-100 rounded-2xl overflow-hidden">
                                        <table class="w-full text-left border-collapse text-xs">
                                            <thead>
                                                <tr class="bg-gray-50 border-b border-gray-100 font-bold">
                                                    <th class="px-4 py-2.5">Plan</th>
                                                    <th class="px-4 py-2.5">Status</th>
                                                    <th class="px-4 py-2.5">Start</th>
                                                    <th class="px-4 py-2.5">Expiry</th>
                                                    <th class="px-4 py-2.5">Paid</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-50 font-semibold text-gray-600">
                                                ${historyRows || '<tr><td colspan="5" class="text-center py-4">No history records.</td></tr>'}
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <div class="space-y-3">
                                    <h5 class="text-xs font-black text-wedding-charcoal-dark uppercase tracking-wider flex items-center gap-1.5">
                                        <i data-lucide="coins" class="w-4 h-4 text-wedding-pink-dark"></i> Billing Transaction Invoices
                                    </h5>
                                    <div class="bg-white border border-gray-100 rounded-2xl overflow-hidden">
                                        <table class="w-full text-left border-collapse text-xs">
                                            <thead>
                                                <tr class="bg-gray-50 border-b border-gray-100 font-bold">
                                                    <th class="px-4 py-2.5">Date</th>
                                                    <th class="px-4 py-2.5">Type</th>
                                                    <th class="px-4 py-2.5">Amount</th>
                                                    <th class="px-4 py-2.5">Details</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-50 font-semibold text-gray-600">
                                                ${txnRows || '<tr><td colspan="4" class="text-center py-4">No invoices.</td></tr>'}
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                `;
            }

            html += `
                <tr class="hover:bg-gray-50/60 transition-colors">
                    <td class="px-6 py-4">
                        <div>
                            <p class="font-bold text-wedding-charcoal-dark text-sm">${sub.displayName || 'App User'}</p>
                            <p class="text-[10px] text-gray-400 font-mono">${sub.email || sub.phone || 'anonymous'}</p>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="font-bold text-xs uppercase text-wedding-charcoal-light">
                            ${active ? active.planType : 'None'}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-2.5 py-1 border text-[10px] font-bold rounded-lg uppercase ${statusClass}">
                            ${statusLabel}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-xs font-semibold text-gray-600 font-mono">
                        ${expiry}
                    </td>
                    <td class="px-6 py-4 text-xs">
                        <button
                            onclick="toggleExpandSub('${sub.userId}')"
                            class="flex items-center gap-1 text-wedding-pink-dark hover:underline font-bold"
                        >
                            ${sub.history.length} plan(s) / ${sub.transactions.length} invoice(s)
                            <i data-lucide="${isExpanded ? 'chevron-up' : 'chevron-down'}" class="w-3.5 h-3.5"></i>
                        </button>
                    </td>
                    <td class="px-6 py-4 text-right">
                        ${cancelBtnHTML}
                    </td>
                </tr>
                ${drawerHTML}
            `;
        });

        body.innerHTML = html;
        lucide.createIcons({ nodeList: [body] });
    }

    function toggleExpandSub(userId) {
        expandedSubId = expandedSubId === userId ? null : userId;
        renderSubscribersList();
    }

    function filterSubscribers() {
        renderSubscribersList();
    }

    async function cancelRenew(userId) {
        if (!confirm('Are you sure you want to cancel this subscriber\'s auto-renewal?')) return;
        try {
            const res = await fetch(`/api/user-subscriptions/${userId}/cancel`, {
                method: 'POST',
                headers
            });
            if (res.ok) {
                Toast.show('Subscription cancelled successfully.', 'success');
                fetchAnalyticsData();
            } else {
                Toast.show('Failed to cancel renewal.', 'error');
            }
        } catch (e) {
            console.error(e);
            Toast.show('Network error.', 'error');
        }
    }

    // MODALS ACTIONS
    function openCreateModal() {
        document.getElementById('new-plan-id').value = '';
        document.getElementById('new-plan-name').value = '';
        document.getElementById('new-plan-price').value = '0';
        document.getElementById('new-plan-duration-type').value = 'monthly';
        document.getElementById('new-plan-start-date').value = '';
        document.getElementById('new-plan-end-date').value = '';
        document.getElementById('new-plan-desc').value = '';
        document.getElementById('new-plan-features').value = '';
        document.getElementById('new-plan-active').checked = true;
        document.getElementById('new-plan-custom-dates').classList.add('hidden');

        // Populate categories check
        const catsContainer = document.getElementById('new-plan-categories');
        catsContainer.innerHTML = '';
        categoriesList.forEach(cat => {
            catsContainer.innerHTML += `
                <label class="flex items-center gap-2 bg-wedding-bg hover:bg-wedding-pink-light/30 border border-wedding-pink-medium/40 px-3 py-1.5 rounded-xl text-xs font-bold cursor-pointer select-none">
                    <input type="checkbox" name="new-plan-cats-input" value="${cat.id}" class="rounded text-wedding-pink-dark focus:ring-wedding-pink-dark/20" />
                    <span>${cat.name}</span>
                </label>
            `;
        });

        document.getElementById('create-plan-modal').classList.remove('hidden');
    }

    function closeCreateModal() {
        document.getElementById('create-plan-modal').classList.add('hidden');
    }

    function handleNewDurationTypeChange(val) {
        const datesDiv = document.getElementById('new-plan-custom-dates');
        if (val === 'custom') {
            datesDiv.classList.remove('hidden');
        } else {
            datesDiv.classList.add('hidden');
        }
    }

    async function handleCreatePlan(e) {
        e.preventDefault();
        
        const id = document.getElementById('new-plan-id').value.trim();
        const name = document.getElementById('new-plan-name').value.trim();
        const price = Number(document.getElementById('new-plan-price').value);
        const durationType = document.getElementById('new-plan-duration-type').value;
        const customStartDate = durationType === 'custom' ? document.getElementById('new-plan-start-date').value : null;
        const customEndDate = durationType === 'custom' ? document.getElementById('new-plan-end-date').value : null;
        const description = document.getElementById('new-plan-desc').value.trim();
        const featuresRaw = document.getElementById('new-plan-features').value;
        const features = featuresRaw ? featuresRaw.split(',').map(f => f.trim()).filter(Boolean) : [];
        const isActive = document.getElementById('new-plan-active').checked;

        // Get selected categories
        const checkedCats = [];
        document.querySelectorAll('input[name="new-plan-cats-input"]:checked').forEach(cb => {
            checkedCats.push(cb.value);
        });

        let durationDays = 30;
        if (durationType === '1day') durationDays = 1;
        else if (durationType === 'weekly') durationDays = 7;
        else if (durationType === 'monthly') durationDays = 30;
        else if (durationType === 'yearly') durationDays = 365;
        else if (durationType === 'custom') durationDays = 0;

        const payload = {
            id,
            name,
            price,
            durationType,
            durationDays,
            customStartDate,
            customEndDate,
            description,
            features,
            isActive,
            includedCategories: checkedCats,
            includedTemplateIds: []
        };

        const submitBtn = document.getElementById('create-submit-btn');
        submitBtn.disabled = true;
        submitBtn.innerText = 'Creating...';

        try {
            const res = await fetch(`/api/subscriptions`, {
                method: 'POST',
                headers,
                body: JSON.stringify(payload)
            });

            if (res.ok) {
                closeCreateModal();
                fetchInitialData();
                Toast.show('Subscription plan created successfully!', 'success');
            } else {
                const err = await res.json();
                Toast.show(err.error || 'Failed to create plan.', 'error');
            }
        } catch (error) {
            console.error(error);
            Toast.show('Network error. Failed to create.', 'error');
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerText = 'Create Plan';
        }
    }

    // MANUAL ASSIGN
    function openAssignModal() {
        document.getElementById('assign-user-select').value = '';
        document.getElementById('assign-plan-select').value = plansList.length > 0 ? plansList[0].id : '';
        document.getElementById('assign-amount').value = plansList.length > 0 ? plansList[0].price : '0';
        document.getElementById('assign-expiry').value = '';

        document.getElementById('manual-assign-modal').classList.remove('hidden');
    }

    function closeAssignModal() {
        document.getElementById('manual-assign-modal').classList.add('hidden');
    }

    function handleAssignPlanChange(val) {
        const plan = plansList.find(p => p.id === val);
        if (plan) {
            document.getElementById('assign-amount').value = plan.price;
        }
    }

    async function handleManualAssign(e) {
        e.preventDefault();

        const userId = document.getElementById('assign-user-select').value;
        const planType = document.getElementById('assign-plan-select').value;
        const expiryDate = document.getElementById('assign-expiry').value;
        const amountPaid = Number(document.getElementById('assign-amount').value);

        if (!userId || !expiryDate) {
            Toast.show('Please fill in user and expiration date.', 'warning');
            return;
        }

        const payload = {
            userId,
            planType,
            expiryDate: new Date(expiryDate).toISOString(),
            amountPaid
        };

        const submitBtn = document.getElementById('assign-submit-btn');
        submitBtn.disabled = true;
        submitBtn.innerText = 'Assigning...';

        try {
            const res = await fetch(`/api/user-subscriptions`, {
                method: 'POST',
                headers,
                body: JSON.stringify(payload)
            });

            if (res.ok) {
                closeAssignModal();
                fetchAnalyticsData();
                Toast.show('Subscription manually assigned successfully!', 'success');
            } else {
                const err = await res.json();
                Toast.show(err.error || 'Failed to assign.', 'error');
            }
        } catch (error) {
            console.error(error);
            Toast.show('Failed to assign.', 'error');
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerText = 'Assign Plan';
        }
    }
</script>
@endpush
