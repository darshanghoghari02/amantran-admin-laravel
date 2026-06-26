@extends('admin.layouts.app')

@section('title', 'Settings')
@section('header_title', 'System Settings')

@php $activeTab = 'settings'; @endphp

@section('content')
<div class="max-w-4xl space-y-6 animate-fadeIn">
    <!-- Upper header section -->
    <div>
        <h2 class="text-2xl font-extrabold text-wedding-charcoal-dark font-sans tracking-wide">
            SYSTEM CONFIGURATION SETTINGS
        </h2>
        <p class="text-xs text-gray-500 font-semibold mt-1">
            Adjust global parameters, contact configurations, registration policies, and maintenance statuses.
        </p>
    </div>

    <!-- Settings Loading -->
    <div id="settings-loading" class="flex items-center justify-center min-h-[40vh]">
        <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-wedding-pink-dark"></div>
    </div>

    <form id="settings-form" onsubmit="handleSaveSettings(event)" class="space-y-6 hidden">
        <!-- Brand Identity Panel -->
        <div class="bg-white rounded-[28px] border border-wedding-pink-medium/10 p-6 md:p-8 shadow-[0_8px_30px_rgba(0,0,0,0.02)] space-y-6">
            <div class="flex items-center gap-3 border-b border-gray-100 pb-4">
                <div class="p-2.5 bg-rose-50 text-wedding-pink-dark rounded-xl border border-rose-100">
                    <i data-lucide="app-window" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="text-sm font-extrabold text-wedding-charcoal-dark uppercase tracking-wider">
                        Application Brand Settings
                    </h3>
                    <p class="text-[10px] text-gray-400 font-semibold mt-0.5">
                        Configure default labels and support communication details.
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- App name -->
                <div class="space-y-1.5">
                    <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block">Application Brand Title</label>
                    <input
                        type="text"
                        id="config-appname"
                        class="w-full px-4 py-3 bg-[#FFF5F6]/40 border border-[#FFCAD2]/60 rounded-xl text-wedding-charcoal-dark placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-wedding-pink-dark/25 focus:bg-white text-sm font-semibold transition-all disabled:opacity-60 disabled:bg-gray-50"
                        placeholder="e.g. Amantran CMS"
                        required
                    />
                </div>

                <!-- Support Email -->
                <div class="space-y-1.5">
                    <div class="flex items-center gap-1.5">
                        <i data-lucide="mail" class="w-3.5 h-3.5 text-gray-400"></i>
                        <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block">Support Contact Email</label>
                    </div>
                    <input
                        type="email"
                        id="config-supportemail"
                        class="w-full px-4 py-3 bg-[#FFF5F6]/40 border border-[#FFCAD2]/60 rounded-xl text-wedding-charcoal-dark placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-wedding-pink-dark/25 focus:bg-white text-sm font-semibold transition-all disabled:opacity-60 disabled:bg-gray-50"
                        placeholder="e.g. support@domain.com"
                        required
                    />
                </div>
            </div>
        </div>

        <!-- User Policies Panel -->
        <div class="bg-white rounded-[28px] border border-wedding-pink-medium/10 p-6 md:p-8 shadow-[0_8px_30px_rgba(0,0,0,0.02)] space-y-6">
            <div class="flex items-center gap-3 border-b border-gray-100 pb-4">
                <div class="p-2.5 bg-indigo-50 text-indigo-500 rounded-xl border border-indigo-100">
                    <i data-lucide="user-plus" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="text-sm font-extrabold text-wedding-charcoal-dark uppercase tracking-wider">
                        Registration & Access Policies
                    </h3>
                    <p class="text-[10px] text-gray-400 font-semibold mt-0.5">
                        Regulate self-service enrollment parameters and initial privileges.
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Default role -->
                <div class="space-y-1.5">
                    <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block">Default New User Role</label>
                    <select
                        id="config-defaultrole"
                        class="w-full px-4 py-3 bg-[#FFF5F6]/40 border border-[#FFCAD2]/60 rounded-xl text-wedding-charcoal-dark focus:outline-none focus:ring-2 focus:ring-wedding-pink-dark/25 focus:bg-white text-sm font-semibold transition-all disabled:opacity-60 disabled:bg-gray-50 cursor-pointer"
                    >
                        <!-- Populated dynamically -->
                    </select>
                    <p class="text-[9px] text-gray-400 font-semibold leading-relaxed mt-1">
                        Choose the baseline privileges assigned automatically when custom users self-register.
                    </p>
                </div>

                <!-- Self registration -->
                <div class="space-y-1.5">
                    <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block">Allow Self-Registration</label>
                    <div class="flex items-center mt-2.5">
                        <label class="relative inline-flex items-center cursor-pointer select-none">
                            <input type="checkbox" id="config-allowselfreg" class="sr-only peer" />
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-wedding-pink-dark"></div>
                            <span id="selfreg-label" class="ml-3 text-xs font-bold text-gray-500 uppercase">Public Enrollments Enabled</span>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <!-- Emergency Maintenance Panel -->
        <div class="bg-white rounded-[28px] border border-wedding-pink-medium/10 p-6 md:p-8 shadow-[0_8px_30px_rgba(0,0,0,0.02)] space-y-6">
            <div class="flex items-center gap-3 border-b border-gray-100 pb-4">
                <div class="p-2.5 bg-amber-50 text-amber-500 rounded-xl border border-amber-100">
                    <i data-lucide="shield-alert" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="text-sm font-extrabold text-wedding-charcoal-dark uppercase tracking-wider">
                        Emergency & System Maintenance
                    </h3>
                    <p class="text-[10px] text-gray-400 font-semibold mt-0.5">
                        Activate temporary read-only lockouts for scheduled database migrations.
                    </p>
                </div>
            </div>

            <div class="flex items-start gap-4">
                <div class="mt-1">
                    <label class="relative inline-flex items-center cursor-pointer select-none">
                        <input type="checkbox" id="config-maintenance" class="sr-only peer" />
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-red-500"></div>
                    </label>
                </div>
                <div class="space-y-1">
                    <h4 id="maintenance-status-title" class="text-xs font-bold text-wedding-charcoal-dark uppercase">🟢 Platform Online & Operating</h4>
                    <p class="text-[10px] text-gray-400 font-medium leading-relaxed max-w-xl">
                        When active, all mobile customer client actions (invitation drafting, purchase processing) will display a maintenance message. Admin control panels remain accessible to authorized managers.
                    </p>
                </div>
            </div>
        </div>

        <!-- Submit Button -->
        <div id="save-btn-container" class="flex justify-end hidden">
            <button
                type="submit"
                id="save-submit-btn"
                class="flex items-center gap-2 px-6 py-3.5 bg-wedding-charcoal-dark hover:bg-wedding-charcoal-light text-wedding-gold-light hover:text-white text-xs font-bold rounded-2xl shadow-md transition-all"
            >
                <i data-lucide="save" class="w-4 h-4"></i> Save Configuration Changes
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    let currentConfig = null;
    let rolesList = [];
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
        document.getElementById('config-allowselfreg').addEventListener('change', (e) => {
            document.getElementById('selfreg-label').innerText = e.target.checked ? 'Public Enrollments Enabled' : 'Registrations Restricted';
        });

        document.getElementById('config-maintenance').addEventListener('change', (e) => {
            document.getElementById('maintenance-status-title').innerText = e.target.checked ? '⚡ Platform Lockout Active' : '🟢 Platform Online & Operating';
        });

        loadSettingsData();
    });

    async function loadSettingsData() {
        try {
            const [resConfig, resRoles] = await Promise.all([
                fetch(`/api/settings`, { headers }).then(r => r.json()),
                fetch(`/api/roles`, { headers }).then(r => r.json())
            ]);

            currentConfig = resConfig;
            rolesList = Array.isArray(resRoles) ? resRoles : [];

            // Populate default roles select
            const dSelect = document.getElementById('config-defaultrole');
            dSelect.innerHTML = '';
            rolesList.forEach(r => {
                const opt = document.createElement('option');
                opt.value = r.id;
                opt.innerText = r.name + (r.isDefault ? ' (System)' : '');
                dSelect.appendChild(opt);
            });

            // Populate form values
            if (currentConfig) {
                document.getElementById('config-appname').value = currentConfig.appName || '';
                document.getElementById('config-supportemail').value = currentConfig.supportEmail || '';
                document.getElementById('config-defaultrole').value = currentConfig.defaultUserRole || 'user';
                
                const selfReg = currentConfig.allowSelfRegistration !== false;
                document.getElementById('config-allowselfreg').checked = selfReg;
                document.getElementById('selfreg-label').innerText = selfReg ? 'Public Enrollments Enabled' : 'Registrations Restricted';

                const maintenance = currentConfig.maintenanceMode === true;
                document.getElementById('config-maintenance').checked = maintenance;
                document.getElementById('maintenance-status-title').innerText = maintenance ? '⚡ Platform Lockout Active' : '🟢 Platform Online & Operating';
            }

            // Lock values if not editable
            const isEditable = hasPermission('settings.edit');
            document.getElementById('config-appname').disabled = !isEditable;
            document.getElementById('config-supportemail').disabled = !isEditable;
            document.getElementById('config-defaultrole').disabled = !isEditable;
            document.getElementById('config-allowselfreg').disabled = !isEditable;
            document.getElementById('config-maintenance').disabled = !isEditable;

            if (isEditable) {
                document.getElementById('save-btn-container').classList.remove('hidden');
            }

            document.getElementById('settings-loading').classList.add('hidden');
            document.getElementById('settings-form').classList.remove('hidden');

            lucide.createIcons();

        } catch (e) {
            console.error(e);
            Toast.show('Error loading global settings.', 'error');
            document.getElementById('settings-loading').innerHTML = `
                <p class="text-sm font-bold text-red-500">Failed to load platform settings.</p>
            `;
        }
    }

    async function handleSaveSettings(e) {
        e.preventDefault();
        
        if (!hasPermission('settings.edit')) {
            Toast.show('Access Denied. You lack settings.edit permission.', 'warning');
            return;
        }

        const payload = {
            appName: document.getElementById('config-appname').value.trim(),
            supportEmail: document.getElementById('config-supportemail').value.trim(),
            defaultUserRole: document.getElementById('config-defaultrole').value,
            allowSelfRegistration: document.getElementById('config-allowselfreg').checked,
            maintenanceMode: document.getElementById('config-maintenance').checked
        };

        const submitBtn = document.getElementById('save-submit-btn');
        submitBtn.disabled = true;
        submitBtn.innerHTML = `<i data-lucide="refresh-cw" class="w-4 h-4 animate-spin"></i> Saving changes...`;
        lucide.createIcons({ nodeList: [submitBtn] });

        try {
            const res = await fetch(`/api/settings`, {
                method: 'PUT',
                headers,
                body: JSON.stringify(payload)
            });

            if (res.ok) {
                Toast.show('System settings updated successfully.', 'success');
                loadSettingsData();
            } else {
                const err = await res.json();
                Toast.show(err.error || 'Failed to update system settings.', 'error');
            }
        } catch (error) {
            console.error(error);
            Toast.show('Failed to update system settings.', 'error');
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = `<i data-lucide="save" class="w-4 h-4"></i> Save Configuration Changes`;
            lucide.createIcons({ nodeList: [submitBtn] });
        }
    }
</script>
@endpush
