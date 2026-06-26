@extends('admin.layouts.app')

@section('title', 'Supported Languages')
@section('header_title', 'Supported Languages')

@php $activeTab = 'languages'; @endphp

@section('content')
<div class="space-y-6">
    <!-- Header action bar -->
    <div class="flex justify-between items-center bg-wedding-card p-6 rounded-3xl border border-wedding-pink-medium/20 shadow-xs">
        <div>
            <h3 class="text-lg font-bold text-wedding-charcoal-dark tracking-tight">Supported Languages</h3>
            <p class="text-xs text-gray-500 font-semibold">Manage translation locales enabled for card templates</p>
        </div>
        <div id="add-btn-container">
            <!-- Dynamically populated based on permission -->
        </div>
    </div>

    <!-- Table Container -->
    <div id="languages-loading" class="flex flex-col items-center justify-center min-h-[40vh] gap-3">
        <div class="w-10 h-10 border-4 border-wedding-pink-medium border-t-wedding-pink-dark rounded-full animate-spin"></div>
        <p class="text-xs font-semibold text-wedding-pink-dark">Loading translation locales...</p>
    </div>

    <div id="languages-table-card" class="bg-wedding-card border border-wedding-pink-medium/20 rounded-3xl shadow-xs overflow-hidden hidden animate-fadeIn">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse min-w-[600px]">
                <thead>
                    <tr class="bg-wedding-pink-light/40 border-b border-wedding-pink-medium/20 text-wedding-charcoal-dark font-bold text-xs uppercase tracking-wider">
                        <th class="py-4 px-6">Language</th>
                        <th class="py-4 px-6">Locale Code</th>
                        <th class="py-4 px-6">Status</th>
                        <th class="py-4 px-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody id="languages-list" class="divide-y divide-wedding-pink-medium/15 text-sm text-wedding-charcoal-dark/95">
                    <!-- Dynamic Languages -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- Language Add Modal overlay -->
    <div id="language-modal" class="fixed inset-0 bg-wedding-charcoal-dark/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden animate-fadeIn">
        <div class="bg-wedding-bg border border-wedding-pink-medium/40 w-full max-w-sm rounded-3xl shadow-2xl overflow-hidden animate-slideUp">
            <div class="p-6 bg-wedding-charcoal-dark text-white flex justify-between items-center">
                <h4 class="font-bold text-lg text-wedding-gold-light">Add Supported Locale</h4>
                <button type="button" onclick="closeModal()" class="text-gray-400 hover:text-white font-bold text-sm bg-wedding-charcoal-light px-3 py-1.5 rounded-xl transition-colors">✕</button>
            </div>
            
            <form id="language-form" onsubmit="handleFormSubmit(event)" class="p-6 space-y-5">
                <!-- Select Language Preset -->
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-wedding-charcoal-light uppercase tracking-wider">Select Language Preset</label>
                    <select
                        id="preset-select"
                        onchange="handlePresetChange(this.value)"
                        class="w-full px-4 py-3 rounded-2xl bg-white border border-wedding-pink-medium/40 focus:ring-wedding-pink-dark/20 text-wedding-charcoal-dark text-sm focus:outline-none focus:ring-2 font-semibold"
                    >
                        <option value="-1">Select a language preset...</option>
                        <!-- Preset options populated dynamically -->
                    </select>
                </div>

                <!-- Language Name -->
                <div class="space-y-1.5 animate-fadeIn">
                    <label class="text-xs font-bold text-wedding-charcoal-light uppercase tracking-wider">Language Name</label>
                    <input 
                        type="text" 
                        id="language-name"
                        placeholder="e.g. Gujarati"
                        class="w-full px-4 py-3 rounded-2xl border text-wedding-charcoal-dark text-sm focus:outline-none focus:ring-2 bg-white border-wedding-pink-medium/40 focus:ring-wedding-pink-dark/20"
                        required
                    />
                    <p id="error-name" class="text-xs text-red-500 font-semibold mt-1 hidden"></p>
                </div>

                <!-- Code -->
                <div class="space-y-1.5 animate-fadeIn">
                    <label class="text-xs font-bold text-wedding-charcoal-light uppercase tracking-wider">Locale ISO Code</label>
                    <input 
                        type="text" 
                        id="language-code"
                        placeholder="e.g. gu"
                        class="w-full px-4 py-3 rounded-2xl border text-wedding-charcoal-dark text-sm focus:outline-none focus:ring-2 font-mono bg-white border-wedding-pink-medium/40 focus:ring-wedding-pink-dark/20"
                        required
                    />
                    <p id="error-code" class="text-xs text-red-500 font-semibold mt-1 hidden"></p>
                </div>

                <!-- Display State -->
                <div class="space-y-1.5 flex flex-col">
                    <label class="text-xs font-bold text-wedding-charcoal-light uppercase tracking-wider mb-2">Display State</label>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input 
                            type="checkbox" 
                            id="language-active"
                            checked
                            class="sr-only peer"
                        />
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-wedding-pink-dark"></div>
                        <span id="active-label" class="ml-3 text-sm font-semibold text-wedding-charcoal-dark">Active</span>
                    </label>
                </div>

                <!-- Submit Buttons -->
                <div class="pt-4 border-t border-wedding-pink-medium/20 flex justify-end gap-3">
                    <button
                        type="button"
                        onclick="closeModal()"
                        class="px-4 py-2.5 rounded-xl bg-gray-100 text-wedding-charcoal-light hover:bg-gray-200 text-xs font-bold transition-colors"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        class="px-5 py-2.5 rounded-xl bg-wedding-pink-dark hover:bg-wedding-pink-hover text-white text-xs font-bold shadow-lg transition-all"
                    >
                        Save Language
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let languagesList = [];
    const headers = {
        'x-user-id': window.CurrentUser ? window.CurrentUser.id : 'admin_super',
        'Content-Type': 'application/json'
    };

    const PRESET_LANGUAGES = [
        { name: 'Hindi', code: 'hi' },
        { name: 'Gujarati', code: 'gu' },
        { name: 'English', code: 'en' },
        { name: 'Marathi', code: 'mr' },
        { name: 'Tamil', code: 'ta' },
        { name: 'Telugu', code: 'te' },
        { name: 'Bengali', code: 'bn' },
        { name: 'Punjabi', code: 'pa' },
        { name: 'Kannada', code: 'kn' },
        { name: 'Malayalam', code: 'ml' },
        { name: 'Spanish', code: 'es' },
        { name: 'French', code: 'fr' },
        { name: 'German', code: 'de' },
        { name: 'Arabic', code: 'ar' }
    ].sort((a, b) => a.name.localeCompare(b.name));

    function hasPermission(perm) {
        if (!window.CurrentUser) return false;
        const rId = window.CurrentUser.roleId || window.CurrentUser.role || 'viewer';
        if (rId === 'super_admin') return true;
        if (window.CurrentUser.permissions && window.CurrentUser.permissions.includes('*')) return true;
        return window.CurrentUser.permissions && window.CurrentUser.permissions.includes(perm);
    }

    document.addEventListener('DOMContentLoaded', () => {
        if (hasPermission('languages.create')) {
            document.getElementById('add-btn-container').innerHTML = `
                <button
                    onclick="openAddModal()"
                    class="flex items-center gap-2 px-5 py-3 bg-wedding-pink-dark hover:bg-wedding-pink-hover text-white text-sm font-bold rounded-2xl shadow-lg transition-all duration-300 transform hover:-translate-y-0.5"
                >
                    <i data-lucide="plus-circle" class="w-5 h-5"></i>
                    Add Language
                </button>
            `;
            lucide.createIcons({ nodeList: [document.getElementById('add-btn-container')] });
        }

        document.getElementById('language-active').addEventListener('change', (e) => {
            document.getElementById('active-label').innerText = e.target.checked ? 'Active' : 'Disabled';
        });

        // Populate preset languages select dropdown
        const presetSelect = document.getElementById('preset-select');
        PRESET_LANGUAGES.forEach((lang, idx) => {
            const opt = document.createElement('option');
            opt.value = idx;
            opt.innerText = `${lang.name} (${lang.code.toUpperCase()})`;
            presetSelect.appendChild(opt);
        });
        const customOpt = document.createElement('option');
        customOpt.value = '-2';
        customOpt.innerText = 'Custom (Type manually...)';
        presetSelect.appendChild(customOpt);

        fetchLanguages();
    });

    async function fetchLanguages() {
        try {
            const res = await fetch(`/api/languages`, { headers });
            if (!res.ok) throw new Error('Failed to load languages');
            
            const data = await res.json();
            languagesList = Array.isArray(data) ? data : [];
            renderLanguages();
        } catch (err) {
            console.error(err);
            Toast.show('Failed to fetch languages.', 'error');
            document.getElementById('languages-loading').innerHTML = `
                <p class="text-sm font-bold text-red-500">Failed to load translation locales.</p>
            `;
        }
    }

    function renderLanguages() {
        document.getElementById('languages-loading').classList.add('hidden');
        document.getElementById('languages-table-card').classList.remove('hidden');

        const tbody = document.getElementById('languages-list');
        if (languagesList.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="4" class="text-center py-8 text-gray-400 font-bold">No translation languages found.</td>
                </tr>
            `;
            return;
        }

        let html = '';
        languagesList.forEach(lang => {
            const statusHTML = lang.isActive 
                ? `<span class="flex items-center gap-1 text-green-700 text-xs font-semibold">
                    <i data-lucide="check-circle-2" class="w-4 h-4 fill-green-100 text-green-700"></i> Enabled
                   </span>`
                : `<span class="flex items-center gap-1 text-gray-400 text-xs font-semibold">
                    <i data-lucide="x-circle" class="w-4 h-4 fill-gray-100 text-gray-400"></i> Disabled
                   </span>`;

            let actionsHTML = '';
            if (hasPermission('languages.delete')) {
                actionsHTML += `
                    <button
                        onclick="handleDelete('${lang.id}')"
                        class="p-2 text-red-500 hover:text-red-700 hover:bg-red-50 rounded-xl transition-all duration-200"
                        title="Delete Language"
                    >
                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                    </button>
                `;
            }

            html += `
                <tr class="hover:bg-wedding-pink-light/20 transition-colors">
                    <td class="py-4 px-6 font-bold text-wedding-charcoal-dark">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-lg bg-wedding-pink-light/30 flex items-center justify-center text-wedding-pink-dark">
                                <i data-lucide="globe" class="w-4 h-4"></i>
                            </div>
                            <span>${lang.name}</span>
                        </div>
                    </td>
                    <td class="py-4 px-6 text-gray-500 font-mono text-xs font-semibold uppercase">${lang.code}</td>
                    <td class="py-4 px-6">
                        <button onclick="handleToggle('${lang.id}', ${lang.isActive})" class="focus:outline-none">
                            ${statusHTML}
                        </button>
                    </td>
                    <td class="py-4 px-6 text-right">
                        <div class="flex justify-end gap-2">${actionsHTML}</div>
                    </td>
                </tr>
            `;
        });

        tbody.innerHTML = html;
        lucide.createIcons({ nodeList: [tbody] });
    }

    function handlePresetChange(val) {
        const idx = Number(val);
        const nameInput = document.getElementById('language-name');
        const codeInput = document.getElementById('language-code');

        if (idx >= 0) {
            nameInput.value = PRESET_LANGUAGES[idx].name;
            codeInput.value = PRESET_LANGUAGES[idx].code;
            nameInput.disabled = true;
            codeInput.disabled = true;
        } else if (idx === -1) {
            nameInput.value = '';
            codeInput.value = '';
            nameInput.disabled = false;
            codeInput.disabled = false;
        } else {
            nameInput.value = '';
            codeInput.value = '';
            nameInput.disabled = false;
            codeInput.disabled = false;
        }
        hideFormErrors();
    }

    function openAddModal() {
        document.getElementById('preset-select').value = '-1';
        document.getElementById('language-name').value = '';
        document.getElementById('language-name').disabled = false;
        document.getElementById('language-code').value = '';
        document.getElementById('language-code').disabled = false;
        document.getElementById('language-active').checked = true;
        document.getElementById('active-label').innerText = 'Active';

        hideFormErrors();
        document.getElementById('language-modal').classList.remove('hidden');
    }

    function closeModal() {
        document.getElementById('language-modal').classList.add('hidden');
    }

    function hideFormErrors() {
        document.getElementById('error-name').classList.add('hidden');
        document.getElementById('error-code').classList.add('hidden');
    }

    function validateForm() {
        hideFormErrors();
        let isValid = true;
        const name = document.getElementById('language-name').value.trim();
        const code = document.getElementById('language-code').value.trim().toLowerCase();

        if (!name) {
            document.getElementById('error-name').innerText = 'Language Name is required.';
            document.getElementById('error-name').classList.remove('hidden');
            isValid = false;
        }

        if (!code) {
            document.getElementById('error-code').innerText = 'ISO Code is required.';
            document.getElementById('error-code').classList.remove('hidden');
            isValid = false;
        } else if (!/^[a-z]{2,3}$/.test(code)) {
            document.getElementById('error-code').innerText = 'ISO Code must be 2 or 3 lowercase letters (e.g. "gu", "hi", "en").';
            document.getElementById('error-code').classList.remove('hidden');
            isValid = false;
        }

        return isValid;
    }

    async function handleFormSubmit(e) {
        e.preventDefault();
        if (!validateForm()) return;

        const payload = {
            name: document.getElementById('language-name').value.trim(),
            code: document.getElementById('language-code').value.trim().toLowerCase(),
            isActive: document.getElementById('language-active').checked
        };

        if (!hasPermission('languages.create')) {
            Toast.show('Access Denied. Missing languages.create permission.', 'warning');
            return;
        }

        try {
            const res = await fetch(`/api/languages`, {
                method: 'POST',
                headers,
                body: JSON.stringify(payload)
            });

            if (res.ok) {
                closeModal();
                fetchLanguages();
                Toast.show('Language locale registered successfully!', 'success');
            } else {
                const err = await res.json();
                Toast.show(err.error || 'Save failed', 'error');
            }
        } catch (error) {
            console.error('Submit error:', error);
            Toast.show('Failed to save language.', 'error');
        }
    }

    async function handleToggle(id, activeState) {
        if (!hasPermission('languages.edit')) {
            Toast.show('Access Denied. Missing languages.edit permission.', 'warning');
            return;
        }

        try {
            const res = await fetch(`/api/languages/${id}`, {
                method: 'PUT',
                headers,
                body: JSON.stringify({ isActive: !activeState })
            });
            if (res.ok) {
                fetchLanguages();
                Toast.show('Language status updated successfully!', 'success');
            } else {
                const err = await res.json();
                Toast.show(err.error || 'Failed to update status.', 'error');
            }
        } catch (error) {
            console.error('Toggle error:', error);
            Toast.show('Failed to update status.', 'error');
        }
    }

    async function handleDelete(id) {
        if (!hasPermission('languages.delete')) {
            Toast.show('Access Denied. Missing languages.delete permission.', 'warning');
            return;
        }
        if (!confirm('Are you sure you want to delete this language? Templates using this language will lose their translation metadata.')) return;

        try {
            const res = await fetch(`/api/languages/${id}`, {
                method: 'DELETE',
                headers: { 'x-user-id': window.CurrentUser ? window.CurrentUser.id : 'admin_super' }
            });
            if (res.ok) {
                fetchLanguages();
                Toast.show('Language deleted successfully!', 'success');
            } else {
                const err = await res.json();
                Toast.show(err.error || 'Failed to delete language.', 'error');
            }
        } catch (error) {
            console.error('Delete error:', error);
            Toast.show('Failed to delete language.', 'error');
        }
    }
</script>
@endpush
