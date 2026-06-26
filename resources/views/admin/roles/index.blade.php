@extends('admin.layouts.app')

@section('title', 'Role & Permissions')
@section('header_title', 'Role & Permissions')

@php $activeTab = 'roles'; @endphp

@section('content')
<div class="space-y-8 animate-fadeIn">
    <!-- Upper header segment -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-extrabold text-wedding-charcoal-dark font-sans tracking-wide">
                ROLE & PERMISSIONS MODULE
            </h2>
            <p class="text-xs text-gray-500 font-semibold mt-1">
                Configure platform Access Control (RBAC), customize roles, and manage permissions matrices.
            </p>
        </div>
        
        <div id="create-role-btn-container">
            <!-- Rendered based on permission -->
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white p-6 rounded-[28px] border border-wedding-pink-medium/10 shadow-[0_8px_30px_rgba(0,0,0,0.02)] flex items-center justify-between transition-all">
            <div class="space-y-1">
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Total Active Roles</p>
                <h3 id="stat-total-roles" class="text-3xl font-black text-wedding-charcoal-dark">-</h3>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-indigo-50 flex items-center justify-center text-indigo-500 border border-indigo-100">
                <i data-lucide="shield" class="w-6 h-6"></i>
            </div>
        </div>

        <div class="bg-white p-6 rounded-[28px] border border-wedding-pink-medium/10 shadow-[0_8px_30px_rgba(0,0,0,0.02)] flex items-center justify-between transition-all">
            <div class="space-y-1">
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Platform Permissions</p>
                <h3 id="stat-total-perms" class="text-3xl font-black text-wedding-charcoal-dark">-</h3>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-sky-50 flex items-center justify-center text-sky-500 border border-sky-100">
                <i data-lucide="sliders" class="w-6 h-6"></i>
            </div>
        </div>

        <div class="bg-white p-6 rounded-[28px] border border-wedding-pink-medium/10 shadow-[0_8px_30px_rgba(0,0,0,0.02)] flex items-center justify-between transition-all">
            <div class="space-y-1">
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Total Staff Members</p>
                <h3 id="stat-total-users" class="text-3xl font-black text-wedding-charcoal-dark">-</h3>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 flex items-center justify-center text-emerald-500 border border-emerald-100">
                <i data-lucide="users" class="w-6 h-6"></i>
            </div>
        </div>

        <div class="bg-white p-6 rounded-[28px] border border-wedding-pink-medium/10 shadow-[0_8px_30px_rgba(0,0,0,0.02)] flex items-center justify-between transition-all">
            <div class="space-y-1">
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Inactive Roles</p>
                <h3 id="stat-inactive-roles" class="text-3xl font-black text-wedding-charcoal-dark">-</h3>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-rose-50 flex items-center justify-center text-rose-500 border border-rose-100">
                <i data-lucide="x-circle" class="w-6 h-6"></i>
            </div>
        </div>
    </div>

    <!-- Workspace Screen -->
    <div id="roles-loading" class="flex items-center justify-center min-h-[40vh]">
        <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-wedding-pink-dark"></div>
    </div>

    <div id="roles-workspace" class="grid grid-cols-1 lg:grid-cols-12 gap-8 hidden">
        <!-- Left Side: Roles Catalog -->
        <div class="lg:col-span-4 space-y-4">
            <div class="bg-white rounded-[28px] border border-wedding-pink-medium/10 p-5 shadow-[0_8px_30px_rgba(0,0,0,0.025)] space-y-4">
                <h4 class="text-xs font-bold text-wedding-charcoal-dark uppercase tracking-wider">
                    System Roles Catalog
                </h4>
                <div id="roles-list-container" class="space-y-2 max-h-[500px] overflow-y-auto pr-1">
                    <!-- Loaded dynamically -->
                </div>
            </div>
        </div>

        <!-- Right Side: Permissions Matrix Panel -->
        <div class="lg:col-span-8 space-y-6">
            <div class="bg-white rounded-[28px] border border-wedding-pink-medium/10 p-6 md:p-8 shadow-[0_8px_30px_rgba(0,0,0,0.025)]">
                <!-- Header Info -->
                <div class="flex flex-col sm:flex-row sm:items-start justify-between border-b border-gray-100 pb-5 mb-6 gap-4">
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 id="active-role-title" class="text-lg font-extrabold text-wedding-charcoal-dark uppercase">-</h3>
                            <span id="readonly-badge" class="px-2 py-0.5 bg-yellow-50 border border-yellow-200 text-yellow-700 text-[8px] font-bold rounded-full uppercase flex items-center gap-1 hidden">
                                <i data-lucide="lock" class="w-2.5 h-2.5"></i> Read-Only
                            </span>
                        </div>
                        <p class="text-xs text-gray-400 mt-0.5">
                            ID: <code id="active-role-id" class="font-mono text-gray-600 bg-gray-50 px-1 py-0.5 rounded">-</code>
                        </p>
                    </div>

                    <div id="safety-lock-tip" class="p-3.5 bg-yellow-50/50 border border-yellow-100 rounded-2xl text-[10px] text-yellow-800 max-w-xs leading-relaxed font-semibold hidden">
                        ⚡ <strong>System Safety Lock:</strong> The Super Admin role possesses the absolute root wildcard permission <code>*</code>. Its default configuration cannot be altered.
                    </div>
                </div>

                <form onsubmit="handleSaveRoleDetails(event)" class="space-y-6">
                    <!-- Grid Inputs -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block">Role Label Title</label>
                            <input
                                type="text"
                                id="role-form-name"
                                class="w-full px-4 py-3 bg-[#FFF5F6]/40 border border-[#FFCAD2]/60 rounded-xl text-wedding-charcoal-dark placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-wedding-pink-dark/25 focus:bg-white text-sm font-semibold transition-all disabled:opacity-60 disabled:bg-gray-50"
                                placeholder="Enter role title..."
                                required
                            />
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block">Status</label>
                            <div class="flex items-center mt-2.5">
                                <label class="relative inline-flex items-center cursor-pointer select-none">
                                    <input type="checkbox" id="role-form-active" class="sr-only peer" />
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-wedding-pink-dark"></div>
                                    <span class="ml-3 text-xs font-bold text-gray-500 uppercase">Active Status</span>
                                </label>
                            </div>
                        </div>

                        <div class="space-y-1.5 md:col-span-2">
                            <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block">Role Purpose Description</label>
                            <textarea
                                id="role-form-desc"
                                rows="2"
                                class="w-full px-4 py-3 bg-[#FFF5F6]/40 border border-[#FFCAD2]/60 rounded-xl text-wedding-charcoal-dark placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-wedding-pink-dark/25 focus:bg-white text-sm font-semibold transition-all disabled:opacity-60 disabled:bg-gray-50 resize-none"
                                placeholder="Describe what tasks users with this role can execute..."
                            ></textarea>
                        </div>
                    </div>

                    <!-- Permission Matrix Grid -->
                    <div class="space-y-4 pt-4 border-t border-gray-100">
                        <div class="flex items-center justify-between">
                            <h4 class="text-xs font-bold text-wedding-charcoal-dark uppercase tracking-wider flex items-center gap-2">
                                <i data-lucide="shield" class="w-4.5 h-4.5 text-wedding-pink-dark"></i>
                                Permission Mapping Grid
                            </h4>
                            <div id="select-actions-container" class="flex gap-2">
                                <button type="button" onclick="selectAllPerms(true)" class="text-[10px] text-wedding-pink-dark hover:underline font-bold">Select All</button>
                                <span class="text-gray-300">|</span>
                                <button type="button" onclick="selectAllPerms(false)" class="text-[10px] text-gray-500 hover:underline font-bold">Clear All</button>
                            </div>
                        </div>

                        <div id="permissions-matrix-container" class="space-y-5 max-h-[600px] overflow-y-auto pr-1">
                            <!-- Populated dynamically -->
                        </div>
                    </div>

                    <!-- Submit action -->
                    <div id="save-action-container" class="flex justify-end pt-4 border-t border-gray-100 hidden">
                        <button
                            type="submit"
                            id="role-save-btn"
                            class="flex items-center gap-2 px-6 py-3.5 bg-wedding-charcoal-dark hover:bg-wedding-charcoal-light text-wedding-gold-light hover:text-white text-xs font-bold rounded-2xl shadow-md transition-all"
                        >
                            <i data-lucide="save" class="w-4 h-4"></i> Save Role Permissions
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Create Custom Role Modal -->
    <div id="create-role-modal" class="fixed inset-0 bg-wedding-charcoal-dark/50 backdrop-blur-xs flex items-center justify-center p-4 z-50 hidden animate-fadeIn">
        <div class="bg-white rounded-[32px] border border-wedding-pink-medium/10 shadow-[0_25px_60px_-15px_rgba(0,0,0,0.15)] max-w-lg w-full p-6 md:p-8 space-y-6 relative animate-slideUp">
            <button
                type="button"
                onclick="closeCreateModal()"
                class="absolute top-5 right-5 p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-xl transition-all"
            >
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>

            <div class="space-y-1">
                <h3 class="text-lg font-black text-wedding-charcoal-dark uppercase">Create Custom Role</h3>
                <p class="text-xs text-gray-400 font-medium">Establish a new permission template and clone settings from existing system profiles.</p>
            </div>

            <form onsubmit="handleCreateRole(event)" class="space-y-5">
                <div class="space-y-1.5">
                    <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block">Role ID / Key *</label>
                    <input type="text" id="new-role-id" placeholder="e.g. support_staff" class="w-full px-4 py-3 bg-[#FFF5F6]/40 border border-[#FFCAD2]/60 rounded-xl text-wedding-charcoal-dark text-sm focus:outline-none focus:ring-2 focus:ring-wedding-pink-dark/25" required />
                </div>
                <div class="space-y-1.5">
                    <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block">Role Name *</label>
                    <input type="text" id="new-role-name" placeholder="e.g. Graphic Designer, Support Team" class="w-full px-4 py-3 bg-[#FFF5F6]/40 border border-[#FFCAD2]/60 rounded-xl text-wedding-charcoal-dark text-sm focus:outline-none focus:ring-2 focus:ring-wedding-pink-dark/25" required />
                </div>

                <div class="space-y-1.5">
                    <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block">Description</label>
                    <textarea id="new-role-desc" rows="2" placeholder="Briefly explain responsibilities of this custom role..." class="w-full px-4 py-3 bg-[#FFF5F6]/40 border border-[#FFCAD2]/60 rounded-xl text-wedding-charcoal-dark text-sm focus:outline-none focus:ring-2 focus:ring-wedding-pink-dark/25 resize-none"></textarea>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block">Clone Existing Role</label>
                        <select id="new-role-clone" class="w-full px-4 py-3 bg-[#FFF5F6]/40 border border-[#FFCAD2]/60 rounded-xl text-wedding-charcoal-dark focus:outline-none text-sm font-semibold">
                            <option value="">-- Empty Permissions --</option>
                        </select>
                    </div>

                    <div class="space-y-1.5 flex flex-col justify-end">
                        <div class="flex items-center mb-3">
                            <label class="relative inline-flex items-center cursor-pointer select-none">
                                <input type="checkbox" id="new-role-active" checked class="sr-only peer" />
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-wedding-pink-dark"></div>
                                <span class="ml-3 text-xs font-bold text-gray-500 uppercase">Active</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="pt-4 border-t border-gray-100 flex justify-end gap-3">
                    <button type="button" onclick="closeCreateModal()" class="px-5 py-3 rounded-2xl bg-gray-100 text-xs font-bold text-wedding-charcoal-light">Cancel</button>
                    <button type="submit" id="create-submit-btn" class="px-6 py-3 rounded-2xl bg-wedding-pink-dark text-white text-xs font-bold shadow-lg">Create Role</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let rolesList = [];
    let staffUsers = [];
    let selectedRoleId = 'super_admin';
    let currentPermissions = []; // active permissions list of selected role

    const headers = {
        'x-user-id': window.CurrentUser ? window.CurrentUser.id : 'admin_super',
        'Content-Type': 'application/json'
    };

    const PERMISSION_GROUPS = [
        {
            category: 'Dashboard',
            permissions: [
                { key: 'dashboard.view', label: 'View Dashboard Analytics', description: 'Allows viewing main admin dashboard charts and stats' }
            ]
        },
        {
            category: 'Templates',
            permissions: [
                { key: 'templates.view', label: 'View Templates List', description: 'Browse and preview invitation template designs' },
                { key: 'templates.create', label: 'Create New Template', description: 'Create and initialize a blank or upload template design' },
                { key: 'templates.edit', label: 'Edit Template Layout', description: 'Modify structure, pages, and objects in Canva editor' },
                { key: 'templates.delete', label: 'Delete Templates', description: 'Permanently remove template assets and records' },
                { key: 'templates.publish', label: 'Publish Templates', description: 'Make templates live for mobile/client application' },
                { key: 'templates.unpublish', label: 'Unpublish Templates', description: 'Temporarily hide templates from mobile application' },
                { key: 'templates.feature', label: 'Feature Templates', description: 'Mark templates as featured' }
            ]
        },
        {
            category: 'Categories',
            permissions: [
                { key: 'categories.view', label: 'View Categories', description: 'View invitation categories catalog' },
                { key: 'categories.create', label: 'Create Categories', description: 'Add new design categories' },
                { key: 'categories.edit', label: 'Edit Categories', description: 'Modify name, ordering, or thumbnail of categories' },
                { key: 'categories.delete', label: 'Delete Categories', description: 'Delete categories (only if they have no active templates)' }
            ]
        },
        {
            category: 'Typography & Fonts',
            permissions: [
                { key: 'fonts.view', label: 'View Custom Fonts', description: 'View custom typography styles registered' },
                { key: 'fonts.create', label: 'Upload Typography Files', description: 'Upload and register new .ttf font assets' },
                { key: 'fonts.edit', label: 'Edit Typography', description: 'Modify registered font options' },
                { key: 'fonts.delete', label: 'Delete Typography', description: 'Permanently delete font styles' }
            ]
        },
        {
            category: 'Languages',
            permissions: [
                { key: 'languages.view', label: 'View Languages', description: 'View platform translation languages' },
                { key: 'languages.create', label: 'Add Languages', description: 'Add new translation languages' },
                { key: 'languages.edit', label: 'Edit Languages', description: 'Enable/disable language status' },
                { key: 'languages.delete', label: 'Delete Languages', description: 'Remove platform language locales' }
            ]
        },
        {
            category: 'Subscription Tiers',
            permissions: [
                { key: 'subscriptions.view', label: 'View Subscriptions', description: 'View subscription plans and customer list' },
                { key: 'subscriptions.create', label: 'Add Subscription Plan', description: 'Create new pricing plans' },
                { key: 'subscriptions.edit', label: 'Edit Subscription Details', description: 'Modify price, tags, and category inclusion' },
                { key: 'subscriptions.delete', label: 'Delete Subscription Tier', description: 'Remove inactive/unused subscription plans' },
                { key: 'subscriptions.activate', label: 'Activate Plans', description: 'Enable plans to be sold' },
                { key: 'subscriptions.deactivate', label: 'Deactivate Plans', description: 'Temporarily hide plans from client storefront' },
                { key: 'subscriptions.manage_pricing', label: 'Manage Price Values', description: 'Change exact monetary prices' }
            ]
        },
        {
            category: 'User Accounts',
            permissions: [
                { key: 'users.view', label: 'View User Management', description: 'View list of admin users and registered customers' },
                { key: 'users.create', label: 'Create Admin User', description: 'Register new administrator accounts' },
                { key: 'users.edit', label: 'Edit User Accounts', description: 'Modify standard profile details' },
                { key: 'users.delete', label: 'Delete User Accounts', description: 'Permanently delete user profiles' },
                { key: 'users.suspend', label: 'Suspend User', description: 'Block user login capabilities' },
                { key: 'users.activate', label: 'Activate User', description: 'Restore suspended user accounts' },
                { key: 'users.assign_roles', label: 'Assign Roles', description: 'Modify system roles of user accounts' },
                { key: 'users.manage_permissions', label: 'Manage Custom Overrides', description: 'Define custom override permission tags per user' }
            ]
        },
        {
            category: 'Role & Permissions',
            permissions: [
                { key: 'roles.view', label: 'View Roles', description: 'View roles, permissions matrix, and audit trails' },
                { key: 'roles.create', label: 'Create Custom Roles', description: 'Define new roles' },
                { key: 'roles.edit', label: 'Edit Role Settings', description: 'Change role names, descriptions, and defaults' },
                { key: 'roles.delete', label: 'Delete Roles', description: 'Remove custom roles (only if no users are assigned)' },
                { key: 'roles.clone', label: 'Clone Roles', description: 'Clone permissions from a template role' },
                { key: 'roles.assign_permissions', label: 'Assign Role Permissions', description: 'Edit the permission mappings of roles' }
            ]
        },
        {
            category: 'System Config Settings',
            permissions: [
                { key: 'settings.view', label: 'View System Settings', description: 'View global application configurations' },
                { key: 'settings.edit', label: 'Edit System Settings', description: 'Modify support emails, app name, and maintenance states' }
            ]
        }
    ];

    const totalUniquePermissions = PERMISSION_GROUPS.reduce((acc, g) => acc + g.permissions.length, 0);

    function hasPermission(perm) {
        if (!window.CurrentUser) return false;
        const rId = window.CurrentUser.roleId || window.CurrentUser.role || 'viewer';
        if (rId === 'super_admin') return true;
        if (window.CurrentUser.permissions && window.CurrentUser.permissions.includes('*')) return true;
        return window.CurrentUser.permissions && window.CurrentUser.permissions.includes(perm);
    }

    document.addEventListener('DOMContentLoaded', () => {
        if (hasPermission('roles.create')) {
            document.getElementById('create-role-btn-container').innerHTML = `
                <button
                    onclick="openCreateModal()"
                    class="flex items-center gap-2 px-5 py-3 bg-gradient-to-r from-wedding-pink-dark to-[#ff6b81] hover:from-[#e62e47] hover:to-[#ff526e] text-white text-xs font-bold rounded-2xl shadow-md transition-all transform hover:-translate-y-0.5"
                >
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    Create Custom Role
                </button>
            `;
            lucide.createIcons({ nodeList: [document.getElementById('create-role-btn-container')] });
        }

        fetchData();
    });

    async function fetchData() {
        try {
            const [resRoles, resUsers] = await Promise.all([
                fetch(`/api/roles`, { headers }).then(r => r.json()),
                fetch(`/api/users`, { headers }).then(r => r.json())
            ]);

            rolesList = Array.isArray(resRoles) ? resRoles : [];
            staffUsers = Array.isArray(resUsers) ? resUsers : [];

            document.getElementById('roles-loading').classList.add('hidden');
            document.getElementById('roles-workspace').classList.remove('hidden');

            // Render stats
            document.getElementById('stat-total-roles').innerText = rolesList.length;
            document.getElementById('stat-total-perms').innerText = totalUniquePermissions;
            document.getElementById('stat-total-users').innerText = staffUsers.length;
            document.getElementById('stat-inactive-roles').innerText = rolesList.filter(r => r.isActive === false).length;

            // Populate Clone option select in modal
            const cloneSelect = document.getElementById('new-role-clone');
            cloneSelect.innerHTML = '<option value="">-- Empty Permissions --</option>';
            rolesList.forEach(r => {
                const opt = document.createElement('option');
                opt.value = r.id;
                opt.innerText = `Clone from: ${r.name}`;
                cloneSelect.appendChild(opt);
            });

            renderRolesList();
            loadRolePermissions(selectedRoleId);

        } catch (e) {
            console.error(e);
            Toast.show('Failed to fetch roles information.', 'error');
        }
    }

    function renderRolesList() {
        const listDiv = document.getElementById('roles-list-container');
        listDiv.innerHTML = '';

        rolesList.forEach(r => {
            const isSelected = r.id === selectedRoleId;
            const assignedCount = staffUsers.filter(u => u.roleId === r.id || u.role === r.id).length;

            const badgeSystem = r.isDefault 
                ? '<span class="px-1.5 py-0.5 bg-gray-100 text-gray-500 text-[8px] font-bold rounded-md uppercase">System</span>' 
                : '';
            const badgeInactive = r.isActive === false 
                ? '<span class="px-1.5 py-0.5 bg-red-50 text-red-500 text-[8px] font-bold rounded-md uppercase">Inactive</span>' 
                : '';

            let delBtnHTML = '';
            if (!r.isDefault && hasPermission('roles.delete')) {
                delBtnHTML = `
                    <button
                        onclick="event.stopPropagation(); handleDeleteRole('${r.id}', '${r.name}')"
                        class="p-2 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-xl transition-all opacity-0 group-hover:opacity-100 focus:opacity-100"
                        title="Delete Role"
                    >
                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                    </button>
                `;
            }

            const item = document.createElement('div');
            item.className = `group w-full flex items-center justify-between p-4 rounded-2xl cursor-pointer transition-all border text-left ${
                isSelected ? 'bg-[#FFF5F6] border-wedding-pink-medium/40 shadow-sm' : 'bg-white hover:bg-gray-50 border-gray-100'
            }`;
            item.onclick = () => selectRole(r.id);
            item.innerHTML = `
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-extrabold truncate ${isSelected ? 'text-wedding-pink-dark' : 'text-wedding-charcoal-dark'}">${r.name}</span>
                        ${badgeSystem}
                        ${badgeInactive}
                    </div>
                    <p class="text-[10px] text-gray-400 mt-1 truncate max-w-[180px]">${r.description || 'No description provided.'}</p>
                    <div class="flex items-center gap-1 text-[9px] font-semibold text-gray-400 mt-1">
                        <i data-lucide="users" class="w-3 h-3 text-gray-300"></i>
                        <span>${assignedCount} Assigned Staff</span>
                    </div>
                </div>
                ${delBtnHTML}
            `;
            listDiv.appendChild(item);
        });

        lucide.createIcons({ nodeList: [listDiv] });
    }

    function selectRole(roleId) {
        selectedRoleId = roleId;
        renderRolesList();
        loadRolePermissions(roleId);
    }

    function loadRolePermissions(roleId) {
        const role = rolesList.find(r => r.id === roleId);
        if (!role) return;

        document.getElementById('active-role-title').innerText = role.name;
        document.getElementById('active-role-id').innerText = role.id;

        const isSuperAdmin = role.id === 'super_admin';
        currentPermissions = role.permissions || [];

        // Setup fields
        document.getElementById('role-form-name').value = role.name;
        document.getElementById('role-form-name').disabled = isSuperAdmin || role.isDefault;
        document.getElementById('role-form-desc').value = role.description || '';
        document.getElementById('role-form-desc').disabled = isSuperAdmin;
        document.getElementById('role-form-active').checked = role.isActive !== false;
        document.getElementById('role-form-active').disabled = isSuperAdmin;

        // Show/hide locks
        if (isSuperAdmin) {
            document.getElementById('readonly-badge').classList.remove('hidden');
            document.getElementById('safety-lock-tip').classList.remove('hidden');
            document.getElementById('select-actions-container').classList.add('hidden');
            document.getElementById('save-action-container').classList.add('hidden');
        } else {
            document.getElementById('readonly-badge').classList.add('hidden');
            document.getElementById('safety-lock-tip').classList.add('hidden');
            
            if (hasPermission('roles.assign_permissions')) {
                document.getElementById('select-actions-container').classList.remove('hidden');
            } else {
                document.getElementById('select-actions-container').classList.add('hidden');
            }

            if (hasPermission('roles.edit')) {
                document.getElementById('save-action-container').classList.remove('hidden');
            } else {
                document.getElementById('save-action-container').classList.add('hidden');
            }
        }

        renderPermissionsMatrix(isSuperAdmin);
    }

    function renderPermissionsMatrix(isSuperAdmin) {
        const container = document.getElementById('permissions-matrix-container');
        container.innerHTML = '';

        PERMISSION_GROUPS.forEach(group => {
            const groupKeys = group.permissions.map(p => p.key);
            const activeGroupKeysCount = groupKeys.filter(k => currentPermissions.includes(k)).length;
            const isAllSelected = activeGroupKeysCount === groupKeys.length;

            let toggleHTML = '';
            if (!isSuperAdmin && hasPermission('roles.assign_permissions')) {
                toggleHTML = `
                    <label class="flex items-center gap-2 cursor-pointer select-none">
                        <input
                            type="checkbox"
                            ${isAllSelected ? 'checked' : ''}
                            onchange="toggleGroupPermissions('${group.category}', this.checked)"
                            class="w-3.5 h-3.5 rounded border-gray-300 text-wedding-pink-dark focus:ring-wedding-pink-dark/25"
                        />
                        <span class="text-[10px] font-bold text-gray-500 uppercase">Toggle Group</span>
                    </label>
                `;
            }

            let cardsHTML = '';
            group.permissions.forEach(perm => {
                const isChecked = isSuperAdmin || currentPermissions.includes(perm.key);
                const disabledAttr = isSuperAdmin || !hasPermission('roles.assign_permissions') ? 'disabled' : '';

                cardsHTML += `
                    <div
                        onclick="clickPermissionCard('${perm.key}', ${isSuperAdmin})"
                        class="flex items-start gap-3 p-3 rounded-xl border transition-all cursor-pointer ${
                            isChecked ? 'bg-emerald-50/25 border-emerald-100' : 'bg-white border-gray-100 hover:bg-gray-50'
                        }"
                    >
                        <input
                            type="checkbox"
                            id="chk-${perm.key}"
                            ${isChecked ? 'checked' : ''}
                            ${disabledAttr}
                            onclick="event.stopPropagation(); togglePermissionKey('${perm.key}')"
                            class="mt-0.5 w-4 h-4 rounded border-gray-300 text-wedding-pink-dark focus:ring-wedding-pink-dark/25"
                        />
                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-extrabold text-wedding-charcoal-dark leading-snug">${perm.label}</p>
                            <p class="text-[10px] text-gray-400 mt-0.5 leading-normal">${perm.description}</p>
                            <code class="inline-block text-[8px] text-gray-400 font-mono mt-1 bg-gray-50 px-1 py-0.5 rounded border border-gray-100">${perm.key}</code>
                        </div>
                    </div>
                `;
            });

            const groupDiv = document.createElement('div');
            groupDiv.className = 'border border-gray-100 rounded-2xl overflow-hidden shadow-xs bg-white';
            groupDiv.innerHTML = `
                <div class="bg-gray-50 px-4 py-3 flex items-center justify-between border-b border-gray-100">
                    <span class="text-xs font-extrabold text-wedding-charcoal-dark">${group.category}</span>
                    ${toggleHTML}
                </div>
                <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                    ${cardsHTML}
                </div>
            `;
            container.appendChild(groupDiv);
        });

        lucide.createIcons({ nodeList: [container] });
    }

    function clickPermissionCard(permKey, isSuperAdmin) {
        if (isSuperAdmin || !hasPermission('roles.assign_permissions')) return;
        togglePermissionKey(permKey);
    }

    function togglePermissionKey(permKey) {
        if (currentPermissions.includes(permKey)) {
            currentPermissions = currentPermissions.filter(k => k !== permKey);
        } else {
            currentPermissions.push(permKey);
        }
        renderPermissionsMatrix(false);
    }

    function toggleGroupPermissions(category, selectAll) {
        const group = PERMISSION_GROUPS.find(g => g.category === category);
        if (!group) return;

        const keys = group.permissions.map(p => p.key);
        currentPermissions = currentPermissions.filter(k => !keys.includes(k));
        
        if (selectAll) {
            currentPermissions = [...currentPermissions, ...keys];
        }
        renderPermissionsMatrix(false);
    }

    function selectAllPerms(selectAll) {
        if (selectedRoleId === 'super_admin' || !hasPermission('roles.assign_permissions')) return;
        if (selectAll) {
            currentPermissions = PERMISSION_GROUPS.flatMap(g => g.permissions.map(p => p.key));
        } else {
            currentPermissions = [];
        }
        renderPermissionsMatrix(false);
    }

    async function handleSaveRoleDetails(e) {
        e.preventDefault();
        if (selectedRoleId === 'super_admin') return;

        if (!hasPermission('roles.edit')) {
            Toast.show('Access Denied. You do not have permission to edit roles.', 'warning');
            return;
        }

        const btn = document.getElementById('role-save-btn');
        btn.disabled = true;
        btn.innerHTML = `<i data-lucide="refresh-cw" class="w-4 h-4 animate-spin"></i> Saving changes...`;
        lucide.createIcons({ nodeList: [btn] });

        const payload = {
            name: document.getElementById('role-form-name').value.trim(),
            description: document.getElementById('role-form-desc').value.trim(),
            permissions: currentPermissions,
            isActive: document.getElementById('role-form-active').checked
        };

        try {
            const res = await fetch(`/api/roles/${selectedRoleId}`, {
                method: 'PUT',
                headers,
                body: JSON.stringify(payload)
            });

            if (res.ok) {
                Toast.show('Role settings saved successfully.', 'success');
                fetchData();
            } else {
                const err = await res.json();
                Toast.show(err.error || 'Failed to save role settings.', 'error');
            }
        } catch (error) {
            console.error(error);
            Toast.show('Failed to save changes.', 'error');
        } finally {
            btn.disabled = false;
            btn.innerHTML = `<i data-lucide="save" class="w-4 h-4"></i> Save Role Permissions`;
            lucide.createIcons({ nodeList: [btn] });
        }
    }

    // CREATE ROLE MODAL
    function openCreateModal() {
        document.getElementById('new-role-id').value = '';
        document.getElementById('new-role-name').value = '';
        document.getElementById('new-role-desc').value = '';
        document.getElementById('new-role-clone').value = '';
        document.getElementById('new-role-active').checked = true;

        document.getElementById('create-role-modal').classList.remove('hidden');
    }

    function closeCreateModal() {
        document.getElementById('create-role-modal').classList.add('hidden');
    }

    async function handleCreateRole(e) {
        e.preventDefault();
        
        if (!hasPermission('roles.create')) {
            Toast.show('Access Denied. Missing roles.create permission.', 'warning');
            return;
        }

        const id = document.getElementById('new-role-id').value.trim().toLowerCase().replace(/[^a-z0-9_]/g, '_');
        const name = document.getElementById('new-role-name').value.trim();
        const description = document.getElementById('new-role-desc').value.trim();
        const cloneRoleId = document.getElementById('new-role-clone').value;
        const isActive = document.getElementById('new-role-active').checked;

        const payload = {
            id,
            name,
            description,
            isActive,
            cloneRoleId: cloneRoleId || undefined
        };

        const submitBtn = document.getElementById('create-submit-btn');
        submitBtn.disabled = true;
        submitBtn.innerText = 'Creating...';

        try {
            const res = await fetch(`/api/roles`, {
                method: 'POST',
                headers,
                body: JSON.stringify(payload)
            });

            if (res.ok) {
                const created = await res.json();
                closeCreateModal();
                selectedRoleId = created.id;
                fetchData();
                Toast.show(`Role "${created.name}" created successfully.`, 'success');
            } else {
                const err = await res.json();
                Toast.show(err.error || 'Failed to create role.', 'error');
            }
        } catch (error) {
            console.error(error);
            Toast.show('Failed to create role.', 'error');
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerText = 'Create Role';
        }
    }

    async function handleDeleteRole(roleId, roleName) {
        if (!hasPermission('roles.delete')) {
            Toast.show('Access Denied. Missing roles.delete permission.', 'warning');
            return;
        }

        if (!confirm(`Are you sure you want to delete the custom role "${roleName}"? This action is permanent.`)) return;

        try {
            const res = await fetch(`/api/roles/${roleId}`, {
                method: 'DELETE',
                headers: { 'x-user-id': window.CurrentUser ? window.CurrentUser.id : 'admin_super' }
            });

            if (res.ok) {
                Toast.show(`Role "${roleName}" has been deleted.`, 'success');
                selectedRoleId = 'super_admin';
                fetchData();
            } else {
                const err = await res.json();
                Toast.show(err.error || 'Failed to delete role.', 'error');
            }
        } catch (error) {
            console.error(error);
            Toast.show('Failed to delete role.', 'error');
        }
    }
</script>
@endpush
