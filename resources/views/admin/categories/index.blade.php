@extends('admin.layouts.app')

@section('title', 'Categories')
@section('header_title', 'Category List')

@php $activeTab = 'categories'; @endphp

@section('content')
<div class="space-y-6">
    <!-- Header action bar -->
    <div class="flex justify-between items-center bg-wedding-card p-6 rounded-3xl border border-wedding-pink-medium/20 shadow-xs">
        <div>
            <h3 class="text-lg font-bold text-wedding-charcoal-dark tracking-tight">Category List</h3>
            <p class="text-xs text-gray-500 font-semibold">Manage categories, icons, cover visual assets, and render sequences</p>
        </div>
        <div id="add-btn-container">
            <!-- Dynamically populated based on permission -->
        </div>
    </div>

    <!-- Table Container -->
    <div id="categories-loading" class="flex flex-col items-center justify-center min-h-[40vh] gap-3">
        <div class="w-10 h-10 border-4 border-wedding-pink-medium border-t-wedding-pink-dark rounded-full animate-spin"></div>
        <p class="text-xs font-semibold text-wedding-pink-dark">Loading your directories...</p>
    </div>

    <div id="categories-table-card" class="bg-wedding-card border border-wedding-pink-medium/20 rounded-3xl shadow-xs overflow-hidden hidden animate-fadeIn">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse min-w-[700px]">
                <thead>
                    <tr class="bg-wedding-pink-light/40 border-b border-wedding-pink-medium/20 text-wedding-charcoal-dark font-bold text-xs uppercase tracking-wider">
                        <th class="py-4 px-6">Image</th>
                        <th class="py-4 px-6">Category Name</th>
                        <th class="py-4 px-6">Slug Path</th>
                        <th class="py-4 px-6">Order</th>
                        <th class="py-4 px-6">Status</th>
                        <th class="py-4 px-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody id="categories-list" class="divide-y divide-wedding-pink-medium/15 text-sm text-wedding-charcoal-dark/95">
                    <!-- Dynamic Categories -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- CRUD Overlay Modal Form -->
    <div id="category-modal" class="fixed inset-0 bg-wedding-charcoal-dark/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden animate-fadeIn">
        <div class="bg-wedding-bg border border-wedding-pink-medium/40 w-full max-w-lg rounded-3xl shadow-2xl overflow-hidden animate-slideUp">
            <div class="p-6 bg-wedding-charcoal-dark text-white flex justify-between items-center">
                <h4 id="modal-title" class="font-bold text-lg text-wedding-gold-light">Create Invitation Category</h4>
                <button type="button" onclick="closeModal()" class="text-gray-400 hover:text-white font-bold text-sm bg-wedding-charcoal-light px-3 py-1.5 rounded-xl transition-colors">✕</button>
            </div>
            
            <form id="category-form" onsubmit="handleFormSubmit(event)" class="p-6 space-y-5">
                <input type="hidden" id="editing-id" value="">
                
                <!-- Category Name Input -->
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-wedding-charcoal-light uppercase tracking-wider">Category Name</label>
                    <input 
                        type="text" 
                        id="category-name"
                        oninput="handleNameInput(this.value)"
                        placeholder="e.g. Royal Wedding"
                        class="w-full px-4 py-3 rounded-2xl bg-white border border-wedding-pink-medium/40 focus:ring-2 focus:ring-wedding-pink-dark/20 focus:outline-none text-wedding-charcoal-dark text-sm"
                        required
                    />
                    <p id="error-name" class="text-xs text-red-500 font-semibold mt-1 hidden"></p>
                </div>

                <!-- Slug Path Input -->
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-wedding-charcoal-light uppercase tracking-wider">Folder Slug (automatic)</label>
                    <input 
                        type="text" 
                        id="category-slug"
                        placeholder="e.g. royal_wedding"
                        class="w-full px-4 py-3 rounded-2xl text-sm font-mono focus:ring-2 focus:ring-wedding-pink-dark/20 focus:outline-none bg-white border border-wedding-pink-medium/40 text-wedding-charcoal-dark"
                        required
                    />
                    <p id="error-slug" class="text-xs text-red-500 font-semibold mt-1 hidden"></p>
                    <p class="text-[10px] text-gray-500 font-medium leading-relaxed">
                        * Creates storage sub-path automatically: <code class="font-mono bg-wedding-pink-light/45 px-1 py-0.5 text-wedding-pink-dark rounded">assets/images/<span id="slug-preview">slug</span>/</code>
                    </p>
                </div>

                <!-- Grid of Display Order and Status -->
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-wedding-charcoal-light uppercase tracking-wider">Display Sequence</label>
                        <input 
                            type="number" 
                            id="category-order"
                            value="1"
                            min="1"
                            class="w-full px-4 py-3 rounded-2xl bg-white border border-wedding-pink-medium/40 focus:ring-2 focus:ring-wedding-pink-dark/20 focus:outline-none text-wedding-charcoal-dark text-sm"
                            required
                        />
                        <p id="error-order" class="text-xs text-red-500 font-semibold mt-1 hidden"></p>
                    </div>
                    
                    <div class="space-y-1.5 flex flex-col justify-center">
                        <label class="text-xs font-bold text-wedding-charcoal-light uppercase tracking-wider mb-2">Display State</label>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input 
                                type="checkbox" 
                                id="category-active"
                                checked
                                class="sr-only peer"
                            />
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-wedding-pink-dark"></div>
                            <span id="active-label" class="ml-3 text-sm font-semibold text-wedding-charcoal-dark">Enabled</span>
                        </label>
                    </div>
                </div>

                <!-- Cover Image Upload system -->
                <div class="space-y-2">
                    <label class="text-xs font-bold text-wedding-charcoal-light uppercase tracking-wider block">Category Cover Visual</label>
                    
                    <div class="flex gap-4 items-center">
                        <div id="image-preview-container" class="w-24 h-16 rounded-xl border border-dashed border-wedding-pink-medium/40 bg-wedding-pink-light/10 flex flex-col items-center justify-center text-[10px] text-wedding-pink-dark font-semibold overflow-hidden relative group">
                            <span id="preview-placeholder">No image</span>
                            <img id="preview-img" src="" alt="Preview" class="w-full h-full object-cover hidden">
                            <button 
                                type="button"
                                onclick="removeImage()"
                                id="remove-image-btn"
                                class="absolute inset-0 bg-red-950/40 opacity-0 group-hover:opacity-100 text-white text-[10px] font-bold flex items-center justify-center transition-opacity hidden"
                            >
                                Remove Image
                            </button>
                        </div>
                        <input type="hidden" id="category-image-url" value="">

                        <label id="upload-label" class="flex-1 border border-wedding-pink-medium/40 hover:bg-wedding-pink-light/20 cursor-pointer p-4 rounded-2xl flex flex-col items-center justify-center transition-all">
                            <i data-lucide="upload" class="w-5 h-5 text-wedding-pink-dark mb-1"></i>
                            <span id="upload-text" class="text-[11px] font-bold text-wedding-charcoal-dark">Upload Image Asset</span>
                            <input 
                                type="file" 
                                id="file-input"
                                accept="image/*"
                                onchange="handleFileUpload(event)"
                                class="hidden" 
                            />
                        </label>
                    </div>
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
                        class="px-6 py-3 rounded-2xl bg-wedding-pink-dark hover:bg-wedding-pink-hover text-white text-sm font-bold shadow-lg transition-all"
                    >
                        Save Category
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let categoriesList = [];
    const headers = {
        'x-user-id': window.CurrentUser ? window.CurrentUser.id : 'admin_super',
        'Content-Type': 'application/json'
    };

    // Check permissions
    function hasPermission(perm) {
        if (!window.CurrentUser) return false;
        const rId = window.CurrentUser.roleId || window.CurrentUser.role || 'viewer';
        if (rId === 'super_admin') return true;
        if (window.CurrentUser.permissions && window.CurrentUser.permissions.includes('*')) return true;
        return window.CurrentUser.permissions && window.CurrentUser.permissions.includes(perm);
    }

    // Initialize UI elements based on permissions
    document.addEventListener('DOMContentLoaded', () => {
        if (hasPermission('categories.create')) {
            document.getElementById('add-btn-container').innerHTML = `
                <button
                    onclick="openAddModal()"
                    class="flex items-center gap-2 px-5 py-3 bg-wedding-pink-dark hover:bg-wedding-pink-hover text-white text-sm font-bold rounded-2xl shadow-lg transition-all duration-300 transform hover:-translate-y-0.5"
                >
                    <i data-lucide="plus-circle" class="w-5 h-5"></i>
                    Add Category
                </button>
            `;
            lucide.createIcons({ nodeList: [document.getElementById('add-btn-container')] });
        }

        document.getElementById('category-active').addEventListener('change', (e) => {
            document.getElementById('active-label').innerText = e.target.checked ? 'Enabled' : 'Disabled';
        });

        fetchCategories();
    });

    async function fetchCategories() {
        try {
            const res = await fetch(`/api/categories`, { headers });
            if (!res.ok) throw new Error('Failed to load categories');
            
            const data = await res.json();
            categoriesList = Array.isArray(data) ? data : [];
            renderCategories();
        } catch (err) {
            console.error(err);
            Toast.show('Failed to fetch categories.', 'error');
            document.getElementById('categories-loading').innerHTML = `
                <p class="text-sm font-bold text-red-500">Failed to load categories list.</p>
            `;
        }
    }

    function renderCategories() {
        document.getElementById('categories-loading').classList.add('hidden');
        document.getElementById('categories-table-card').classList.remove('hidden');

        const tbody = document.getElementById('categories-list');
        if (categoriesList.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center py-8 text-gray-400 font-bold">No categories found.</td>
                </tr>
            `;
            return;
        }

        let html = '';
        categoriesList.forEach(cat => {
            const imgHTML = cat.imageUrl 
                ? `<div class="w-14 h-10 rounded-lg overflow-hidden border border-wedding-pink-medium/40 bg-gray-100 flex items-center justify-center">
                    <img src="${cat.imageUrl}" alt="${cat.name}" class="w-full h-full object-cover">
                   </div>`
                : `<div class="w-14 h-10 rounded-lg border border-dashed border-wedding-pink-medium/40 flex items-center justify-center bg-wedding-pink-light/20 text-[10px] text-wedding-pink-dark font-bold">
                    No image
                   </div>`;

            const statusHTML = cat.isActive 
                ? `<span class="flex items-center gap-1 text-green-700 text-xs font-semibold">
                    <i data-lucide="check-circle-2" class="w-4 h-4 fill-green-100 text-green-700"></i> Active
                   </span>`
                : `<span class="flex items-center gap-1 text-gray-400 text-xs font-semibold">
                    <i data-lucide="x-circle" class="w-4 h-4 fill-gray-100 text-gray-400"></i> Disabled
                   </span>`;

            let actionsHTML = '';
            if (hasPermission('categories.edit')) {
                actionsHTML += `
                    <button
                        onclick='openEditModal(${JSON.stringify(cat).replace(/'/g, "&apos;")})'
                        class="p-2 text-wedding-charcoal-light hover:text-wedding-gold-dark hover:bg-wedding-pink-light/30 rounded-xl transition-all duration-200"
                        title="Edit category"
                    >
                        <i data-lucide="edit-3" class="w-4 h-4"></i>
                    </button>
                `;
            }
            if (hasPermission('categories.delete')) {
                actionsHTML += `
                    <button
                        onclick="handleDelete('${cat.id}')"
                        class="p-2 text-red-500 hover:text-red-700 hover:bg-red-50 rounded-xl transition-all duration-200"
                        title="Delete category"
                    >
                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                    </button>
                `;
            }

            html += `
                <tr class="hover:bg-wedding-pink-light/20 transition-colors">
                    <td class="py-4 px-6">${imgHTML}</td>
                    <td class="py-4 px-6 font-bold text-wedding-charcoal-dark">${cat.name}</td>
                    <td class="py-4 px-6 text-gray-500 font-mono text-xs">${cat.slug}</td>
                    <td class="py-4 px-6">
                        <span class="px-3 py-1 bg-wedding-pink-light text-wedding-pink-dark text-xs font-bold rounded-lg border border-wedding-pink-medium/30 shadow-xs">
                            ${cat.displayOrder}
                        </span>
                    </td>
                    <td class="py-4 px-6">${statusHTML}</td>
                    <td class="py-4 px-6 text-right">
                        <div class="flex justify-end gap-2">${actionsHTML}</div>
                    </td>
                </tr>
            `;
        });

        tbody.innerHTML = html;
        lucide.createIcons({ nodeList: [tbody] });
    }

    function handleNameInput(val) {
        document.getElementById('error-name').classList.add('hidden');
        const editingId = document.getElementById('editing-id').value;
        if (!editingId) {
            const generatedSlug = val.toLowerCase().replace(/[^a-z0-9_]/g, '_').replace(/_+/g, '_');
            document.getElementById('category-slug').value = generatedSlug;
            document.getElementById('slug-preview').innerText = generatedSlug || 'slug';
            document.getElementById('error-slug').classList.add('hidden');
        }
    }

    function openAddModal() {
        document.getElementById('modal-title').innerText = 'Create Invitation Category';
        document.getElementById('editing-id').value = '';
        document.getElementById('category-name').value = '';
        document.getElementById('category-slug').value = '';
        document.getElementById('category-slug').disabled = false;
        document.getElementById('category-order').value = '1';
        document.getElementById('category-active').checked = true;
        document.getElementById('active-label').innerText = 'Enabled';
        document.getElementById('slug-preview').innerText = 'slug';
        removeImage();

        hideFormErrors();
        document.getElementById('category-modal').classList.remove('hidden');
    }

    function openEditModal(cat) {
        document.getElementById('modal-title').innerText = 'Edit Invitation Category';
        document.getElementById('editing-id').value = cat.id;
        document.getElementById('category-name').value = cat.name;
        document.getElementById('category-slug').value = cat.slug;
        document.getElementById('category-slug').disabled = true;
        document.getElementById('category-order').value = cat.displayOrder;
        document.getElementById('category-active').checked = cat.isActive;
        document.getElementById('active-label').innerText = cat.isActive ? 'Enabled' : 'Disabled';
        document.getElementById('slug-preview').innerText = cat.slug;

        if (cat.imageUrl) {
            showPreviewImage(cat.imageUrl);
        } else {
            removeImage();
        }

        hideFormErrors();
        document.getElementById('category-modal').classList.remove('hidden');
    }

    function closeModal() {
        document.getElementById('category-modal').classList.add('hidden');
    }

    function hideFormErrors() {
        document.getElementById('error-name').classList.add('hidden');
        document.getElementById('error-slug').classList.add('hidden');
        document.getElementById('error-order').classList.add('hidden');
    }

    function showPreviewImage(url) {
        document.getElementById('category-image-url').value = url;
        document.getElementById('preview-placeholder').classList.add('hidden');
        const img = document.getElementById('preview-img');
        img.src = url;
        img.classList.remove('hidden');
        document.getElementById('remove-image-btn').classList.remove('hidden');
    }

    function removeImage() {
        document.getElementById('category-image-url').value = '';
        document.getElementById('preview-placeholder').classList.remove('hidden');
        const img = document.getElementById('preview-img');
        img.src = '';
        img.classList.add('hidden');
        document.getElementById('remove-image-btn').classList.add('hidden');
        document.getElementById('file-input').value = '';
    }

    async function handleFileUpload(e) {
        const fileInput = e.target;
        if (!fileInput.files || fileInput.files.length === 0) return;
        const file = fileInput.files[0];
        
        const slug = document.getElementById('category-slug').value || 'temp_category';
        document.getElementById('upload-text').innerText = 'Uploading...';
        
        const formData = new FormData();
        formData.append('file', file);

        try {
            const res = await fetch(`/api/uploads/single?type=categories&categorySlug=${slug}`, {
                method: 'POST',
                headers: {
                    'x-user-id': window.CurrentUser ? window.CurrentUser.id : 'admin_super'
                },
                body: formData
            });
            const data = await res.json();
            if (data.success) {
                showPreviewImage(data.filePath);
                Toast.show('Cover image uploaded successfully!', 'success');
            } else {
                Toast.show(data.error || 'Upload failed', 'error');
            }
        } catch (err) {
            console.error('Upload error:', err);
            Toast.show('Failed to upload image.', 'error');
        } finally {
            document.getElementById('upload-text').innerText = 'Upload Image Asset';
        }
    }

    function validateForm() {
        hideFormErrors();
        let isValid = true;
        const name = document.getElementById('category-name').value.trim();
        const slug = document.getElementById('category-slug').value.trim();
        const order = document.getElementById('category-order').value;

        if (!name) {
            document.getElementById('error-name').innerText = 'Category name is required.';
            document.getElementById('error-name').classList.remove('hidden');
            isValid = false;
        }

        if (!slug) {
            document.getElementById('error-slug').innerText = 'Slug is required.';
            document.getElementById('error-slug').classList.remove('hidden');
            isValid = false;
        } else if (!/^[a-z0-9_]+$/.test(slug)) {
            document.getElementById('error-slug').innerText = 'Slug can only contain lowercase letters, numbers, and underscores.';
            document.getElementById('error-slug').classList.remove('hidden');
            isValid = false;
        }

        const orderNum = parseInt(order);
        if (!order.trim()) {
            document.getElementById('error-order').innerText = 'Display sequence is required.';
            document.getElementById('error-order').classList.remove('hidden');
            isValid = false;
        } else if (isNaN(orderNum) || orderNum < 1) {
            document.getElementById('error-order').innerText = 'Display sequence must be a positive number.';
            document.getElementById('error-order').classList.remove('hidden');
            isValid = false;
        }

        return isValid;
    }

    async function handleFormSubmit(e) {
        e.preventDefault();
        if (!validateForm()) return;

        const editingId = document.getElementById('editing-id').value;
        const payload = {
            name: document.getElementById('category-name').value.trim(),
            slug: document.getElementById('category-slug').value.trim(),
            imageUrl: document.getElementById('category-image-url').value,
            displayOrder: parseInt(document.getElementById('category-order').value) || 1,
            isActive: document.getElementById('category-active').checked
        };

        try {
            let res;
            if (editingId) {
                if (!hasPermission('categories.edit')) {
                    Toast.show('Access Denied. Missing categories.edit permission.', 'warning');
                    return;
                }
                res = await fetch(`/api/categories/${editingId}`, {
                    method: 'PUT',
                    headers,
                    body: JSON.stringify(payload)
                });
            } else {
                if (!hasPermission('categories.create')) {
                    Toast.show('Access Denied. Missing categories.create permission.', 'warning');
                    return;
                }
                res = await fetch(`/api/categories`, {
                    method: 'POST',
                    headers,
                    body: JSON.stringify(payload)
                });
            }

            if (res.ok) {
                closeModal();
                fetchCategories();
                Toast.show(editingId ? 'Category updated successfully!' : 'Category created successfully!', 'success');
            } else {
                const err = await res.json();
                Toast.show(err.error || 'Save failed', 'error');
            }
        } catch (error) {
            console.error('Submit error:', error);
            Toast.show('Failed to save category.', 'error');
        }
    }

    async function handleDelete(id) {
        if (!hasPermission('categories.delete')) {
            Toast.show('Access Denied. Missing categories.delete permission.', 'warning');
            return;
        }
        if (!confirm('Are you sure you want to delete this category?')) return;

        try {
            const res = await fetch(`/api/categories/${id}`, {
                method: 'DELETE',
                headers: { 'x-user-id': window.CurrentUser ? window.CurrentUser.id : 'admin_super' }
            });
            if (res.ok) {
                fetchCategories();
                Toast.show('Category deleted successfully!', 'success');
            } else {
                const err = await res.json();
                Toast.show(err.error || 'Failed to delete category.', 'error');
            }
        } catch (error) {
            console.error('Delete error:', error);
            Toast.show('Failed to delete category.', 'error');
        }
    }
</script>
@endpush
