@extends('admin.layouts.app')

@section('title', 'User Management')
@section('header_title', 'User Management')

@php $activeTab = 'users'; @endphp

@section('content')
<div class="space-y-6 animate-fadeIn">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-extrabold text-wedding-charcoal-dark font-sans tracking-wide">
                USER DIRECTORY
            </h2>
            <p class="text-xs text-gray-500 font-semibold mt-1">
                Manage administrator profiles, assign roles, and configure individual custom permission overrides.
            </p>
        </div>

        <div id="add-btn-container">
            <!-- Dynamically populated based on permission -->
        </div>
    </div>

    <!-- Tabs Switcher -->
    <div class="flex gap-2 p-1 bg-[#FFF5F6]/45 border border-[#FFCAD2]/40 rounded-2xl w-fit shadow-xs">
        <button
            onclick="switchUserTab('staff')"
            id="tab-btn-staff"
            class="tab-user-btn flex items-center gap-2 px-5 py-2.5 rounded-xl text-xs font-bold transition-all duration-300 bg-wedding-charcoal-dark text-wedding-gold-light shadow-md"
        >
            <i data-lucide="shield" class="w-3.5 h-3.5"></i>
            Staff Accounts
            <span id="badge-staff-count" class="ml-1 px-2 py-0.5 text-[10px] rounded-md font-mono bg-wedding-charcoal-light text-white">0</span>
        </button>
        <button
            onclick="switchUserTab('app_users')"
            id="tab-btn-app_users"
            class="tab-user-btn flex items-center gap-2 px-5 py-2.5 rounded-xl text-xs font-bold transition-all duration-300 text-gray-500 hover:text-wedding-charcoal-dark hover:bg-white/50"
        >
            <i data-lucide="smartphone" class="w-3.5 h-3.5"></i>
            Mobile App Users
            <span id="badge-app-count" class="ml-1 px-2 py-0.5 text-[10px] rounded-md font-mono bg-gray-100 text-gray-600">0</span>
        </button>
    </div>

    <!-- Search & Filters Bar -->
    <div class="bg-white rounded-[24px] border border-wedding-pink-medium/10 p-5 shadow-[0_8px_30px_rgba(0,0,0,0.015)] flex flex-col sm:flex-row gap-4 items-center">
        <div class="relative flex-1 w-full">
            <i data-lucide="search" class="w-4 h-4 text-gray-400 absolute left-4 top-1/2 transform -translate-y-1/2"></i>
            <input
                type="text"
                id="user-search"
                oninput="filterUsers()"
                placeholder="Search by name or email address..."
                class="w-full pl-11 pr-4 py-3 bg-[#FFF5F6]/30 border border-[#FFCAD2]/55 rounded-2xl text-wedding-charcoal-dark placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-wedding-pink-dark/25 focus:bg-white text-sm font-semibold transition-all"
            />
        </div>

        <div class="relative w-full sm:w-auto" id="role-filter-container">
            <select
                id="role-filter"
                onchange="filterUsers()"
                class="w-full sm:w-48 px-4 py-3 bg-[#FFF5F6]/30 border border-[#FFCAD2]/55 rounded-2xl text-wedding-charcoal-dark focus:outline-none focus:ring-2 focus:ring-wedding-pink-dark/25 text-sm font-semibold transition-all appearance-none pr-8 cursor-pointer"
            >
                <option value="">All Roles</option>
            </select>
            <i data-lucide="chevron-down" class="w-4 h-4 text-gray-400 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none"></i>
        </div>
    </div>

    <!-- Users Table Container -->
    <div id="users-loading" class="flex flex-col items-center justify-center min-h-[40vh] gap-3">
        <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-wedding-pink-dark"></div>
        <p class="text-xs text-gray-400 font-bold">Loading user directory...</p>
    </div>

    <div id="users-table-card" class="bg-white rounded-[28px] border border-wedding-pink-medium/10 shadow-[0_8px_30px_rgba(0,0,0,0.02)] overflow-hidden hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse min-w-[900px]">
                <thead id="table-head">
                    <!-- Loaded dynamically based on tab -->
                </thead>
                <tbody id="users-list-body" class="divide-y divide-gray-100">
                    <!-- Dynamic Users -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- Create/Edit Staff Modal -->
    <div id="staff-modal" class="fixed inset-0 bg-wedding-charcoal-dark/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden animate-fadeIn">
        <div class="bg-wedding-bg border border-wedding-pink-medium/40 w-full max-w-2xl rounded-3xl shadow-2xl overflow-hidden animate-slideUp max-h-[90vh] flex flex-col">
            <div class="p-6 bg-wedding-charcoal-dark text-white flex justify-between items-center">
                <h4 id="staff-modal-title" class="font-bold text-lg text-wedding-gold-light">Register New Administrator</h4>
                <button type="button" onclick="closeStaffModal()" class="text-gray-400 hover:text-white font-bold text-sm bg-wedding-charcoal-light px-3 py-1.5 rounded-xl">✕</button>
            </div>
            
            <form onsubmit="handleStaffSubmit(event)" class="p-6 space-y-5 overflow-y-auto flex-1">
                <input type="hidden" id="staff-id" value="">
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-wedding-charcoal-light uppercase tracking-wider">Display Name</label>
                        <input type="text" id="staff-name" placeholder="e.g. John Doe" class="w-full px-4 py-3 rounded-2xl bg-white border border-wedding-pink-medium/40 text-sm focus:ring-2 focus:ring-wedding-pink-dark/20 focus:outline-none" required />
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-wedding-charcoal-light uppercase tracking-wider">Email Address</label>
                        <input type="email" id="staff-email" placeholder="e.g. john@amantran.com" class="w-full px-4 py-3 rounded-2xl bg-white border border-wedding-pink-medium/40 text-sm focus:ring-2 focus:ring-wedding-pink-dark/20 focus:outline-none" required />
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-wedding-charcoal-light uppercase tracking-wider">Assigned Role</label>
                        <select id="staff-role" onchange="handleStaffRoleChange(this.value)" class="w-full px-4 py-3 rounded-2xl bg-white border border-wedding-pink-medium/40 text-sm focus:ring-2 focus:ring-wedding-pink-dark/20 focus:outline-none font-semibold">
                            <!-- Populated dynamically -->
                        </select>
                    </div>
                    <div class="space-y-1.5">
                        <label id="password-label" class="text-xs font-bold text-wedding-charcoal-light uppercase tracking-wider">Security Password</label>
                        <input type="password" id="staff-password" placeholder="••••••••" class="w-full px-4 py-3 rounded-2xl bg-white border border-wedding-pink-medium/40 text-sm focus:ring-2 focus:ring-wedding-pink-dark/20 focus:outline-none" />
                    </div>
                </div>

                <!-- Custom Permissions Override Toggle -->
                <div class="flex items-center justify-between p-3.5 bg-gray-50 border border-wedding-pink-medium/10 rounded-2xl">
                    <div>
                        <h5 class="text-xs font-black text-wedding-charcoal-dark uppercase tracking-wider">Custom Permissions Override</h5>
                        <p class="text-[10px] text-gray-500 font-semibold mt-0.5">Manually toggle individual permission rules for this account</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" id="staff-custom-override" onchange="toggleCustomPermissionsDiv(this.checked)" class="sr-only peer" />
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-wedding-pink-dark"></div>
                    </label>
                </div>

                <!-- Custom Permissions Checklist -->
                <div id="custom-permissions-container" class="space-y-4 pt-4 border-t border-wedding-pink-medium/10 hidden animate-fadeIn">
                    <h5 class="text-xs font-black text-wedding-charcoal-dark uppercase tracking-wider">Configure Overrides</h5>
                    <div id="permissions-checklist" class="grid grid-cols-1 sm:grid-cols-2 gap-2 bg-gray-50 p-4 border border-wedding-pink-medium/10 rounded-2xl max-h-[220px] overflow-y-auto">
                        <!-- Loaded dynamically -->
                    </div>
                </div>

                <div class="pt-4 border-t border-wedding-pink-medium/20 flex justify-end gap-3">
                    <button type="button" onclick="closeStaffModal()" class="px-5 py-3 rounded-2xl bg-wedding-pink-light/40 border border-wedding-pink-medium/30 text-wedding-charcoal-light hover:bg-wedding-pink-light/80 hover:text-wedding-pink-dark text-sm font-bold transition-all duration-300">Cancel</button>
                    <button type="submit" id="staff-submit-btn" class="px-6 py-3 rounded-2xl bg-wedding-pink-dark text-white text-sm font-bold shadow-lg">Save Profile</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Reset Password Modal -->
    <div id="password-modal" class="fixed inset-0 bg-wedding-charcoal-dark/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden animate-fadeIn">
        <div class="bg-wedding-bg border border-wedding-pink-medium/40 w-full max-w-sm rounded-3xl shadow-2xl overflow-hidden animate-slideUp">
            <div class="p-6 bg-wedding-charcoal-dark text-white flex justify-between items-center">
                <h4 class="font-bold text-lg text-wedding-gold-light">Reset Password</h4>
                <button type="button" onclick="closePasswordModal()" class="text-gray-400 hover:text-white font-bold text-sm bg-wedding-charcoal-light px-3 py-1.5 rounded-xl">✕</button>
            </div>
            
            <form onsubmit="handlePasswordResetSubmit(event)" class="p-6 space-y-4">
                <input type="hidden" id="password-user-id" value="">
                <p class="text-xs text-gray-500 font-semibold leading-relaxed">
                    Set a new security password for administrator: <strong id="password-user-email" class="text-wedding-charcoal-dark"></strong>
                </p>

                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-wedding-charcoal-light uppercase tracking-wider">New Password</label>
                    <input type="password" id="reset-password-val" placeholder="••••••••" class="w-full px-4 py-3 rounded-2xl bg-white border border-wedding-pink-medium/40 text-sm focus:outline-none" required />
                </div>
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-wedding-charcoal-light uppercase tracking-wider">Confirm Password</label>
                    <input type="password" id="reset-confirm-val" placeholder="••••••••" class="w-full px-4 py-3 rounded-2xl bg-white border border-wedding-pink-medium/40 text-sm focus:outline-none" required />
                </div>

                <div class="pt-4 border-t border-wedding-pink-medium/20 flex justify-end gap-3">
                    <button type="button" onclick="closePasswordModal()" class="px-5 py-3 rounded-2xl bg-gray-100 text-sm font-bold">Cancel</button>
                    <button type="submit" id="pwd-submit-btn" class="px-6 py-3 rounded-2xl bg-wedding-charcoal-dark text-wedding-gold-light text-sm font-bold shadow-lg">Change Password</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Mobile User Subscription & Ratings Modal -->
    <div id="sub-rating-modal" class="fixed inset-0 bg-wedding-charcoal-dark/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden animate-fadeIn">
        <div class="bg-wedding-bg border border-wedding-pink-medium/40 w-full max-w-xl rounded-3xl shadow-2xl overflow-hidden animate-slideUp max-h-[90vh] flex flex-col">
            <div class="p-6 bg-wedding-charcoal-dark text-white flex justify-between items-center">
                <h4 class="font-bold text-lg text-wedding-gold-light">Mobile User Details & Status</h4>
                <button type="button" onclick="closeSubRatingModal()" class="text-gray-400 hover:text-white font-bold text-sm bg-wedding-charcoal-light px-3 py-1.5 rounded-xl">✕</button>
            </div>
            
            <div class="p-6 space-y-6 overflow-y-auto flex-1">
                <!-- User Core Info -->
                <div class="flex gap-4 items-center bg-wedding-pink-light/35 p-4 border border-wedding-pink-medium/25 rounded-2xl">
                    <div id="modal-user-avatar" class="w-12 h-12 rounded-full bg-wedding-pink-dark text-white flex items-center justify-center font-extrabold text-sm border border-wedding-pink-medium/30">US</div>
                    <div>
                        <h5 id="modal-user-name" class="font-extrabold text-wedding-charcoal-dark">Anonymous User</h5>
                        <p id="modal-user-sub" class="text-xs text-gray-500 font-medium"></p>
                    </div>
                </div>

                <!-- Subscriptions Form Card -->
                <form onsubmit="handleSubSubmit(event)" class="space-y-4">
                    <h4 class="text-xs font-black text-wedding-charcoal-dark uppercase tracking-wider flex items-center gap-1.5">
                        <i data-lucide="award" class="w-4 h-4 text-wedding-pink-dark"></i> Active Premium Subscription
                    </h4>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-wedding-charcoal-light uppercase tracking-wider">Plan Access Level</label>
                            <select id="modal-sub-plan" onchange="handleModalPlanChange(this.value)" class="w-full px-3 py-2.5 rounded-xl bg-white border border-wedding-pink-medium/40 text-xs font-semibold focus:outline-none">
                                <!-- Populated dynamically -->
                            </select>
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-wedding-charcoal-light uppercase tracking-wider">Amount Paid (₹)</label>
                            <input type="number" id="modal-sub-amount" class="w-full px-3 py-2.5 rounded-xl bg-white border border-wedding-pink-medium/40 text-xs font-semibold focus:outline-none" required />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-wedding-charcoal-light uppercase tracking-wider">Start Date</label>
                            <input type="date" id="modal-sub-start" class="w-full px-3 py-2.5 rounded-xl bg-white border border-wedding-pink-medium/40 text-xs font-semibold focus:outline-none" required />
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-wedding-charcoal-light uppercase tracking-wider">Expiry Date</label>
                            <input type="date" id="modal-sub-expiry" class="w-full px-3 py-2.5 rounded-xl bg-white border border-wedding-pink-medium/40 text-xs font-semibold focus:outline-none" required />
                        </div>
                    </div>

                    <div class="flex items-center justify-between p-3.5 bg-gray-50 border border-wedding-pink-medium/10 rounded-2xl">
                        <div class="text-xs font-semibold text-gray-500">Subscription Status (Active/Suspended)</div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" id="modal-sub-active" checked class="sr-only peer" />
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-wedding-pink-dark"></div>
                        </label>
                    </div>

                    <div class="flex justify-between gap-3 pt-2">
                        <button type="button" onclick="handleRevokeSub()" id="modal-revoke-sub-btn" class="px-4 py-2.5 border border-red-200 bg-red-50 hover:bg-red-100 text-red-600 text-xs font-bold rounded-xl shadow-xs transition-all duration-200 hidden">
                            Revoke Membership
                        </button>
                        <button type="submit" id="modal-save-sub-btn" class="px-5 py-2.5 bg-wedding-charcoal-dark hover:bg-wedding-charcoal-light text-wedding-gold-light text-xs font-bold rounded-xl shadow-lg ml-auto">
                            Save Membership
                        </button>
                    </div>
                </form>

                <!-- User Saved Designs & Drafts -->
                <div class="space-y-4 pt-4 border-t border-wedding-pink-medium/15">
                    <h4 class="text-xs font-black text-wedding-charcoal-dark uppercase tracking-wider flex items-center gap-1.5 font-sans">
                        <i data-lucide="folder-open" class="w-4 h-4 text-wedding-pink-dark"></i> User Saved Designs & Drafts
                    </h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Drafts List -->
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-black text-wedding-charcoal-light uppercase tracking-wider flex items-center gap-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Drafts (<span id="modal-drafts-count">0</span>)
                            </label>
                            <div id="modal-drafts-list" class="space-y-1.5 max-h-[140px] overflow-y-auto bg-gray-50 border border-wedding-pink-medium/10 p-2.5 rounded-2xl">
                                <!-- Loaded dynamically -->
                            </div>
                        </div>
                        <!-- Completed List -->
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-black text-wedding-charcoal-light uppercase tracking-wider flex items-center gap-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Downloaded/Finalized (<span id="modal-cards-count">0</span>)
                            </label>
                            <div id="modal-cards-list" class="space-y-1.5 max-h-[140px] overflow-y-auto bg-gray-50 border border-wedding-pink-medium/10 p-2.5 rounded-2xl">
                                <!-- Loaded dynamically -->
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Ratings Feedback History -->
                <div class="space-y-4 pt-4 border-t border-wedding-pink-medium/15">
                    <h4 class="text-xs font-black text-wedding-charcoal-dark uppercase tracking-wider flex items-center gap-1.5">
                        <i data-lucide="star" class="w-4 h-4 text-wedding-pink-dark fill-wedding-pink-dark"></i> App User Feedback & Rating Reviews
                    </h4>
                    <div id="modal-ratings-list" class="space-y-2 max-h-[160px] overflow-y-auto">
                        <!-- Populated dynamically -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let activeUserTab = 'staff';
    let usersList = [];
    let appUsersList = [];
    let rolesList = [];
    let plansList = [];
    
    // Search and filter parameters
    const headers = {
        'x-user-id': window.CurrentUser ? window.CurrentUser.id : 'admin_super',
        'Content-Type': 'application/json'
    };

    const ALL_PERMISSIONS = [
        { id: 'dashboard.view', name: 'View Dashboard & Stats' },
        { id: 'templates.view', name: 'View Templates' },
        { id: 'templates.create', name: 'Create Templates' },
        { id: 'templates.edit', name: 'Edit Templates' },
        { id: 'templates.delete', name: 'Delete Templates' },
        { id: 'templates.publish', name: 'Publish / Unpublish Templates' },
        { id: 'categories.view', name: 'View Categories' },
        { id: 'categories.create', name: 'Create Categories' },
        { id: 'categories.edit', name: 'Edit Categories' },
        { id: 'categories.delete', name: 'Delete Categories' },
        { id: 'fonts.view', name: 'View Fonts' },
        { id: 'fonts.create', name: 'Upload Fonts' },
        { id: 'fonts.edit', name: 'Edit Fonts' },
        { id: 'fonts.delete', name: 'Delete Fonts' },
        { id: 'languages.view', name: 'View Languages' },
        { id: 'languages.create', name: 'Add Languages' },
        { id: 'languages.edit', name: 'Edit Languages' },
        { id: 'languages.delete', name: 'Delete Languages' },
        { id: 'subscriptions.view', name: 'View Subscriptions' },
        { id: 'subscriptions.create', name: 'Create Subscription Plans' },
        { id: 'subscriptions.edit', name: 'Edit Subscription Plans' },
        { id: 'subscriptions.delete', name: 'Delete Subscription Plans' },
        { id: 'subscriptions.activate', name: 'Activate Plans' },
        { id: 'subscriptions.deactivate', name: 'Deactivate Plans' },
        { id: 'subscriptions.manage_pricing', name: 'Manage Pricing' },
        { id: 'users.view', name: 'View Users' },
        { id: 'users.create', name: 'Create Users' },
        { id: 'users.edit', name: 'Edit Users' },
        { id: 'users.delete', name: 'Delete Users' },
        { id: 'users.suspend', name: 'Suspend Users' },
        { id: 'users.activate', name: 'Activate Suspended Users' },
        { id: 'users.assign_roles', name: 'Assign Roles to Users' },
        { id: 'users.manage_permissions', name: 'Manage Custom Permission Overrides' },
        { id: 'roles.view', name: 'View Roles & Audit Logs' },
        { id: 'roles.create', name: 'Create Custom Roles' },
        { id: 'roles.edit', name: 'Edit Role Settings' },
        { id: 'roles.delete', name: 'Delete Roles' },
        { id: 'roles.assign_permissions', name: 'Assign Role Permissions' },
        { id: 'settings.view', name: 'View System Settings' },
        { id: 'settings.edit', name: 'Edit System Settings' },
    ];

    function hasPermission(perm) {
        if (!window.CurrentUser) return false;
        const rId = window.CurrentUser.roleId || window.CurrentUser.role || 'viewer';
        if (rId === 'super_admin') return true;
        if (window.CurrentUser.permissions && window.CurrentUser.permissions.includes('*')) return true;
        return window.CurrentUser.permissions && window.CurrentUser.permissions.includes(perm);
    }

    document.addEventListener('DOMContentLoaded', () => {
        // Setup Create User Button if permitted
        if (hasPermission('users.create')) {
            document.getElementById('add-btn-container').innerHTML = `
                <button
                    onclick="openAddModal()"
                    id="add-user-btn"
                    class="flex items-center gap-2 px-5 py-3 bg-gradient-to-r from-wedding-pink-dark to-[#ff6b81] hover:from-[#e62e47] hover:to-[#ff526e] text-white text-xs font-bold rounded-2xl shadow-md transition-all transform hover:-translate-y-0.5 shrink-0"
                >
                    <i data-lucide="plus-circle" class="w-4 h-4"></i>
                    Register New User
                </button>
            `;
            lucide.createIcons({ nodeList: [document.getElementById('add-btn-container')] });
        }

        // Load roles and custom permissions checklist dynamically
        fetchRoles().then(() => {
            fetchInitialData();
            // Polling interval
            setInterval(fetchInitialDataSilent, 8000);
        });
    });

    async function fetchRoles() {
        try {
            const res = await fetch(`/api/roles`, { headers });
            rolesList = await res.json();
            
            // Populate filters
            const rFilter = document.getElementById('role-filter');
            rolesList.forEach(r => {
                const opt = document.createElement('option');
                opt.value = r.id;
                opt.innerText = r.name;
                rFilter.appendChild(opt);
            });

            // Populate form select
            const rSelect = document.getElementById('staff-role');
            rolesList.forEach(r => {
                const opt = document.createElement('option');
                opt.value = r.id;
                opt.innerText = r.name;
                rSelect.appendChild(opt);
            });

            // Render custom permissions checklist in modal
            const checklist = document.getElementById('permissions-checklist');
            checklist.innerHTML = '';
            ALL_PERMISSIONS.forEach(p => {
                checklist.innerHTML += `
                    <label class="flex items-center gap-2 bg-white p-2 rounded-xl text-xs font-semibold border border-wedding-pink-medium/10 cursor-pointer select-none">
                        <input type="checkbox" name="custom-perm-box" value="${p.id}" class="rounded text-wedding-pink-dark focus:ring-wedding-pink-dark/20" />
                        <span>${p.name}</span>
                    </label>
                `;
            });

        } catch (e) {
            console.error('Failed to load roles', e);
        }
    }

    function switchUserTab(tab) {
        activeUserTab = tab;
        document.querySelectorAll('.tab-user-btn').forEach(btn => {
            btn.classList.remove('bg-wedding-charcoal-dark', 'text-wedding-gold-light', 'shadow-md');
            btn.classList.add('text-gray-500', 'hover:text-wedding-charcoal-dark', 'hover:bg-white/50');
        });
        document.getElementById(`tab-btn-${tab}`).classList.add('bg-wedding-charcoal-dark', 'text-wedding-gold-light', 'shadow-md');
        document.getElementById(`tab-btn-${tab}`).classList.remove('text-gray-500', 'hover:text-wedding-charcoal-dark', 'hover:bg-white/50');

        const roleFilterDiv = document.getElementById('role-filter-container');
        const addUserBtn = document.getElementById('add-user-btn');

        if (tab === 'staff') {
            roleFilterDiv.classList.remove('hidden');
            if (addUserBtn) addUserBtn.classList.remove('hidden');
        } else {
            roleFilterDiv.classList.add('hidden');
            if (addUserBtn) addUserBtn.classList.add('hidden');
        }

        document.getElementById('user-search').value = '';
        if (tab === 'staff') {
            document.getElementById('role-filter').value = '';
        }

        fetchInitialData();
    }

    async function fetchInitialData() {
        document.getElementById('users-loading').classList.remove('hidden');
        document.getElementById('users-table-card').classList.add('hidden');
        await fetchInitialDataSilent();
        document.getElementById('users-loading').classList.add('hidden');
        document.getElementById('users-table-card').classList.remove('hidden');
    }

    async function fetchInitialDataSilent() {
        try {
            const query = document.getElementById('user-search').value.trim();
            const params = new URLSearchParams();
            if (query) params.append('query', query);

            const [resStaff, resMobile] = await Promise.all([
                fetch(`/api/users?${params.toString()}`, { headers }).then(r => r.json()),
                fetch(`/api/users/app-users?${params.toString()}`, { headers }).then(r => r.json())
            ]);

            usersList = Array.isArray(resStaff) ? resStaff : [];
            appUsersList = Array.isArray(resMobile) ? resMobile : [];

            // Update badge counts
            document.getElementById('badge-staff-count').innerText = usersList.length;
            document.getElementById('badge-app-count').innerText = appUsersList.length;

            renderTableHead();
            renderUsers();
        } catch (e) {
            console.error(e);
        }
    }

    function renderTableHead() {
        const thead = document.getElementById('table-head');
        if (activeUserTab === 'staff') {
            thead.innerHTML = `
                <tr class="bg-gray-50 border-b border-gray-100">
                    <th class="px-6 py-4 text-[10px] font-black text-gray-500 uppercase tracking-wider">User Profile</th>
                    <th class="px-6 py-4 text-[10px] font-black text-gray-500 uppercase tracking-wider">Role</th>
                    <th class="px-6 py-4 text-[10px] font-black text-gray-500 uppercase tracking-wider">Join Date</th>
                    <th class="px-6 py-4 text-[10px] font-black text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-4 text-[10px] font-black text-gray-500 uppercase tracking-wider text-right">Actions</th>
                </tr>
            `;
        } else {
            thead.innerHTML = `
                <tr class="bg-gray-50 border-b border-gray-100">
                    <th class="px-6 py-4 text-[10px] font-black text-gray-500 uppercase tracking-wider">Mobile User</th>
                    <th class="px-6 py-4 text-[10px] font-black text-gray-500 uppercase tracking-wider">Provider</th>
                    <th class="px-6 py-4 text-[10px] font-black text-gray-500 uppercase tracking-wider">Active Pass</th>
                    <th class="px-6 py-4 text-[10px] font-black text-gray-500 uppercase tracking-wider">Invites Created</th>
                    <th class="px-6 py-4 text-[10px] font-black text-gray-500 uppercase tracking-wider">Drafts</th>
                    <th class="px-6 py-4 text-[10px] font-black text-gray-500 uppercase tracking-wider">Join Date</th>
                    <th class="px-6 py-4 text-[10px] font-black text-gray-500 uppercase tracking-wider">Feedback</th>
                    <th class="px-6 py-4 text-[10px] font-black text-gray-500 uppercase tracking-wider text-right">Actions</th>
                </tr>
            `;
        }
    }

    function getRoleBadgeColor(rId) {
        const colorMap = {
            super_admin: 'bg-red-50 text-red-700 border-red-200',
            admin: 'bg-orange-50 text-orange-700 border-orange-200',
            content_manager: 'bg-green-50 text-green-700 border-green-200',
            subscription_manager: 'bg-indigo-50 text-indigo-700 border-indigo-200',
            editor: 'bg-blue-50 text-blue-700 border-blue-200',
            user: 'bg-gray-50 text-gray-600 border-gray-200'
        };
        return colorMap[rId] || 'bg-purple-50 text-purple-700 border-purple-200';
    }

    function renderUsers() {
        const tbody = document.getElementById('users-list-body');
        
        if (activeUserTab === 'staff') {
            const roleFilterVal = document.getElementById('role-filter').value;
            let filteredStaff = usersList;
            if (roleFilterVal) {
                filteredStaff = filteredStaff.filter(u => u.roleId === roleFilterVal || u.role === roleFilterVal);
            }

            if (filteredStaff.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colSpan="5" class="py-16 text-center text-gray-400">
                            <i data-lucide="users" class="w-10 h-10 text-gray-200 mx-auto mb-2"></i>
                            <p class="text-sm font-bold text-gray-500">No staff accounts found.</p>
                        </td>
                    </tr>
                `;
                lucide.createIcons({ nodeList: [tbody] });
                return;
            }

            let html = '';
            filteredStaff.forEach(user => {
                const initials = (user.displayName || 'AD').slice(0, 2).toUpperCase();
                const matchedRole = rolesList.find(r => r.id === user.roleId || r.id === user.role);
                const roleLabel = matchedRole ? matchedRole.name : (user.roleId || user.role || 'editor');
                const badgeColor = getRoleBadgeColor(user.roleId || user.role);
                const joinDate = user.createdAt ? new Date(user.createdAt).toLocaleDateString('en-IN') : '—';

                const statusHTML = user.isBlocked 
                    ? `<span class="flex items-center gap-1.5 text-red-600 text-xs font-bold bg-red-50 border border-red-200 px-2.5 py-1 rounded-lg w-fit">
                        <i data-lucide="shield-alert" class="w-3.5 h-3.5"></i> Suspended
                       </span>`
                    : `<span class="flex items-center gap-1.5 text-green-700 text-xs font-bold bg-green-50 border border-green-200 px-2.5 py-1 rounded-lg w-fit">
                        <i data-lucide="shield-check" class="w-3.5 h-3.5"></i> Active
                       </span>`;

                let actionsHTML = '';
                if (hasPermission('users.edit')) {
                    actionsHTML += `
                        <button
                            onclick='openEditModal(${JSON.stringify(user).replace(/'/g, "&apos;")})'
                            class="p-2 text-gray-400 hover:text-wedding-charcoal-dark hover:bg-gray-100 rounded-xl transition-all border border-transparent hover:border-gray-200"
                            title="Edit User Profile"
                        >
                            <i data-lucide="edit-3" class="w-4 h-4"></i>
                        </button>
                    `;
                }

                if (hasPermission('*')) {
                    actionsHTML += `
                        <button
                            onclick='openPasswordModal(${JSON.stringify(user).replace(/'/g, "&apos;")})'
                            class="p-2 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-xl transition-all border border-transparent hover:border-indigo-100"
                            title="Reset Password"
                        >
                            <i data-lucide="lock" class="w-4 h-4"></i>
                        </button>
                    `;
                }

                if (hasPermission('users.suspend') || hasPermission('users.activate')) {
                    const blockBtnLabel = user.isBlocked ? 'Activate' : 'Suspend';
                    const blockBtnClass = user.isBlocked
                        ? 'bg-green-50 border-green-200 text-green-700 hover:bg-green-100'
                        : 'bg-red-50 border-red-200 text-red-600 hover:bg-red-100';

                    actionsHTML += `
                        <button
                            onclick="handleToggleBlock('${user.id}', ${user.isBlocked})"
                            class="px-3 py-1.5 rounded-xl border text-xs font-bold flex items-center gap-1 transition-all ${blockBtnClass}"
                        >
                            ${blockBtnLabel}
                        </button>
                    `;
                }

                if (hasPermission('users.delete') && user.id !== 'admin_super' && user.id !== window.CurrentUser?.id) {
                    actionsHTML += `
                        <button
                            onclick="handleDelete('${user.id}')"
                            class="p-2 text-red-500 hover:text-red-700 hover:bg-red-50 rounded-xl transition-all"
                            title="Delete administrator account"
                        >
                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                        </button>
                    `;
                }

                html += `
                    <tr class="hover:bg-gray-50/60 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-wedding-pink-light flex items-center justify-center font-extrabold text-wedding-pink-dark text-sm border border-wedding-pink-medium/30">
                                    ${initials}
                                </div>
                                <div>
                                    <p class="font-bold text-wedding-charcoal-dark text-sm">${user.displayName || '—'}</p>
                                    <p class="text-[10px] text-gray-400 font-mono">${user.email}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2.5 py-1 border text-xs font-bold rounded-lg uppercase ${badgeColor}">
                                ${roleLabel}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-xs font-semibold text-gray-600 font-mono">${joinDate}</td>
                        <td class="px-6 py-4">${statusHTML}</td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex justify-end items-center gap-2">${actionsHTML}</div>
                        </td>
                    </tr>
                `;
            });
            tbody.innerHTML = html;
        } else {
            // App mobile users
            if (appUsersList.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colSpan="8" class="py-16 text-center text-gray-400">
                            <i data-lucide="users" class="w-10 h-10 text-gray-200 mx-auto mb-2"></i>
                            <p class="text-sm font-bold text-gray-500">No mobile users found.</p>
                        </td>
                    </tr>
                `;
                lucide.createIcons({ nodeList: [tbody] });
                return;
            }

            let html = '';
            appUsersList.forEach(user => {
                const initials = (user.displayName || 'US').slice(0, 2).toUpperCase();
                const providerBadge = user.provider === 'google' 
                    ? '<span class="px-2 py-0.5 bg-red-50 text-red-700 text-[10px] font-bold rounded border border-red-100 capitalize">Google</span>'
                    : user.provider === 'apple'
                    ? '<span class="px-2 py-0.5 bg-black text-white text-[10px] font-bold rounded capitalize">Apple</span>'
                    : '<span class="px-2 py-0.5 bg-green-50 text-green-700 text-[10px] font-bold rounded border border-green-100 capitalize">WhatsApp</span>';

                const sub = user.subscription;
                const activePassBadge = sub && sub.isActive
                    ? `<span class="px-2.5 py-1 bg-purple-50 text-purple-700 text-xs font-extrabold rounded-lg border border-purple-200 capitalize">
                        ${sub.planType || 'Premium'}
                       </span>`
                    : '<span class="text-gray-400 font-semibold text-xs">Standard Free</span>';

                const joinDate = user.createdAt ? new Date(user.createdAt).toLocaleDateString('en-IN') : '—';
                
                const starsHTML = user.rating !== null && user.rating !== undefined
                    ? `<span class="flex items-center gap-1 text-amber-500 font-bold text-xs bg-amber-50 px-2 py-1 rounded-lg border border-amber-200 w-fit">
                        <i data-lucide="star" class="w-3.5 h-3.5 fill-amber-500"></i> ${user.rating} / 5
                       </span>`
                    : '<span class="text-gray-300 italic text-xs font-semibold">No feedback</span>';

                let actionsHTML = '';
                if (hasPermission('subscriptions.view')) {
                    actionsHTML += `
                        <button
                            onclick='openSubscriptionModal(${JSON.stringify(user).replace(/'/g, "&apos;")})'
                            class="px-3 py-1.5 bg-wedding-pink-light text-wedding-pink-dark border border-wedding-pink-medium/35 text-xs font-black rounded-xl hover:bg-wedding-pink-light/80 shadow-xs"
                        >
                            Manage Pass & Rating
                        </button>
                    `;
                }

                if (hasPermission('users.suspend') || hasPermission('users.activate')) {
                    const blockBtnLabel = user.isBlocked ? 'Activate' : 'Suspend';
                    const blockBtnClass = user.isBlocked
                        ? 'bg-green-50 border-green-200 text-green-700 hover:bg-green-100'
                        : 'bg-red-50 border-red-200 text-red-600 hover:bg-red-100';

                    actionsHTML += `
                        <button
                            onclick="handleToggleBlock('${user.id}', ${user.isBlocked})"
                            class="px-3 py-1.5 rounded-xl border text-xs font-bold flex items-center gap-1 transition-all ${blockBtnClass}"
                        >
                            ${blockBtnLabel}
                        </button>
                    `;
                }

                if (hasPermission('users.delete')) {
                    actionsHTML += `
                        <button
                            onclick="handleDelete('${user.id}')"
                            class="p-2 text-red-500 hover:text-red-700 hover:bg-red-50 rounded-xl transition-all"
                            title="Delete mobile app account"
                        >
                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                        </button>
                    `;
                }

                html += `
                    <tr class="hover:bg-gray-50/60 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-wedding-pink-light flex items-center justify-center font-extrabold text-wedding-pink-dark text-sm border border-wedding-pink-medium/30">
                                    ${initials}
                                </div>
                                <div>
                                    <p class="font-bold text-wedding-charcoal-dark text-sm">${user.displayName || '—'}</p>
                                    <p class="text-[10px] text-gray-400 font-mono">${user.email || user.phone || 'anonymous'}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">${providerBadge}</td>
                        <td class="px-6 py-4">${activePassBadge}</td>
                        <td class="px-6 py-4">
                            <span class="px-2.5 py-1 bg-emerald-50 text-emerald-700 text-xs font-bold rounded-lg border border-emerald-100">
                                ${user.invitationCount || 0} cards
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2.5 py-1 bg-amber-50 text-amber-700 text-xs font-bold rounded-lg border border-amber-100">
                                ${user.draftsCount || 0} drafts
                            </span>
                        </td>
                        <td class="px-6 py-4 text-xs font-semibold text-gray-600 font-mono">${joinDate}</td>
                        <td class="px-6 py-4">${starsHTML}</td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex justify-end items-center gap-2">${actionsHTML}</div>
                        </td>
                    </tr>
                `;
            });
            tbody.innerHTML = html;
        }

        lucide.createIcons({ nodeList: [tbody] });
    }

    function filterUsers() {
        renderUsers();
    }

    // TOGGLE STATUS
    async function handleToggleBlock(id, currentlyBlocked) {
        const requiredPerm = currentlyBlocked ? 'users.activate' : 'users.suspend';
        if (!hasPermission(requiredPerm)) {
            Toast.show(`Access Denied. Missing permission: ${requiredPerm}`, 'warning');
            return;
        }

        try {
            const endpoint = activeUserTab === 'staff'
                ? `/api/users/${id}`
                : `/api/users/app-users/${id}`;

            const res = await fetch(endpoint, {
                method: 'PUT',
                headers,
                body: JSON.stringify({ isBlocked: !currentlyBlocked })
            });

            if (res.ok) {
                fetchInitialDataSilent();
                Toast.show(currentlyBlocked ? 'User account activated successfully!' : 'User account suspended successfully!', 'success');
            } else {
                const err = await res.json();
                Toast.show(err.error || 'Failed to toggle status.', 'error');
            }
        } catch (e) {
            console.error(e);
            Toast.show('Failed to toggle status.', 'error');
        }
    }

    async function handleDelete(id) {
        if (!hasPermission('users.delete')) {
            Toast.show('Access Denied. Missing users.delete permission.', 'warning');
            return;
        }
        if (!confirm('Are you sure you want to delete this user? This action is permanent.')) return;

        try {
            const endpoint = activeUserTab === 'staff'
                ? `/api/users/${id}`
                : `/api/users/app-users/${id}`;

            const res = await fetch(endpoint, {
                method: 'DELETE',
                headers: { 'x-user-id': window.CurrentUser ? window.CurrentUser.id : 'admin_super' }
            });

            if (res.ok) {
                fetchInitialDataSilent();
                Toast.show('User deleted successfully!', 'success');
            } else {
                const err = await res.json();
                Toast.show(err.error || 'Failed to delete user.', 'error');
            }
        } catch (e) {
            console.error(e);
            Toast.show('Failed to delete user.', 'error');
        }
    }

    // STAFF MODAL
    function openAddModal() {
        document.getElementById('staff-modal-title').innerText = 'Register New Administrator';
        document.getElementById('staff-id').value = '';
        document.getElementById('staff-name').value = '';
        document.getElementById('staff-email').value = '';
        document.getElementById('staff-role').value = rolesList.length > 0 ? rolesList[0].id : '';
        document.getElementById('staff-password').placeholder = 'Password Required';
        document.getElementById('staff-password').required = true;
        document.getElementById('staff-custom-override').checked = false;
        toggleCustomPermissionsDiv(false);

        document.getElementById('staff-modal').classList.remove('hidden');
    }

    function openEditModal(user) {
        document.getElementById('staff-modal-title').innerText = 'Edit Administrator Profile';
        document.getElementById('staff-id').value = user.id;
        document.getElementById('staff-name').value = user.displayName || '';
        document.getElementById('staff-email').value = user.email || '';
        
        const uRole = user.roleId || user.role || 'editor';
        document.getElementById('staff-role').value = uRole;
        document.getElementById('staff-password').placeholder = 'Leave blank to keep current password';
        document.getElementById('staff-password').required = false;
        
        const isCustom = user.isCustomPermissions === true;
        document.getElementById('staff-custom-override').checked = isCustom;
        toggleCustomPermissionsDiv(isCustom);

        // Precheck checkboxes
        const checklist = document.querySelectorAll('input[name="custom-perm-box"]');
        checklist.forEach(cb => {
            cb.checked = false;
        });

        const userPerms = user.customPermissions || user.permissions || [];
        checklist.forEach(cb => {
            if (userPerms.includes(cb.value)) {
                cb.checked = true;
            }
        });

        document.getElementById('staff-modal').classList.remove('hidden');
    }

    function closeStaffModal() {
        document.getElementById('staff-modal').classList.add('hidden');
    }

    function toggleCustomPermissionsDiv(checked) {
        const container = document.getElementById('custom-permissions-container');
        if (checked) {
            container.classList.remove('hidden');
        } else {
            container.classList.add('hidden');
        }
    }

    function handleStaffRoleChange(val) {
        const isOverride = document.getElementById('staff-custom-override').checked;
        if (!isOverride) {
            const matchedRole = rolesList.find(r => r.id === val);
            if (matchedRole) {
                const checklist = document.querySelectorAll('input[name="custom-perm-box"]');
                checklist.forEach(cb => {
                    cb.checked = matchedRole.permissions.includes(cb.value) || matchedRole.permissions.includes('*');
                });
            }
        }
    }

    async function handleStaffSubmit(e) {
        e.preventDefault();

        const id = document.getElementById('staff-id').value;
        const displayName = document.getElementById('staff-name').value.trim();
        const email = document.getElementById('staff-email').value.trim();
        const roleId = document.getElementById('staff-role').value;
        const password = document.getElementById('staff-password').value;
        const isCustomPermissions = document.getElementById('staff-custom-override').checked;

        // Custom permissions
        const customPermissions = [];
        if (isCustomPermissions) {
            document.querySelectorAll('input[name="custom-perm-box"]:checked').forEach(cb => {
                customPermissions.push(cb.value);
            });
        }

        const isCreate = !id;
        const payload = {
            displayName,
            email,
            roleId,
            role: roleId,
            isCustomPermissions,
            customPermissions,
            permissions: isCustomPermissions ? customPermissions : []
        };
        if (password) payload.password = password;

        const reqPerm = isCreate ? 'users.create' : 'users.edit';
        if (!hasPermission(reqPerm)) {
            Toast.show(`Access Denied. You lack the "${reqPerm}" permission.`, 'warning');
            return;
        }

        try {
            const endpoint = isCreate ? `/api/users` : `/api/users/${id}`;
            const res = await fetch(endpoint, {
                method: isCreate ? 'POST' : 'PUT',
                headers,
                body: JSON.stringify(payload)
            });

            if (res.ok) {
                closeStaffModal();
                fetchInitialDataSilent();
                Toast.show(isCreate ? 'User registered successfully!' : 'User profile updated successfully!', 'success');
            } else {
                const err = await res.json();
                Toast.show(err.error || 'Operation failed.', 'error');
            }
        } catch (error) {
            console.error(error);
            Toast.show('Failed to save profile.', 'error');
        }
    }

    // PASSWORD RESET
    function openPasswordModal(user) {
        document.getElementById('password-user-id').value = user.id;
        document.getElementById('password-user-email').innerText = user.email;
        document.getElementById('reset-password-val').value = '';
        document.getElementById('reset-confirm-val').value = '';

        document.getElementById('password-modal').classList.remove('hidden');
    }

    function closePasswordModal() {
        document.getElementById('password-modal').classList.add('hidden');
    }

    async function handlePasswordResetSubmit(e) {
        e.preventDefault();

        const id = document.getElementById('password-user-id').value;
        const pwd = document.getElementById('reset-password-val').value;
        const confirm = document.getElementById('reset-confirm-val').value;

        if (pwd !== confirm) {
            Toast.show('Passwords do not match.', 'error');
            return;
        }
        if (pwd.length < 6) {
            Toast.show('Password must be at least 6 characters.', 'error');
            return;
        }

        try {
            const res = await fetch(`/api/users/${id}`, {
                method: 'PUT',
                headers,
                body: JSON.stringify({ password: pwd })
            });

            if (res.ok) {
                closePasswordModal();
                Toast.show('Password reset successfully!', 'success');
            } else {
                const err = await res.json();
                Toast.show(err.error || 'Reset failed.', 'error');
            }
        } catch (error) {
            console.error(error);
            Toast.show('Failed to reset password.', 'error');
        }
    }

    // MOBILE USER SUB & RATING
    let currentModalUser = null;
    let modalPlansList = [];

    async function openSubscriptionModal(user) {
        currentModalUser = user;
        document.getElementById('modal-user-avatar').innerText = (user.displayName || 'US').slice(0, 2).toUpperCase();
        document.getElementById('modal-user-name').innerText = user.displayName || 'Anonymous User';
        document.getElementById('modal-user-sub').innerText = user.email || user.phone || 'no contact details';

        const sub = user.subscription;

        // Fetch user ratings, plans, drafts, and cards
        const ratingsListDiv = document.getElementById('modal-ratings-list');
        ratingsListDiv.innerHTML = '<p class="text-xs text-gray-400 font-bold">Querying feedback...</p>';

        try {
            const [resRatings, resPlans, resDrafts, resCards] = await Promise.all([
                fetch(`/api/app/ratings/${user.id}`, { headers }).then(r => r.ok ? r.json() : null),
                fetch(`/api/subscriptions`, { headers }).then(r => r.json()),
                fetch(`/api/users/app-users/${user.id}/drafts`, { headers }).then(r => r.ok ? r.json() : []),
                fetch(`/api/users/app-users/${user.id}/cards`, { headers }).then(r => r.ok ? r.json() : [])
            ]);

            modalPlansList = Array.isArray(resPlans) ? resPlans : [];

            // Populate modal select plan
            const planSelect = document.getElementById('modal-sub-plan');
            planSelect.innerHTML = '<option value="">-- No Active Membership --</option>';
            modalPlansList.forEach(p => {
                const opt = document.createElement('option');
                opt.value = p.id;
                opt.innerText = `${p.name} (₹${p.price})`;
                planSelect.appendChild(opt);
            });

            // Set current plan
            if (sub) {
                planSelect.value = sub.planType || sub.type || 'monthly';
                document.getElementById('modal-sub-amount').value = sub.amountPaid || 0;
                document.getElementById('modal-sub-start').value = sub.startDate ? sub.startDate.split('T')[0] : '';
                document.getElementById('modal-sub-expiry').value = sub.expiryDate ? sub.expiryDate.split('T')[0] : '';
                document.getElementById('modal-sub-active').checked = sub.isActive !== false;
                document.getElementById('modal-revoke-sub-btn').classList.remove('hidden');
            } else {
                planSelect.value = '';
                document.getElementById('modal-sub-amount').value = '0';
                document.getElementById('modal-sub-start').value = new Date().toISOString().split('T')[0];
                const exp = new Date(); exp.setDate(exp.getDate() + 30);
                document.getElementById('modal-sub-expiry').value = exp.toISOString().split('T')[0];
                document.getElementById('modal-sub-active').checked = true;
                document.getElementById('modal-revoke-sub-btn').classList.add('hidden');
            }

            // Render drafts list inside modal
            const draftsCount = Array.isArray(resDrafts) ? resDrafts.length : 0;
            document.getElementById('modal-drafts-count').innerText = draftsCount;
            const draftsListDiv = document.getElementById('modal-drafts-list');
            if (draftsCount > 0) {
                draftsListDiv.innerHTML = resDrafts.map(d => {
                    const name = d.templateName || d.templateId || 'Untitled Template';
                    const date = d.updatedAt ? new Date(d.updatedAt).toLocaleDateString('en-IN') : '—';
                    return `
                        <div class="flex flex-col p-1.5 bg-white border border-gray-150 rounded-xl text-[10px] font-semibold text-wedding-charcoal-dark shadow-xs mb-1 last:mb-0">
                            <span class="font-extrabold truncate">${name}</span>
                            <span class="text-[8px] text-gray-400 font-mono mt-0.5">Saved: ${date}</span>
                        </div>
                    `;
                }).join('');
            } else {
                draftsListDiv.innerHTML = '<p class="text-[10px] text-gray-400 font-bold text-center py-4">No drafts</p>';
            }

            // Render cards/finalized list inside modal
            const cardsCount = Array.isArray(resCards) ? resCards.length : 0;
            document.getElementById('modal-cards-count').innerText = cardsCount;
            const cardsListDiv = document.getElementById('modal-cards-list');
            if (cardsCount > 0) {
                cardsListDiv.innerHTML = resCards.map(c => {
                    const name = c.templateName || c.templateId || 'Untitled Template';
                    const date = c.updatedAt ? new Date(c.updatedAt).toLocaleDateString('en-IN') : '—';
                    return `
                        <div class="flex flex-col p-1.5 bg-white border border-gray-150 rounded-xl text-[10px] font-semibold text-wedding-charcoal-dark shadow-xs mb-1 last:mb-0">
                            <span class="font-extrabold truncate">${name}</span>
                            <span class="text-[8px] text-gray-400 font-mono mt-0.5">Downloaded: ${date}</span>
                        </div>
                    `;
                }).join('');
            } else {
                cardsListDiv.innerHTML = '<p class="text-[10px] text-gray-400 font-bold text-center py-4">No completed designs</p>';
            }

            // Render rating feedback
            if (resRatings) {
                const starIcons = Array.from({ length: 5 }).map((_, i) => {
                    const active = i < Math.round(resRatings.rating);
                    return `<i data-lucide="star" class="w-3.5 h-3.5 ${active ? 'text-amber-500 fill-amber-500' : 'text-gray-300'}"></i>`;
                }).join('');
                ratingsListDiv.innerHTML = `
                    <div class="bg-gray-50 border border-gray-100 p-3 rounded-2xl space-y-2">
                        <div class="flex items-center gap-1.5">${starIcons} <span class="text-xs font-black">${resRatings.rating} / 5</span></div>
                        <p class="text-[11px] text-gray-500 font-semibold italic">Updated: ${new Date(resRatings.updatedAt).toLocaleString('en-IN')}</p>
                    </div>
                `;
            } else {
                ratingsListDiv.innerHTML = '<p class="text-xs text-gray-400 font-bold">This subscriber has not submitted reviews.</p>';
            }

            lucide.createIcons({ nodeList: [ratingsListDiv] });
            document.getElementById('sub-rating-modal').classList.remove('hidden');

        } catch (e) {
            console.error(e);
            Toast.show('Error loading subscription details.', 'error');
        }
    }

    function closeSubRatingModal() {
        document.getElementById('sub-rating-modal').classList.add('hidden');
    }

    function handleModalPlanChange(val) {
        const plan = modalPlansList.find(p => p.id === val);
        if (plan) {
            document.getElementById('modal-sub-amount').value = plan.price || 0;
            const startVal = document.getElementById('modal-sub-start').value || new Date().toISOString().split('T')[0];
            const start = new Date(startVal);
            const end = new Date(start);
            
            const durType = plan.durationType || 'monthly';
            const durDays = plan.durationDays || 30;

            if (durType === '1day') end.setDate(end.getDate() + 1);
            else if (durType === 'weekly') end.setDate(end.getDate() + 7);
            else if (durType === 'monthly') end.setMonth(end.getMonth() + 1);
            else if (durType === 'yearly') end.setFullYear(end.getFullYear() + 1);
            else end.setDate(end.getDate() + durDays);

            document.getElementById('modal-sub-expiry').value = end.toISOString().split('T')[0];
        }
    }

    async function handleSubSubmit(e) {
        e.preventDefault();
        if (!currentModalUser) return;

        const planType = document.getElementById('modal-sub-plan').value;
        if (!planType) {
            Toast.show('Please select a valid membership plan.', 'warning');
            return;
        }

        const payload = {
            userId: currentModalUser.id,
            planType,
            type: planType,
            startDate: new Date(document.getElementById('modal-sub-start').value).toISOString(),
            expiryDate: new Date(document.getElementById('modal-sub-expiry').value).toISOString(),
            isActive: document.getElementById('modal-sub-active').checked,
            amountPaid: Number(document.getElementById('modal-sub-amount').value) || 0
        };

        const hasSubRecord = !!currentModalUser.subscription;
        const endpoint = hasSubRecord
            ? `/api/user-subscriptions/${currentModalUser.subscription.id}`
            : `/api/user-subscriptions`;

        const method = hasSubRecord ? 'PUT' : 'POST';

        try {
            const res = await fetch(endpoint, {
                method,
                headers,
                body: JSON.stringify(payload)
            });

            if (res.ok) {
                closeSubRatingModal();
                fetchInitialDataSilent();
                Toast.show('User subscription settings saved!', 'success');
            } else {
                const err = await res.json();
                Toast.show(err.error || 'Failed to save subscription.', 'error');
            }
        } catch (error) {
            console.error(error);
            Toast.show('Error saving subscription.', 'error');
        }
    }

    async function handleRevokeSub() {
        if (!currentModalUser || !currentModalUser.subscription) return;
        if (!confirm('Are you sure you want to revoke this membership? This will immediately suspend their premium access.')) return;

        try {
            const res = await fetch(`/api/user-subscriptions/${currentModalUser.subscription.id}`, {
                method: 'DELETE',
                headers: { 'x-user-id': window.CurrentUser ? window.CurrentUser.id : 'admin_super' }
            });

            if (res.ok) {
                closeSubRatingModal();
                fetchInitialDataSilent();
                Toast.show('Subscription revoked successfully!', 'success');
            } else {
                const err = await res.json();
                Toast.show(err.error || 'Failed to revoke.', 'error');
            }
        } catch (error) {
            console.error(error);
            Toast.show('Error revoking membership.', 'error');
        }
    }
</script>
@endpush
