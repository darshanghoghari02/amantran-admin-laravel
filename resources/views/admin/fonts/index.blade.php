@extends('admin.layouts.app')

@section('title', 'Typography & Fonts')
@section('header_title', 'Typography & Fonts')

@php $activeTab = 'fonts'; @endphp

@section('content')
<div class="space-y-6">
    <!-- Header action bar -->
    <div class="flex justify-between items-center bg-wedding-card p-6 rounded-3xl border border-wedding-pink-medium/20 shadow-xs">
        <div>
            <h3 class="text-lg font-bold text-wedding-charcoal-dark tracking-tight">Typography & Fonts</h3>
            <p class="text-xs text-gray-500 font-semibold">Upload wedding typography binaries (.ttf/.otf) and register layout families</p>
        </div>
        <div id="add-btn-container">
            <!-- Dynamically populated based on permission -->
        </div>
    </div>

    <!-- Table Container -->
    <div id="fonts-loading" class="flex flex-col items-center justify-center min-h-[40vh] gap-3">
        <div class="w-10 h-10 border-4 border-wedding-pink-medium border-t-wedding-pink-dark rounded-full animate-spin"></div>
        <p class="text-xs font-semibold text-wedding-pink-dark">Loading your typographies...</p>
    </div>

    <div id="fonts-table-card" class="bg-wedding-card border border-wedding-pink-medium/20 rounded-3xl shadow-xs overflow-hidden hidden animate-fadeIn">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse min-w-[800px]">
                <thead>
                    <tr class="bg-wedding-pink-light/40 border-b border-wedding-pink-medium/20 text-wedding-charcoal-dark font-bold text-xs uppercase tracking-wider">
                        <th class="py-4 px-6">Font Family</th>
                        <th class="py-4 px-6">Live Specimen Preview</th>
                        <th class="py-4 px-6">Flutter Asset Destination</th>
                        <th class="py-4 px-6">Status</th>
                        <th class="py-4 px-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody id="fonts-list" class="divide-y divide-wedding-pink-medium/15 text-sm text-wedding-charcoal-dark/95">
                    <!-- Dynamic Fonts -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- Font Upload overlay Modal -->
    <div id="font-modal" class="fixed inset-0 bg-wedding-charcoal-dark/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden animate-fadeIn">
        <div class="bg-wedding-bg border border-wedding-pink-medium/40 w-full max-w-lg rounded-3xl shadow-2xl overflow-hidden animate-slideUp">
            <div class="p-6 bg-wedding-charcoal-dark text-white flex justify-between items-center">
                <h4 class="font-bold text-lg text-wedding-gold-light">Upload Custom Invitation Font</h4>
                <button type="button" onclick="closeModal()" class="text-gray-400 hover:text-white font-bold text-sm bg-wedding-charcoal-light px-3 py-1.5 rounded-xl transition-colors">✕</button>
            </div>
            
            <form id="font-form" onsubmit="handleFormSubmit(event)" class="p-6 space-y-5">
                <!-- Font file uploader -->
                <div class="space-y-2">
                    <label class="text-xs font-bold text-wedding-charcoal-light uppercase tracking-wider block">Font binary (.ttf / .otf)</label>
                    
                    <label id="dropzone" class="border-2 border-dashed border-wedding-pink-medium/40 hover:bg-wedding-pink-light/10 cursor-pointer p-8 rounded-2xl flex flex-col items-center justify-center transition-all">
                        <i data-lucide="upload" class="w-8 h-8 text-wedding-pink-dark mb-2"></i>
                        <span id="upload-text" class="text-sm font-bold text-wedding-charcoal-dark text-center">Click to Upload TTF/OTF File</span>
                        <span class="text-[10px] text-gray-500 mt-1">Supports TTF or OTF typography formats</span>
                        <input 
                            type="file" 
                            id="file-input"
                            accept=".ttf,.otf"
                            onchange="handleFileUpload(event)"
                            class="hidden" 
                        />
                    </label>
                    <p id="error-file" class="text-xs text-red-500 font-semibold mt-1 hidden"></p>
                </div>

                <!-- Font Family Name -->
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-wedding-charcoal-light uppercase tracking-wider">Font Family Name</label>
                    <input 
                        type="text" 
                        id="font-family"
                        placeholder="e.g. Hind Vadodara"
                        class="w-full px-4 py-3 rounded-2xl bg-white border border-wedding-pink-medium/40 focus:ring-2 focus:ring-wedding-pink-dark/20 focus:outline-none text-wedding-charcoal-dark text-sm"
                        required
                    />
                    <p id="error-family" class="text-xs text-red-500 font-semibold mt-1 hidden"></p>
                </div>

                <!-- Flutter Path (Automatic) -->
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-wedding-charcoal-light uppercase tracking-wider">Saved Asset Path (automatic)</label>
                    <input 
                        type="text" 
                        id="font-path"
                        readonly
                        placeholder="assets/fonts/font_name.ttf"
                        class="w-full px-4 py-3 rounded-2xl bg-gray-50 border border-wedding-pink-medium/30 text-wedding-charcoal-dark/70 text-sm font-mono focus:outline-none"
                    />
                    <p class="text-[10px] text-gray-500 font-medium leading-relaxed">
                        * Dynamic target: <code class="font-mono bg-wedding-pink-light/45 px-1 py-0.5 text-wedding-pink-dark rounded">assets/fonts/</code>. Flutter will map to this exact asset tree.
                    </p>
                </div>

                <!-- Display State -->
                <div class="space-y-1.5 flex flex-col">
                    <label class="text-xs font-bold text-wedding-charcoal-light uppercase tracking-wider mb-2">Display State</label>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input 
                            type="checkbox" 
                            id="font-active"
                            checked
                            class="sr-only peer"
                        />
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-wedding-pink-dark"></div>
                        <span id="active-label" class="ml-3 text-sm font-semibold text-wedding-charcoal-dark">Active & Usable</span>
                    </label>
                </div>

                <!-- Submit Buttons -->
                <div class="pt-4 border-t border-wedding-pink-medium/20 flex justify-end gap-3">
                    <button
                        type="button"
                        onclick="closeModal()"
                        class="px-5 py-3 rounded-2xl bg-gray-100 text-wedding-charcoal-light hover:bg-gray-200 text-sm font-bold transition-colors"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        id="save-btn"
                        disabled
                        class="px-6 py-3 rounded-2xl bg-wedding-pink-dark hover:bg-wedding-pink-hover text-white text-sm font-bold shadow-lg transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        Save Font
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let fontsList = [];
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
        if (hasPermission('fonts.create')) {
            document.getElementById('add-btn-container').innerHTML = `
                <button
                    onclick="openAddModal()"
                    class="flex items-center gap-2 px-5 py-3 bg-wedding-pink-dark hover:bg-wedding-pink-hover text-white text-sm font-bold rounded-2xl shadow-lg transition-all duration-300 transform hover:-translate-y-0.5"
                >
                    <i data-lucide="plus-circle" class="w-5 h-5"></i>
                    Upload Font Asset
                </button>
            `;
            lucide.createIcons({ nodeList: [document.getElementById('add-btn-container')] });
        }

        document.getElementById('font-active').addEventListener('change', (e) => {
            document.getElementById('active-label').innerText = e.target.checked ? 'Active & Usable' : 'Disabled';
        });

        fetchFonts();
    });

    async function fetchFonts() {
        try {
            const res = await fetch(`/api/fonts`, { headers });
            if (!res.ok) throw new Error('Failed to load fonts');
            
            const data = await res.json();
            fontsList = Array.isArray(data) ? data : [];
            
            injectCustomFontsInPage();
            renderFonts();
        } catch (err) {
            console.error(err);
            Toast.show('Failed to fetch fonts.', 'error');
            document.getElementById('fonts-loading').innerHTML = `
                <p class="text-sm font-bold text-red-500">Failed to load fonts list.</p>
            `;
        }
    }

    function injectCustomFontsInPage() {
        try {
            const activeFonts = fontsList.filter(f => f.isActive);
            let styleContent = '';
            activeFonts.forEach(f => {
                styleContent += `
                    @font-face {
                        font-family: '${f.family}';
                        src: url('${f.localPath}') format('truetype');
                        font-weight: normal;
                        font-style: normal;
                        font-display: swap;
                    }
                `;
            });
            const id = 'dynamic-custom-fonts';
            let existingStyle = document.getElementById(id);
            if (existingStyle) {
                existingStyle.textContent = styleContent;
            } else {
                const style = document.createElement('style');
                style.id = id;
                style.textContent = styleContent;
                document.head.appendChild(style);
            }
        } catch (e) {
            console.error('Failed to dynamically load custom fonts into browser:', e);
        }
    }

    function renderFonts() {
        document.getElementById('fonts-loading').classList.add('hidden');
        document.getElementById('fonts-table-card').classList.remove('hidden');

        const tbody = document.getElementById('fonts-list');
        if (fontsList.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="5" class="text-center py-8 text-gray-400 font-bold">No custom fonts registered.</td>
                </tr>
            `;
            return;
        }

        let html = '';
        fontsList.forEach(f => {
            const statusHTML = f.isActive 
                ? `<span class="flex items-center gap-1 text-green-700 text-xs font-semibold">
                    <i data-lucide="check-circle-2" class="w-4 h-4 fill-green-100 text-green-700"></i> Enabled
                   </span>`
                : `<span class="flex items-center gap-1 text-gray-400 text-xs font-semibold">
                    <i data-lucide="x-circle" class="w-4 h-4 fill-gray-100 text-gray-400"></i> Disabled
                   </span>`;

            let actionsHTML = '';
            if (hasPermission('fonts.delete')) {
                actionsHTML += `
                    <button
                        onclick="handleDelete('${f.id}')"
                        class="p-2 text-red-500 hover:text-red-700 hover:bg-red-50 rounded-xl transition-all duration-200"
                        title="Delete Font Record"
                    >
                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                    </button>
                `;
            }

            html += `
                <tr class="hover:bg-wedding-pink-light/20 transition-colors">
                    <td class="py-4 px-6">
                        <div class="flex items-center gap-2">
                            <div class="p-2 bg-wedding-pink-light/30 rounded-lg text-wedding-pink-dark">
                                <i data-lucide="type" class="w-4 h-4"></i>
                            </div>
                            <span class="font-bold text-wedding-charcoal-dark">${f.family}</span>
                        </div>
                    </td>
                    <td class="py-4 px-6">
                        <span style="font-family: '${f.family}', sans-serif" class="text-lg text-wedding-charcoal-dark">
                            Aarav weds Ananya | 18.12.2026
                        </span>
                    </td>
                    <td class="py-4 px-6 text-gray-500 font-mono text-xs">${f.localPath}</td>
                    <td class="py-4 px-6">
                        <button onclick="handleToggle('${f.id}', ${f.isActive})" class="flex items-center gap-1.5 focus:outline-none">
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

    function openAddModal() {
        document.getElementById('font-family').value = '';
        document.getElementById('font-path').value = '';
        document.getElementById('font-active').checked = true;
        document.getElementById('active-label').innerText = 'Active & Usable';
        document.getElementById('save-btn').disabled = true;
        document.getElementById('file-input').value = '';
        document.getElementById('upload-text').innerText = 'Click to Upload TTF/OTF File';

        hideFormErrors();
        document.getElementById('font-modal').classList.remove('hidden');
    }

    function closeModal() {
        document.getElementById('font-modal').classList.add('hidden');
    }

    function hideFormErrors() {
        document.getElementById('error-file').classList.add('hidden');
        document.getElementById('error-family').classList.add('hidden');
    }

    async function handleFileUpload(e) {
        const fileInput = e.target;
        if (!fileInput.files || fileInput.files.length === 0) return;
        const file = fileInput.files[0];
        
        document.getElementById('upload-text').innerText = 'Uploading font binary...';
        document.getElementById('error-file').classList.add('hidden');
        
        const formData = new FormData();
        formData.append('file', file);

        try {
            const res = await fetch(`/api/uploads/single?type=fonts`, {
                method: 'POST',
                headers: {
                    'x-user-id': window.CurrentUser ? window.CurrentUser.id : 'admin_super'
                },
                body: formData
            });
            const data = await res.json();
            if (data.success) {
                document.getElementById('font-path').value = data.filePath;
                document.getElementById('upload-text').innerText = `Uploaded: ${file.name}`;
                
                // Prefill font family name if empty
                const famInput = document.getElementById('font-family');
                if (!famInput.value.trim()) {
                    const cleanName = file.name.replace(/\.[^/.]+$/, "").replace(/[-_]/g, ' ');
                    famInput.value = cleanName;
                }
                
                document.getElementById('save-btn').disabled = false;
                Toast.show('Font binary uploaded successfully!', 'success');
            } else {
                Toast.show(data.error || 'Upload failed', 'error');
                document.getElementById('upload-text').innerText = 'Click to Upload TTF/OTF File';
            }
        } catch (err) {
            console.error('Upload error:', err);
            Toast.show('Failed to upload font file.', 'error');
            document.getElementById('upload-text').innerText = 'Click to Upload TTF/OTF File';
        }
    }

    async function handleFormSubmit(e) {
        e.preventDefault();
        hideFormErrors();

        const family = document.getElementById('font-family').value.trim();
        const localPath = document.getElementById('font-path').value;
        const isActive = document.getElementById('font-active').checked;

        if (!family) {
            document.getElementById('error-family').innerText = 'Font Family Name is required.';
            document.getElementById('error-family').classList.remove('hidden');
            return;
        }

        if (!localPath) {
            document.getElementById('error-file').innerText = 'Font binary file upload is required.';
            document.getElementById('error-file').classList.remove('hidden');
            return;
        }

        if (!hasPermission('fonts.create')) {
            Toast.show('Access Denied. You lack the "fonts.create" permission.', 'warning');
            return;
        }

        const payload = { family, localPath, isActive };

        try {
            const res = await fetch(`/api/fonts`, {
                method: 'POST',
                headers,
                body: JSON.stringify(payload)
            });

            if (res.ok) {
                closeModal();
                fetchFonts();
                Toast.show('Font registered successfully!', 'success');
            } else {
                const err = await res.json();
                Toast.show(err.error || 'Save failed', 'error');
            }
        } catch (error) {
            console.error('Submit font error:', error);
            Toast.show('Failed to register font.', 'error');
        }
    }

    async function handleToggle(id, activeState) {
        if (!hasPermission('fonts.edit')) {
            Toast.show('Access Denied. You lack the "fonts.edit" permission.', 'warning');
            return;
        }

        try {
            const res = await fetch(`/api/fonts/${id}`, {
                method: 'PUT',
                headers,
                body: JSON.stringify({ isActive: !activeState })
            });
            if (res.ok) {
                fetchFonts();
                Toast.show('Font status updated successfully!', 'success');
            } else {
                const err = await res.json();
                Toast.show(err.error || 'Failed to update status.', 'error');
            }
        } catch (error) {
            console.error('Toggle status error:', error);
            Toast.show('Failed to update status.', 'error');
        }
    }

    async function handleDelete(id) {
        if (!hasPermission('fonts.delete')) {
            Toast.show('Access Denied. You lack the "fonts.delete" permission.', 'warning');
            return;
        }
        if (!confirm('Are you sure you want to delete this font? Templates using this font will fall back to default typography.')) return;

        try {
            const res = await fetch(`/api/fonts/${id}`, {
                method: 'DELETE',
                headers: { 'x-user-id': window.CurrentUser ? window.CurrentUser.id : 'admin_super' }
            });
            if (res.ok) {
                fetchFonts();
                Toast.show('Font deleted successfully!', 'success');
            } else {
                const err = await res.json();
                Toast.show(err.error || 'Failed to delete font.', 'error');
            }
        } catch (error) {
            console.error('Delete error:', error);
            Toast.show('Failed to delete font.', 'error');
        }
    }
</script>
@endpush
