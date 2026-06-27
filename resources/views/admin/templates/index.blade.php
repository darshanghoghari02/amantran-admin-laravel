@extends('admin.layouts.app')

@section('title', 'Templates')
@section('header_title', 'Invitation Templates')

@php $activeTab = 'templates'; @endphp

@section('content')
<div class="space-y-6" id="templates-page">

    {{-- Toolbar: Search + Create + Import buttons, Category filters --}}
    <div class="bg-wedding-card p-4 sm:p-6 rounded-3xl border border-wedding-pink-medium/10 shadow-md flex flex-col gap-4">

        {{-- Row 1: Search Bar + Action Buttons --}}
        <div class="flex items-center gap-3">
            {{-- Search Input --}}
            <div class="relative flex-1">
                <div class="absolute inset-y-0 left-4 flex items-center pointer-events-none">
                    <i data-lucide="search" class="w-4 h-4 text-wedding-pink-dark/60"></i>
                </div>
                <input
                    type="text"
                    id="search-input"
                    oninput="filterTemplates()"
                    placeholder="Search templates by name or slug..."
                    class="w-full pl-11 pr-10 py-3 bg-wedding-bg border border-wedding-pink-medium/20 rounded-2xl text-sm font-medium text-wedding-charcoal-dark placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-wedding-pink-dark/30 focus:border-wedding-pink-dark/40 transition-all duration-300 shadow-sm"
                />
                <button
                    onclick="clearSearch()"
                    id="search-clear-btn"
                    class="absolute inset-y-0 right-3 flex items-center px-1 text-gray-400 hover:text-wedding-pink-dark transition-colors hidden"
                    title="Clear search"
                >
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            {{-- Create Template Button (permission gated) --}}
            <div id="create-btn-container"></div>

            {{-- Import JSON Button (permission gated) --}}
            <div id="import-btn-container"></div>
            <input type="file" id="json-import-input" accept=".json" class="hidden" onchange="handleJsonImport(event)">
        </div>

        {{-- Row 2: Category Filter Scroll --}}
        <div class="flex items-center gap-0">
            {{-- Fixed: All Invitations button --}}
            <button
                id="cat-all-btn"
                onclick="selectCategory('')"
                class="px-5 py-2.5 rounded-xl text-xs font-bold transition-all duration-300 shrink-0 border bg-wedding-pink-dark text-white border-transparent shadow-md shadow-wedding-pink-dark/15"
            >
                All Invitations
            </button>

            {{-- Divider --}}
            <div class="w-px h-6 bg-wedding-pink-medium/20 mx-3 shrink-0"></div>

            {{-- Left Arrow --}}
            <button
                id="cat-scroll-left"
                onclick="scrollCategories('left')"
                class="shrink-0 p-2 rounded-xl bg-wedding-pink-light/40 text-wedding-charcoal-light/75 border border-wedding-pink-medium/20 hover:bg-wedding-pink-light/90 hover:text-wedding-pink-dark hover:border-wedding-pink-medium/50 transition-all duration-200 mr-2 hidden"
            >
                <i data-lucide="chevron-left" class="w-4 h-4"></i>
            </button>

            {{-- Scrollable Categories --}}
            <div id="categories-scroll" class="flex gap-2 items-center overflow-x-auto flex-1" style="scrollbar-width: none; -ms-overflow-style: none;">
                {{-- Dynamic category pills --}}
            </div>

            {{-- Right Arrow --}}
            <button
                id="cat-scroll-right"
                onclick="scrollCategories('right')"
                class="shrink-0 p-2 rounded-xl bg-wedding-pink-light/40 text-wedding-charcoal-light/75 border border-wedding-pink-medium/20 hover:bg-wedding-pink-light/90 hover:text-wedding-pink-dark hover:border-wedding-pink-medium/50 transition-all duration-200 ml-2 hidden"
            >
                <i data-lucide="chevron-right" class="w-4 h-4"></i>
            </button>
        </div>

        {{-- Search Results Count --}}
        <p id="search-results-text" class="text-xs font-semibold text-wedding-charcoal-light/70 hidden"></p>
    </div>

    {{-- Loading State --}}
    <div id="templates-loading" class="flex flex-col items-center justify-center min-h-[40vh] gap-3">
        <div class="w-10 h-10 border-4 border-wedding-pink-medium border-t-wedding-pink-dark rounded-full animate-spin"></div>
        <p class="text-xs font-semibold text-wedding-pink-dark">Fetching template assets...</p>
    </div>

    {{-- Template Grid --}}
    <div id="templates-grid" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 animate-fadeIn hidden">
        {{-- Dynamic template cards --}}
    </div>

    {{-- Empty State --}}
    <div id="templates-empty" class="hidden col-span-full py-16 text-center text-gray-400 font-semibold bg-wedding-card border rounded-3xl border-wedding-pink-medium/10 shadow-md">
        No invitation templates inside this category directory yet.
    </div>

    {{-- Pagination Container --}}
    <div id="pagination-container"></div>
</div>

{{-- ═══════════════════════════════════════════════════════════ --}}
{{-- Creation / Edit Wizard Modal --}}
{{-- ═══════════════════════════════════════════════════════════ --}}
<div id="template-modal" class="fixed inset-0 bg-wedding-charcoal-dark/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 overflow-y-auto hidden animate-fadeIn">
    <div class="bg-wedding-bg border border-wedding-pink-medium/40 w-full max-w-2xl rounded-3xl shadow-2xl overflow-hidden my-8 animate-slideUp">
        {{-- Modal Header --}}
        <div class="p-6 bg-wedding-charcoal-dark text-white flex justify-between items-center">
            <h4 id="modal-title" class="font-bold text-lg text-wedding-gold-light flex items-center gap-2">
                <i data-lucide="palette" class="w-5 h-5 text-wedding-pink-medium"></i>
                Initialize Wedding Template
            </h4>
            <button type="button" onclick="closeModal()" class="w-8 h-8 flex items-center justify-center text-gray-400 hover:text-white hover:bg-white/10 rounded-full transition-colors font-bold text-sm">✕</button>
        </div>

        {{-- Modal Form --}}
        <form id="template-form" onsubmit="handleFormSubmit(event)" class="p-6 space-y-6">
            <input type="hidden" id="editing-id" value="">

            {{-- Row 1: Name + Slug --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-wedding-charcoal-light uppercase tracking-wider">Template Name</label>
                    <input
                        type="text"
                        id="tpl-name"
                        oninput="handleNameInput(this.value)"
                        placeholder="e.g. Royal Gold Wedding"
                        class="w-full px-4 py-3 rounded-2xl bg-white border border-wedding-pink-medium/40 focus:ring-2 focus:ring-wedding-pink-dark/20 focus:outline-none text-wedding-charcoal-dark text-sm"
                    />
                    <p id="error-name" class="text-xs text-red-500 font-semibold hidden"></p>
                </div>
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-wedding-charcoal-light uppercase tracking-wider">Asset Slug (automatic)</label>
                    <input
                        type="text"
                        id="tpl-slug"
                        placeholder="e.g. royal_gold_wedding"
                        oninput="this.value = this.value.toLowerCase().replace(/[^a-z0-9_]/g,'_')"
                        class="w-full px-4 py-3 rounded-2xl text-sm font-mono focus:ring-2 focus:ring-wedding-pink-dark/20 focus:outline-none bg-gray-50 border border-wedding-pink-medium/30 text-wedding-charcoal-dark/70"
                    />
                    <p id="error-slug" class="text-xs text-red-500 font-semibold hidden"></p>
                </div>
            </div>

            {{-- Row 2: Category + Toggles --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-wedding-charcoal-light uppercase tracking-wider">Category</label>
                    <select id="tpl-category" class="w-full px-4 py-3 rounded-2xl bg-white border border-wedding-pink-medium/40 focus:ring-2 focus:ring-wedding-pink-dark/20 focus:outline-none text-wedding-charcoal-dark text-sm">
                        <option value="">Select Category</option>
                    </select>
                    <p id="error-category" class="text-xs text-red-500 font-semibold hidden"></p>
                </div>
                <div class="space-y-1.5 flex flex-col justify-center pl-2">
                    <label class="text-xs font-bold text-wedding-charcoal-light uppercase tracking-wider mb-2">Access Type</label>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" id="tpl-premium" checked class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-wedding-pink-dark"></div>
                        <span id="premium-label" class="ml-3 text-sm font-semibold text-wedding-charcoal-dark">Premium Lock</span>
                    </label>
                </div>
                <div class="space-y-1.5 flex flex-col justify-center pl-2">
                    <label class="text-xs font-bold text-wedding-charcoal-light uppercase tracking-wider mb-2">Display State</label>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" id="tpl-active" checked class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-wedding-pink-dark"></div>
                        <span id="active-label" class="ml-3 text-sm font-semibold text-wedding-charcoal-dark">Published</span>
                    </label>
                </div>
            </div>

            {{-- Row 3: Fonts Multi-select --}}
            <div class="space-y-2">
                <label class="text-xs font-bold text-wedding-charcoal-light uppercase tracking-wider block">Assigned Layout Fonts</label>
                <div id="fonts-selector" class="flex gap-2 flex-wrap bg-white p-4 border border-wedding-pink-medium/30 rounded-2xl min-h-[60px]">
                    <span class="text-xs text-gray-400">Loading fonts...</span>
                </div>
            </div>

            {{-- Row 4: Languages Multi-select --}}
            <div class="space-y-2">
                <label class="text-xs font-bold text-wedding-charcoal-light uppercase tracking-wider block">Assigned Languages</label>
                <div id="languages-selector" class="flex gap-2 flex-wrap bg-white p-4 border border-wedding-pink-medium/30 rounded-2xl min-h-[60px]">
                    <span class="text-xs text-gray-400">Loading languages...</span>
                </div>
            </div>

            {{-- Row 5: Pricing & Plans (only when premium) --}}
            <div id="pricing-section" class="space-y-3 p-4 bg-gradient-to-br from-amber-50 to-yellow-50/30 border border-amber-200/50 rounded-2xl animate-fadeIn">
                <h5 class="text-xs font-bold text-amber-800 uppercase tracking-wider flex items-center gap-1.5">
                    <i data-lucide="sparkles" class="w-4 h-4 text-amber-700"></i>
                    Pricing & Subscription Plans
                </h5>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-1">
                    <div class="space-y-1.5 sm:col-span-1">
                        <label class="text-[10px] font-bold text-wedding-charcoal-light uppercase tracking-wider block">Single Purchase Price (INR)</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-3.5 flex items-center text-gray-500 font-bold text-sm">₹</span>
                            <input
                                type="number"
                                id="tpl-price"
                                min="0"
                                value="49"
                                class="w-full pl-8 pr-4 py-2.5 rounded-xl bg-white border border-wedding-pink-medium/30 focus:ring-2 focus:ring-wedding-pink-dark/20 focus:outline-none text-wedding-charcoal-dark text-sm font-semibold"
                            />
                        </div>
                        <p id="error-price" class="text-xs text-red-500 font-semibold hidden"></p>
                    </div>
                    <div class="space-y-1.5 sm:col-span-2">
                        <label class="text-[10px] font-bold text-wedding-charcoal-light uppercase tracking-wider block mb-1">Include in Subscription Plans</label>
                        <div id="plans-selector" class="flex gap-2 flex-wrap bg-white p-2.5 border border-wedding-pink-medium/30 rounded-2xl min-h-[48px]">
                            <span class="text-xs text-gray-400">Loading plans...</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Row 6: File Uploads --}}
            <div id="upload-section" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                {{-- Thumbnail --}}
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-wedding-charcoal-light uppercase tracking-wider">Template Thumbnail</label>
                    <label id="thumbnail-label" class="border-2 border-dashed cursor-pointer p-5 rounded-2xl flex flex-col items-center justify-center transition-all bg-white border-wedding-pink-medium/50 hover:border-wedding-pink-dark/50 hover:bg-wedding-pink-light/10 shadow-xs">
                        <i data-lucide="upload" class="w-5 h-5 text-wedding-pink-dark mb-1"></i>
                        <span id="thumbnail-text" class="text-[11px] font-bold text-wedding-charcoal-dark text-center">Choose Thumbnail File</span>
                        <input type="file" id="thumbnail-file" accept="image/*" onchange="handleThumbnailSelect(this)" class="hidden">
                    </label>
                    <p id="error-thumbnail" class="text-xs text-red-500 font-semibold hidden"></p>
                </div>
                {{-- Page Backgrounds --}}
                <div class="space-y-1.5" id="bg-upload-section">
                    <label class="text-xs font-bold text-wedding-charcoal-light uppercase tracking-wider">Page Backgrounds (Multiple)</label>
                    <label class="border-2 border-dashed cursor-pointer p-5 rounded-2xl flex flex-col items-center justify-center transition-all bg-white border-wedding-pink-medium/50 hover:border-wedding-pink-dark/50 hover:bg-wedding-pink-light/10 shadow-xs">
                        <i data-lucide="upload" class="w-5 h-5 text-wedding-pink-dark mb-1"></i>
                        <span id="bg-text" class="text-[11px] font-bold text-wedding-charcoal-dark text-center">Select Page Backgrounds</span>
                        <input type="file" id="bg-files" accept="image/*" multiple onchange="handleBgSelect(this)" class="hidden">
                    </label>
                    <p id="error-bg" class="text-xs text-red-500 font-semibold hidden"></p>
                </div>
            </div>

            {{-- Submit Buttons --}}
            <div class="pt-4 border-t border-wedding-pink-medium/20 flex justify-end gap-3">
                <button
                    type="button"
                    onclick="closeModal()"
                    class="px-5 py-3 rounded-2xl bg-wedding-pink-light/40 border border-wedding-pink-medium/30 text-wedding-charcoal-light hover:bg-wedding-pink-light/80 hover:text-wedding-pink-dark text-sm font-bold transition-all duration-300"
                >
                    Cancel
                </button>
                <button
                    type="submit"
                    id="submit-btn"
                    class="px-6 py-3 rounded-2xl bg-wedding-pink-dark hover:bg-[#a0525e] text-white text-sm font-bold shadow-lg transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    Generate Template
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
// ═══ STATE ═══════════════════════════════════════════════════
let allTemplates = [];
let allCategories = [];
let allFonts = [];
let allLanguages = [];
let allPlans = [];
let selectedCatId = '';
let selectedFonts = [];
let selectedLangs = [];
let selectedPlanIds = ['monthly', 'yearly'];
let isUploading = false;
let currentPage = 1;
let perPage = 12;
let totalTemplates = 0;
let totalPages = 1;
let searchTimeout = null;

const headers = {
    'Content-Type': 'application/json',
    'x-user-id': window.CurrentUser ? window.CurrentUser.id : 'admin_super'
};

// ═══ PERMISSIONS ═════════════════════════════════════════════
function hasPermission(perm) {
    if (!window.CurrentUser) return false;
    const rId = window.CurrentUser.roleId || window.CurrentUser.role || 'viewer';
    if (rId === 'super_admin') return true;
    if (window.CurrentUser.permissions && window.CurrentUser.permissions.includes('*')) return true;
    return window.CurrentUser.permissions && window.CurrentUser.permissions.includes(perm);
}

// ═══ INIT ════════════════════════════════════════════════════
document.addEventListener('DOMContentLoaded', () => {
    // Build permission-gated buttons
    if (hasPermission('templates.create')) {
        document.getElementById('create-btn-container').innerHTML = `
            <button onclick="openAddModal()" class="flex items-center gap-2 px-5 py-3 bg-wedding-pink-dark hover:bg-[#a0525e] text-white text-sm font-bold rounded-2xl shadow-lg transition-all duration-300 transform hover:-translate-y-0.5 shrink-0">
                <i data-lucide="plus-circle" class="w-5 h-5"></i> Create Template
            </button>
        `;
        document.getElementById('import-btn-container').innerHTML = `
            <button onclick="document.getElementById('json-import-input').click()" class="flex items-center gap-2 px-5 py-3 bg-wedding-pink-light border border-wedding-pink-medium/40 hover:bg-wedding-pink-light/80 text-wedding-pink-dark text-sm font-bold rounded-2xl shadow-md transition-all duration-300 transform hover:-translate-y-0.5 shrink-0">
                <i data-lucide="upload" class="w-4 h-4"></i> Import JSON
            </button>
        `;
        lucide.createIcons({ nodeList: [document.getElementById('create-btn-container'), document.getElementById('import-btn-container')] });
    }

    // Toggle event handlers
    document.getElementById('tpl-premium').addEventListener('change', function() {
        document.getElementById('premium-label').innerText = this.checked ? 'Premium Lock' : 'Free Access';
        document.getElementById('pricing-section').style.display = this.checked ? 'block' : 'none';
    });
    document.getElementById('tpl-active').addEventListener('change', function() {
        document.getElementById('active-label').innerText = this.checked ? 'Published' : 'Hidden Draft';
    });

    fetchAllData();
});

async function fetchAllData() {
    const loadingEl = document.getElementById('templates-loading');
    
    try {
        const catParam = selectedCatId ? `categoryId=${selectedCatId}` : '';
        const pageParam = `page=${currentPage}&perPage=${perPage}`;
        const queryParams = [catParam, pageParam].filter(Boolean).join('&');
        const url = `/api/templates${queryParams ? '?' + queryParams : ''}`;
        
        const userId = window.CurrentUser ? window.CurrentUser.id : 'admin_super';
        const h = { 'x-user-id': userId };

        const [resTpl, resCat, resFont, resLang, resPlans] = await Promise.all([
            fetch(url, { headers: h }),
            fetch(`/api/categories`, { headers: h }),
            fetch(`/api/fonts`, { headers: h }),
            fetch(`/api/languages`, { headers: h }),
            fetch(`/api/subscriptions`, { headers: h })
        ]);

        const tplResponse = await resTpl.json();
        const catData = await resCat.json();
        const fontData = await resFont.json();
        const langData = await resLang.json();
        const plansData = await resPlans.json();

        // Handle paginated response
        if (tplResponse.data && tplResponse.pagination) {
            allTemplates = tplResponse.data;
            totalTemplates = tplResponse.pagination.total;
            totalPages = tplResponse.pagination.totalPages;
            currentPage = tplResponse.pagination.page;
        } else {
            // Fallback for non-paginated response
            allTemplates = Array.isArray(tplResponse) ? tplResponse : [];
            totalTemplates = allTemplates.length;
            totalPages = 1;
        }
        
        allCategories = Array.isArray(catData) ? catData : [];
        allFonts = Array.isArray(fontData) ? fontData.filter(f => f.isActive) : [];
        allLanguages = Array.isArray(langData) ? langData.filter(l => l.isActive) : [];
        allPlans = Array.isArray(plansData) ? plansData : [];

        renderCategoryPills();
        renderTemplates();
        renderPagination();
        populateModalSelectors();
    } catch (err) {
        console.error('Failed to load templates data:', err);
        Toast.show('Failed to load templates: ' + err.message, 'error');
    } finally {
        // Always hide loading state
        if (loadingEl) loadingEl.classList.add('hidden');
    }
}

// ═══ CATEGORY PILLS ══════════════════════════════════════════
function renderCategoryPills() {
    const container = document.getElementById('categories-scroll');
    container.innerHTML = '';

    allCategories.forEach(cat => {
        const btn = document.createElement('button');
        btn.className = `px-5 py-2.5 rounded-xl text-xs font-bold transition-all duration-300 shrink-0 border ${
            selectedCatId === cat.id
                ? 'bg-wedding-pink-dark text-white border-transparent shadow-md shadow-wedding-pink-dark/15'
                : 'bg-wedding-pink-light/40 text-wedding-charcoal-light/85 border-wedding-pink-medium/30 hover:bg-wedding-pink-light/90 hover:text-wedding-pink-dark hover:border-wedding-pink-medium/60'
        }`;
        btn.textContent = cat.name;
        btn.onclick = () => selectCategory(cat.id);
        container.appendChild(btn);
    });

    checkScrollable();
}

function checkScrollable() {
    const el = document.getElementById('categories-scroll');
    const showArrows = el.scrollWidth > el.clientWidth;
    document.getElementById('cat-scroll-left').classList.toggle('hidden', !showArrows);
    document.getElementById('cat-scroll-right').classList.toggle('hidden', !showArrows);
}

function scrollCategories(dir) {
    const el = document.getElementById('categories-scroll');
    el.scrollBy({ left: dir === 'left' ? -200 : 200, behavior: 'smooth' });
}

function selectCategory(catId) {
    selectedCatId = catId;
    currentPage = 1; // Reset to first page when changing category

    // Update "All Invitations" pill
    document.getElementById('cat-all-btn').className = `px-5 py-2.5 rounded-xl text-xs font-bold transition-all duration-300 shrink-0 border ${
        !catId
            ? 'bg-wedding-pink-dark text-white border-transparent shadow-md shadow-wedding-pink-dark/15'
            : 'bg-wedding-pink-light/40 text-wedding-charcoal-light/85 border-wedding-pink-medium/30 hover:bg-wedding-pink-light/90 hover:text-wedding-pink-dark hover:border-wedding-pink-medium/60'
    }`;

    renderCategoryPills();
    fetchAllData();
}

// ═══ RENDER TEMPLATES ════════════════════════════════════════
function getImageUrl(path) {
    if (!path) return '';
    try {
        if (path.startsWith('http://') || path.startsWith('https://')) {
            const parsed = new URL(path);
            if (parsed.origin !== window.location.origin) {
                return window.location.origin + parsed.pathname + parsed.search;
            }
            return path;
        }
    } catch(e) {}
    return path.startsWith('/') ? path : '/' + path;
}

function filterTemplates() {
    // Clear previous timeout
    if (searchTimeout) {
        clearTimeout(searchTimeout);
    }
    
    // Debounce search to improve performance
    searchTimeout = setTimeout(() => {
        const q = document.getElementById('search-input').value.trim().toLowerCase();
        document.getElementById('search-clear-btn').classList.toggle('hidden', !q);

        const filtered = allTemplates.filter(tpl => {
            if (selectedCatId && tpl.categoryId !== selectedCatId) return false;
            if (!q) return true;
            return tpl.name.toLowerCase().includes(q) || tpl.slug.toLowerCase().includes(q);
        });

        const resultsEl = document.getElementById('search-results-text');
        if (q) {
            resultsEl.classList.remove('hidden');
            resultsEl.textContent = filtered.length === 0
                ? 'No templates found'
                : `${filtered.length} template${filtered.length !== 1 ? 's' : ''} found for "${q}"`;
        } else {
            resultsEl.classList.add('hidden');
        }

        renderTemplateCards(filtered);
    }, 300); // 300ms debounce delay
}

function clearSearch() {
    document.getElementById('search-input').value = '';
    document.getElementById('search-clear-btn').classList.add('hidden');
    document.getElementById('search-results-text').classList.add('hidden');
    renderTemplates();
}

function renderTemplates() {
    renderTemplateCards(allTemplates);
}

// ═══ PAGINATION ═══════════════════════════════════════════════
function renderPagination() {
    const container = document.getElementById('pagination-container');
    if (!container) return;
    
    if (totalPages <= 1) {
        container.innerHTML = '';
        return;
    }
    
    let html = `
        <div class="flex items-center justify-center gap-2 mt-8">
            <button onclick="goToPage(${currentPage - 1})" 
                ${currentPage === 1 ? 'disabled' : ''} 
                class="px-4 py-2 rounded-xl bg-wedding-card border border-wedding-pink-medium/20 text-wedding-charcoal-dark text-xs font-bold hover:bg-wedding-pink-light/50 disabled:opacity-50 disabled:cursor-not-allowed transition-all">
                <i data-lucide="chevron-left" class="w-4 h-4"></i>
            </button>
    `;
    
    for (let i = 1; i <= totalPages; i++) {
        if (i === 1 || i === totalPages || (i >= currentPage - 1 && i <= currentPage + 1)) {
            html += `
                <button onclick="goToPage(${i})" 
                    class="px-4 py-2 rounded-xl text-xs font-bold transition-all ${
                        i === currentPage 
                            ? 'bg-wedding-pink-dark text-white border-transparent shadow-md' 
                            : 'bg-wedding-card border border-wedding-pink-medium/20 text-wedding-charcoal-dark hover:bg-wedding-pink-light/50'
                    }">
                    ${i}
                </button>
            `;
        } else if (i === currentPage - 2 || i === currentPage + 2) {
            html += `<span class="text-wedding-charcoal-light/50 text-xs font-bold">...</span>`;
        }
    }
    
    html += `
            <button onclick="goToPage(${currentPage + 1})" 
                ${currentPage === totalPages ? 'disabled' : ''} 
                class="px-4 py-2 rounded-xl bg-wedding-card border border-wedding-pink-medium/20 text-wedding-charcoal-dark text-xs font-bold hover:bg-wedding-pink-light/50 disabled:opacity-50 disabled:cursor-not-allowed transition-all">
                <i data-lucide="chevron-right" class="w-4 h-4"></i>
            </button>
        </div>
        <p class="text-center text-xs text-wedding-charcoal-light/60 mt-2">
            Showing ${(currentPage - 1) * perPage + 1}-${Math.min(currentPage * perPage, totalTemplates)} of ${totalTemplates} templates
        </p>
    `;
    
    container.innerHTML = html;
    lucide.createIcons({ nodeList: [container] });
}

function goToPage(page) {
    if (page < 1 || page > totalPages || page === currentPage) return;
    currentPage = page;
    document.getElementById('templates-loading').classList.remove('hidden');
    fetchAllData();
}

function renderTemplateCards(templates) {
    const grid = document.getElementById('templates-grid');
    const empty = document.getElementById('templates-empty');

    grid.innerHTML = '';

    if (templates.length === 0) {
        grid.classList.add('hidden');
        empty.classList.remove('hidden');
        const q = document.getElementById('search-input').value.trim();
        empty.textContent = q ? `No templates found for "${q}"` : 'No invitation templates inside this category directory yet.';
        return;
    }

    grid.classList.remove('hidden');
    empty.classList.add('hidden');

    templates.forEach(tpl => {
        const cat = allCategories.find(c => c.id === tpl.categoryId);
        const catName = cat ? cat.name : 'General';
        const thumbUrl = getImageUrl(tpl.thumbnail);

        // Build plan badges
        let badgesHTML = '';
        if (tpl.isPremium) {
            const monthlyPlan = allPlans.find(p => p.id === 'monthly');
            const yearlyPlan = allPlans.find(p => p.id === 'yearly');

            if (tpl.includedInMonthlyPlan && monthlyPlan && monthlyPlan.isActive !== false) {
                badgesHTML += `<span class="flex items-center gap-0.5 px-2 py-0.5 bg-blue-600 text-white text-[9px] font-extrabold rounded-md uppercase shadow-sm">Monthly</span>`;
            }
            if (tpl.includedInYearlyPlan && yearlyPlan && yearlyPlan.isActive !== false) {
                badgesHTML += `<span class="flex items-center gap-0.5 px-2 py-0.5 bg-purple-600 text-white text-[9px] font-extrabold rounded-md uppercase shadow-sm">Yearly</span>`;
            }
            allPlans.forEach(p => {
                if (p.isActive && p.id !== 'monthly' && p.id !== 'yearly' && p.includedTemplateIds && p.includedTemplateIds.includes(tpl.id)) {
                    badgesHTML += `<span class="flex items-center gap-0.5 px-2 py-0.5 bg-emerald-600 text-white text-[9px] font-extrabold rounded-md uppercase shadow-sm">${escapeHtml(p.name)}</span>`;
                }
            });
            if (tpl.singlePurchasePrice && tpl.singlePurchasePrice > 0) {
                badgesHTML += `<span class="flex items-center gap-0.5 px-2 py-0.5 bg-amber-600 text-white text-[9px] font-extrabold rounded-md uppercase shadow-sm font-mono">₹${tpl.singlePurchasePrice}</span>`;
            } else if (!tpl.includedInMonthlyPlan && !tpl.includedInYearlyPlan) {
                badgesHTML += `<span class="flex items-center gap-1 px-2.5 py-0.5 bg-wedding-gold-dark text-white text-[9px] font-bold rounded-md uppercase shadow-sm"><i data-lucide="sparkles" class="w-2.5 h-2.5"></i> Premium</span>`;
            }
        } else {
            badgesHTML += `<span class="px-2 pt-1 py-0.5 bg-wedding-charcoal-light/95 text-white text-[9px] font-bold rounded-md uppercase shadow-sm">Free</span>`;
        }

        const liveStatus = tpl.isActive
            ? `<span class="flex items-center gap-0.5 px-3 py-1 bg-green-600 text-white text-[10px] font-bold rounded-lg uppercase shadow"><i data-lucide="eye" class="w-3 h-3"></i> Live</span>`
            : `<span class="flex items-center gap-0.5 px-3 py-1 bg-amber-600 text-white text-[10px] font-bold rounded-lg uppercase shadow"><i data-lucide="eye-off" class="w-3 h-3"></i> Draft</span>`;

        const designBtn = hasPermission('templates.edit')
            ? `<div class="absolute inset-0 bg-wedding-charcoal-dark/40 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity duration-300">
                <button onclick="openEditor('${tpl.id}')" class="px-6 py-3.5 bg-white hover:bg-wedding-pink-light text-wedding-charcoal-dark text-xs font-extrabold rounded-2xl shadow-xl transition-all duration-200 transform scale-90 group-hover:scale-100 hover:scale-105 flex items-center gap-2">
                    <i data-lucide="palette" class="w-4 h-4 text-wedding-pink-dark"></i> Design in Canvas
                </button>
            </div>`
            : '';

        let footerActionHTML = '';
        if (!hasPermission('templates.edit')) {
            footerActionHTML = `<span class="flex-1 py-2 border border-wedding-pink-medium/20 text-wedding-charcoal-light/60 text-xs font-bold rounded-xl flex items-center justify-center gap-1.5 bg-gray-50/50 select-none">Read-Only View</span>`;
        } else {
            footerActionHTML = `<button onclick="openEditor('${tpl.id}')" class="flex-1 py-2 bg-wedding-charcoal-dark hover:bg-wedding-charcoal-light text-white text-xs font-extrabold rounded-xl shadow-xs flex items-center justify-center gap-1.5 transition-all duration-300 transform hover:-translate-y-0.5 shrink-0">
                <i data-lucide="palette" class="w-3.5 h-3.5 text-wedding-pink-dark"></i> Design
            </button>`;
        }

        let iconActionsHTML = '';
        if (hasPermission('templates.edit') || hasPermission('templates.publish') || hasPermission('templates.create') || hasPermission('templates.delete')) {
            iconActionsHTML = `<div class="flex items-center gap-1">`;
            if (hasPermission('templates.edit')) {
                iconActionsHTML += `<button onclick='openEditModal(${JSON.stringify(tpl).replace(/'/g, "&apos;")})' class="p-2 text-wedding-charcoal-light hover:text-wedding-pink-dark hover:bg-wedding-pink-light/50 rounded-lg transition-colors border border-wedding-pink-medium/10" title="Edit Template Details"><i data-lucide="edit-3" class="w-3.5 h-3.5"></i></button>`;
            }
            if (hasPermission('templates.publish')) {
                const activeClass = tpl.isActive
                    ? 'border-amber-200 text-amber-600 bg-amber-50/50 hover:bg-amber-100/50'
                    : 'border-green-200 text-green-600 bg-green-50/50 hover:bg-green-100/50';
                const toggleIcon = tpl.isActive ? 'eye-off' : 'eye';
                const toggleTitle = tpl.isActive ? 'Revert to Draft' : 'Publish to Live';
                iconActionsHTML += `<button onclick="handleToggleState('${tpl.id}', ${tpl.isActive})" class="p-2 rounded-lg border transition-all duration-200 ${activeClass}" title="${toggleTitle}"><i data-lucide="${toggleIcon}" class="w-3.5 h-3.5"></i></button>`;
            }
            if (hasPermission('templates.create')) {
                iconActionsHTML += `<button onclick="handleDuplicate('${tpl.id}')" class="p-2 text-wedding-charcoal-light hover:text-wedding-pink-dark hover:bg-wedding-pink-light/50 rounded-lg transition-colors border border-wedding-pink-medium/10" title="Clone Template"><i data-lucide="copy" class="w-3.5 h-3.5"></i></button>`;
                iconActionsHTML += `<button onclick='handleExport(${JSON.stringify(tpl).replace(/'/g, "&apos;")})' class="p-2 text-wedding-charcoal-light hover:text-wedding-pink-dark hover:bg-wedding-pink-light/50 rounded-lg transition-colors border border-wedding-pink-medium/10" title="Export Template JSON"><i data-lucide="download" class="w-3.5 h-3.5"></i></button>`;
            }
            if (hasPermission('templates.delete')) {
                iconActionsHTML += `<button onclick="handleDelete('${tpl.id}')" class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors border border-transparent hover:border-red-100" title="Delete Template"><i data-lucide="trash-2" class="w-3.5 h-3.5"></i></button>`;
            }
            iconActionsHTML += `</div>`;
        }

        const card = document.createElement('div');
        card.className = 'group bg-wedding-card border border-wedding-pink-medium/10 rounded-3xl overflow-hidden shadow-md hover:shadow-xl transition-all duration-300 flex flex-col justify-between';
        card.innerHTML = `
            <div class="aspect-[2/3] w-full bg-gray-50 border-b border-wedding-pink-medium/20 relative overflow-hidden flex items-center justify-center">
                ${thumbUrl
                    ? `<img src="${escapeHtml(thumbUrl)}" alt="${escapeHtml(tpl.name)}" loading="lazy" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">`
                    : `<div class="w-full h-full flex items-center justify-center bg-wedding-pink-light/20 text-wedding-pink-dark text-xs font-bold">No Image</div>`
                }
                <div class="absolute left-3 top-3 flex flex-wrap gap-1 max-w-[calc(100%-24px)]">
                    ${badgesHTML}
                    ${liveStatus}
                </div>
                ${designBtn}
            </div>
            <div class="p-4 space-y-3 bg-wedding-card">
                <div class="space-y-0.5">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-bold text-wedding-pink-dark uppercase tracking-wider">${escapeHtml(catName)}</span>
                        <span class="text-[10px] font-bold text-wedding-pink-dark bg-wedding-pink-light border border-wedding-pink-medium/20 px-2 py-0.5 rounded-md font-mono shadow-xs">${(tpl.pages || []).length} pages</span>
                    </div>
                    <h4 class="text-sm font-extrabold text-wedding-charcoal-dark truncate" title="${escapeHtml(tpl.name)}">${escapeHtml(tpl.name)}</h4>
                    <p class="text-[10px] text-gray-500 font-mono truncate">${escapeHtml(tpl.slug)}</p>
                </div>
                <div class="flex items-center justify-between gap-2 pt-2 border-t border-wedding-pink-medium/15">
                    ${footerActionHTML}
                    ${iconActionsHTML}
                </div>
            </div>
        `;
        grid.appendChild(card);
    });

    // Only create icons for new cards to improve performance
    lucide.createIcons({ 
        attr: 'data-lucide',
        name: 'lucide',
        nodes: Array.from(grid.querySelectorAll('[data-lucide]')) 
    });
}

// ═══ OPEN EDITOR ═════════════════════════════════════════════
function openEditor(templateId) {
    window.location.href = `/admin/templates/editor/${templateId}`;
}

// ═══ MODAL OPERATIONS ════════════════════════════════════════
function populateModalSelectors() {
    // Fonts selector
    const fontsEl = document.getElementById('fonts-selector');
    fontsEl.innerHTML = '';
    allFonts.forEach(f => {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.dataset.family = f.family;
        btn.className = `px-4 py-2 rounded-xl text-xs font-bold transition-all border bg-wedding-bg border-wedding-pink-medium/55 text-wedding-charcoal-light/95 hover:bg-wedding-pink-light/35 hover:border-wedding-pink-medium/80`;
        btn.textContent = f.family;
        btn.onclick = () => toggleFont(f.family, btn);
        fontsEl.appendChild(btn);
    });

    // Languages selector
    const langsEl = document.getElementById('languages-selector');
    langsEl.innerHTML = '';
    allLanguages.forEach(l => {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.dataset.lang = l.name;
        btn.className = `px-4 py-2 rounded-xl text-xs font-bold transition-all border bg-wedding-bg border-wedding-pink-medium/55 text-wedding-charcoal-light/95 hover:bg-wedding-pink-light/35 hover:border-wedding-pink-medium/80`;
        btn.textContent = l.name;
        btn.onclick = () => toggleLang(l.name, btn);
        langsEl.appendChild(btn);
    });

    // Category dropdown
    const catSel = document.getElementById('tpl-category');
    catSel.innerHTML = '<option value="">Select Category</option>';
    allCategories.forEach(c => {
        const opt = document.createElement('option');
        opt.value = c.id;
        opt.textContent = c.name;
        catSel.appendChild(opt);
    });

    // Plans selector
    const plansEl = document.getElementById('plans-selector');
    plansEl.innerHTML = '';
    const activePlans = allPlans.filter(p => p.isActive);
    if (activePlans.length === 0) {
        plansEl.innerHTML = '<span class="text-xs text-gray-400 font-medium p-1">No active plans available.</span>';
    } else {
        activePlans.forEach(plan => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.dataset.planId = plan.id;
            btn.className = `px-3 py-1.5 rounded-xl text-[11px] font-bold transition-all border bg-wedding-bg border-wedding-pink-medium/55 text-wedding-charcoal-light/95 hover:bg-wedding-pink-light/35 hover:border-wedding-pink-medium/80`;
            btn.textContent = plan.name;
            btn.onclick = () => togglePlan(plan.id, btn);
            plansEl.appendChild(btn);
        });
    }
}

function toggleFont(family, btn) {
    if (selectedFonts.includes(family)) {
        selectedFonts = selectedFonts.filter(f => f !== family);
        btn.className = 'px-4 py-2 rounded-xl text-xs font-bold transition-all border bg-wedding-bg border-wedding-pink-medium/55 text-wedding-charcoal-light/95 hover:bg-wedding-pink-light/35 hover:border-wedding-pink-medium/80';
    } else {
        selectedFonts.push(family);
        btn.className = 'px-4 py-2 rounded-xl text-xs font-bold transition-all border bg-wedding-pink-light border-wedding-pink-dark text-wedding-pink-dark shadow-xs';
    }
}

function toggleLang(lang, btn) {
    if (selectedLangs.includes(lang)) {
        selectedLangs = selectedLangs.filter(l => l !== lang);
        btn.className = 'px-4 py-2 rounded-xl text-xs font-bold transition-all border bg-wedding-bg border-wedding-pink-medium/55 text-wedding-charcoal-light/95 hover:bg-wedding-pink-light/35 hover:border-wedding-pink-medium/80';
    } else {
        selectedLangs.push(lang);
        btn.className = 'px-4 py-2 rounded-xl text-xs font-bold transition-all border bg-wedding-pink-light border-wedding-pink-dark text-wedding-pink-dark shadow-xs';
    }
}

function togglePlan(planId, btn) {
    if (selectedPlanIds.includes(planId)) {
        selectedPlanIds = selectedPlanIds.filter(id => id !== planId);
        btn.className = 'px-3 py-1.5 rounded-xl text-[11px] font-bold transition-all border bg-wedding-bg border-wedding-pink-medium/55 text-wedding-charcoal-light/95 hover:bg-wedding-pink-light/35 hover:border-wedding-pink-medium/80';
    } else {
        selectedPlanIds.push(planId);
        btn.className = 'px-3 py-1.5 rounded-xl text-[11px] font-bold transition-all border bg-blue-50 border-blue-500 text-blue-700 font-black shadow-xs';
    }
}

function syncSelectorUI(containerSelector, selectedArr, dataAttr, activeClass, inactiveClass) {
    document.querySelectorAll(containerSelector).forEach(btn => {
        const val = btn.dataset[dataAttr];
        if (selectedArr.includes(val)) {
            btn.className = activeClass;
        } else {
            btn.className = inactiveClass;
        }
    });
}

function openAddModal() {
    document.getElementById('editing-id').value = '';
    document.getElementById('modal-title').innerHTML = '<i data-lucide="palette" class="w-5 h-5 text-wedding-pink-medium"></i> Initialize Wedding Template';
    document.getElementById('tpl-name').value = '';
    document.getElementById('tpl-slug').value = '';
    if (allCategories.length > 0) document.getElementById('tpl-category').value = allCategories[0].id;
    document.getElementById('tpl-premium').checked = true;
    document.getElementById('premium-label').textContent = 'Premium Lock';
    document.getElementById('tpl-active').checked = true;
    document.getElementById('active-label').textContent = 'Published';
    document.getElementById('tpl-price').value = '49';
    document.getElementById('pricing-section').style.display = 'block';
    document.getElementById('bg-upload-section').style.display = 'block';
    document.getElementById('submit-btn').textContent = 'Generate Template';

    selectedFonts = allFonts.slice(0, 2).map(f => f.family);
    selectedLangs = allLanguages.slice(0, 3).map(l => l.name);
    selectedPlanIds = ['monthly', 'yearly'];

    populateModalSelectors();
    refreshSelectorHighlights();

    hideAllErrors();
    document.getElementById('thumbnail-text').textContent = 'Choose Thumbnail File';
    document.getElementById('bg-text').textContent = 'Select Page Backgrounds';
    document.getElementById('thumbnail-file').value = '';
    document.getElementById('bg-files').value = '';

    lucide.createIcons({ nodeList: [document.getElementById('template-modal')] });
    document.getElementById('template-modal').classList.remove('hidden');
}

function openEditModal(tpl) {
    document.getElementById('editing-id').value = tpl.id;
    document.getElementById('modal-title').innerHTML = '<i data-lucide="edit-3" class="w-5 h-5 text-wedding-pink-medium"></i> Edit Template Details';
    document.getElementById('tpl-name').value = tpl.name || '';
    document.getElementById('tpl-slug').value = tpl.slug || '';

    // Premium / Active toggles
    const isPremium = tpl.isPremium === true || tpl.isPremium === 1;
    const isActive  = tpl.isActive  === true || tpl.isActive  === 1;
    document.getElementById('tpl-premium').checked = isPremium;
    document.getElementById('premium-label').textContent = isPremium ? 'Premium Lock' : 'Free Access';
    document.getElementById('tpl-active').checked = isActive;
    document.getElementById('active-label').textContent = isActive ? 'Published' : 'Hidden Draft';
    document.getElementById('tpl-price').value = tpl.singlePurchasePrice ?? 49;
    document.getElementById('pricing-section').style.display = isPremium ? 'block' : 'none';
    document.getElementById('bg-upload-section').style.display = 'none';
    document.getElementById('submit-btn').textContent = 'Update Details';

    // Prepare selection state BEFORE populateModalSelectors
    const activeNames = allLanguages.map(l => l.name);
    selectedFonts = Array.isArray(tpl.fonts) ? [...tpl.fonts] : [];
    selectedLangs = (tpl.languages || []).filter(lang => activeNames.includes(lang) || lang === 'English');

    selectedPlanIds = [];
    if (tpl.includedInMonthlyPlan !== false) selectedPlanIds.push('monthly');
    if (tpl.includedInYearlyPlan !== false) selectedPlanIds.push('yearly');
    allPlans.forEach(p => {
        if (p.id !== 'monthly' && p.id !== 'yearly' && p.includedTemplateIds && p.includedTemplateIds.includes(tpl.id)) {
            selectedPlanIds.push(p.id);
        }
    });

    // Rebuild dropdown options first, THEN set the selected values
    populateModalSelectors();
    refreshSelectorHighlights();

    // Set category AFTER dropdown is rebuilt (otherwise it gets reset)
    if (tpl.categoryId) {
        document.getElementById('tpl-category').value = tpl.categoryId;
    }

    // Show existing thumbnail if available
    const thumbUrl = tpl.thumbnail ? getImageUrl(tpl.thumbnail) : null;
    if (thumbUrl) {
        document.getElementById('thumbnail-text').innerHTML =
            `<img src="${thumbUrl}" alt="Current thumbnail" class="w-16 h-16 object-cover rounded-lg mx-auto mb-1"><span class="block text-[10px] text-gray-500">Click to change</span>`;
    } else {
        document.getElementById('thumbnail-text').textContent = 'Choose Thumbnail File';
    }
    document.getElementById('thumbnail-file').value = '';

    lucide.createIcons({ nodeList: [document.getElementById('template-modal')] });
    document.getElementById('template-modal').classList.remove('hidden');
}

function refreshSelectorHighlights() {
    // Fonts
    document.querySelectorAll('#fonts-selector button').forEach(btn => {
        const active = selectedFonts.includes(btn.dataset.family);
        btn.className = `px-4 py-2 rounded-xl text-xs font-bold transition-all border ${active
            ? 'bg-wedding-pink-light border-wedding-pink-dark text-wedding-pink-dark shadow-xs'
            : 'bg-wedding-bg border-wedding-pink-medium/55 text-wedding-charcoal-light/95 hover:bg-wedding-pink-light/35 hover:border-wedding-pink-medium/80'}`;
    });
    // Languages
    document.querySelectorAll('#languages-selector button').forEach(btn => {
        const active = selectedLangs.includes(btn.dataset.lang);
        btn.className = `px-4 py-2 rounded-xl text-xs font-bold transition-all border ${active
            ? 'bg-wedding-pink-light border-wedding-pink-dark text-wedding-pink-dark shadow-xs'
            : 'bg-wedding-bg border-wedding-pink-medium/55 text-wedding-charcoal-light/95 hover:bg-wedding-pink-light/35 hover:border-wedding-pink-medium/80'}`;
    });
    // Plans
    document.querySelectorAll('#plans-selector button').forEach(btn => {
        const active = selectedPlanIds.includes(btn.dataset.planId);
        btn.className = `px-3 py-1.5 rounded-xl text-[11px] font-bold transition-all border ${active
            ? 'bg-blue-50 border-blue-500 text-blue-700 font-black shadow-xs'
            : 'bg-wedding-bg border-wedding-pink-medium/55 text-wedding-charcoal-light/95 hover:bg-wedding-pink-light/35 hover:border-wedding-pink-medium/80'}`;
    });
}

function closeModal() {
    document.getElementById('template-modal').classList.add('hidden');
}

// ═══ FILE HANDLERS ═══════════════════════════════════════════
function handleThumbnailSelect(input) {
    const file = input.files[0];
    if (file) {
        document.getElementById('thumbnail-text').textContent = file.name;
        document.getElementById('error-thumbnail').classList.add('hidden');
    }
}

function handleBgSelect(input) {
    if (input.files && input.files.length > 0) {
        document.getElementById('bg-text').textContent = `${input.files.length} files selected`;
        document.getElementById('error-bg').classList.add('hidden');
    }
}

// ═══ FORM SUBMIT ═════════════════════════════════════════════
function handleNameInput(val) {
    const editingId = document.getElementById('editing-id').value;
    if (!editingId) {
        const slug = val.toLowerCase().replace(/[^a-z0-9_]/g, '_').replace(/_+/g, '_');
        document.getElementById('tpl-slug').value = slug;
    }
    document.getElementById('error-name').classList.add('hidden');
    document.getElementById('error-slug').classList.add('hidden');
}

function validateForm() {
    hideAllErrors();
    let valid = true;
    const name = document.getElementById('tpl-name').value.trim();
    const slug = document.getElementById('tpl-slug').value.trim();
    const catId = document.getElementById('tpl-category').value;
    const isPremium = document.getElementById('tpl-premium').checked;
    const editingId = document.getElementById('editing-id').value;

    if (!name) { showError('error-name', 'Template name is required.'); valid = false; }
    if (!slug) { showError('error-slug', 'Slug is required.'); valid = false; }
    else if (!/^[a-z0-9_]+$/.test(slug)) { showError('error-slug', 'Slug can only contain lowercase letters, numbers, and underscores.'); valid = false; }
    if (!catId) { showError('error-category', 'Category selection is required.'); valid = false; }
    if (isPremium) {
        const price = parseInt(document.getElementById('tpl-price').value);
        if (isNaN(price) || price < 0) { showError('error-price', 'Single purchase price must be a non-negative number.'); valid = false; }
    }
    if (!editingId) {
        if (!document.getElementById('thumbnail-file').files[0]) { showError('error-thumbnail', 'Thumbnail file is required for new templates.'); valid = false; }
        if (!document.getElementById('bg-files').files || document.getElementById('bg-files').files.length === 0) { showError('error-bg', 'At least one page background file is required.'); valid = false; }
    }

    return valid;
}

function showError(id, msg) {
    const el = document.getElementById(id);
    el.textContent = msg;
    el.classList.remove('hidden');
}

function hideAllErrors() {
    ['error-name', 'error-slug', 'error-category', 'error-price', 'error-thumbnail', 'error-bg'].forEach(id => {
        document.getElementById(id).classList.add('hidden');
    });
}

async function handleFormSubmit(e) {
    e.preventDefault();
    if (!validateForm()) {
        Toast.show('Please resolve the errors in the form.', 'warning');
        return;
    }

    const editingId = document.getElementById('editing-id').value;
    if (editingId && !hasPermission('templates.edit')) {
        Toast.show('Access Denied. You lack the "templates.edit" permission.', 'warning'); return;
    }
    if (!editingId && !hasPermission('templates.create')) {
        Toast.show('Access Denied. You lack the "templates.create" permission.', 'warning'); return;
    }

    const submitBtn = document.getElementById('submit-btn');
    submitBtn.disabled = true;
    submitBtn.textContent = 'Processing...';
    isUploading = true;

    try {
        const name = document.getElementById('tpl-name').value.trim();
        const slug = document.getElementById('tpl-slug').value.trim();
        const catId = document.getElementById('tpl-category').value;
        const isPremium = document.getElementById('tpl-premium').checked;
        const isActive = document.getElementById('tpl-active').checked;
        const price = parseInt(document.getElementById('tpl-price').value) || 0;
        const selectedCategory = allCategories.find(c => c.id === catId);
        const catSlug = selectedCategory ? selectedCategory.slug : 'uncategorized';
        const userId = window.CurrentUser ? window.CurrentUser.id : 'admin_super';

        let finalThumbnail = '';
        let finalBgs = [];

        // Upload thumbnail
        const thumbFile = document.getElementById('thumbnail-file').files[0];
        if (thumbFile) {
            submitBtn.textContent = 'Uploading thumbnail...';
            const thumbData = new FormData();
            thumbData.append('file', thumbFile);
            const resThumb = await fetch(`/api/uploads/single?type=template&categorySlug=${catSlug}&templateSlug=${slug}`, {
                method: 'POST',
                headers: { 'x-user-id': userId },
                body: thumbData
            });
            const thumbJson = await resThumb.json();
            if (thumbJson.success) finalThumbnail = thumbJson.filePath;
        }

        // Upload backgrounds
        const bgFiles = document.getElementById('bg-files').files;
        if (bgFiles && bgFiles.length > 0) {
            submitBtn.textContent = 'Uploading backgrounds...';
            const bgData = new FormData();
            for (let i = 0; i < bgFiles.length; i++) bgData.append('files', bgFiles[i]);
            const resBg = await fetch(`/api/uploads/multiple?type=template&categorySlug=${catSlug}&templateSlug=${slug}`, {
                method: 'POST',
                headers: { 'x-user-id': userId },
                body: bgData
            });
            const bgJson = await resBg.json();
            if (bgJson.success) finalBgs = bgJson.files.map(f => f.filePath);
        }

        // Build pages from backgrounds (generic - non-wedding default)
        let initialPages = finalBgs.map((bgUrl, index) => ({
            id: `page_${Math.random().toString(36).substr(2, 9)}`,
            name: index === 0 ? 'Cover Page' : index === 1 ? 'Ceremony Page' : `Details Page ${index}`,
            backgroundImage: bgUrl,
            elements: [{
                id: `elem_${Math.random().toString(36).substr(2, 9)}`,
                type: 'text',
                x: 100, y: 400, width: 880, height: 100,
                rotation: 0, opacity: 1, zIndex: 1, isLocked: false,
                text: index === 0 ? 'INVITATION' : 'Join Us',
                fontFamily: selectedFonts[0] || 'Rasa',
                fontSize: 48, color: '#D4AF37', lineHeight: 1.2, alignment: 'center',
                translations: { English: index === 0 ? 'INVITATION' : 'Join Us' }
            }]
        }));

        if (initialPages.length === 0 && !editingId) {
            initialPages.push({
                id: `page_blank_${Math.random().toString(36).substr(2, 9)}`,
                name: 'Cover Page', backgroundImage: '',
                elements: [{
                    id: `elem_blank_${Math.random().toString(36).substr(2, 9)}`,
                    type: 'text',
                    x: 100, y: 400, width: 880, height: 100,
                    rotation: 0, opacity: 1, zIndex: 1, isLocked: false,
                    text: 'Double click to edit text', fontFamily: 'Rasa',
                    fontSize: 36, color: '#4A2E35', lineHeight: 1.2, alignment: 'center',
                    translations: { English: 'Double click to edit text' }
                }]
            });
        }

        const localAssetPaths = [
            finalThumbnail.replace(/^\//, ''),
            ...finalBgs.map(bg => bg.replace(/^\//, ''))
        ].filter(Boolean);

        const payload = editingId ? {
            categoryId: catId, name, slug, isPremium, isActive,
            fonts: selectedFonts, languages: selectedLangs,
            singlePurchasePrice: price,
            includedInMonthlyPlan: selectedPlanIds.includes('monthly'),
            includedInYearlyPlan: selectedPlanIds.includes('yearly'),
            ...(finalThumbnail && { thumbnail: finalThumbnail }),
            ...(finalThumbnail && { thumbnailUrl: finalThumbnail })
        } : {
            categoryId: catId, name, slug,
            thumbnail: finalThumbnail || '',
            thumbnailUrl: finalThumbnail || '',
            previewImages: finalBgs,
            localAssetPaths,
            isPremium, isActive,
            fonts: selectedFonts, languages: selectedLangs,
            pages: initialPages,
            singlePurchasePrice: price,
            includedInMonthlyPlan: selectedPlanIds.includes('monthly'),
            includedInYearlyPlan: selectedPlanIds.includes('yearly')
        };

        submitBtn.textContent = editingId ? 'Updating...' : 'Saving template...';

        const url = editingId ? `/api/templates/${editingId}` : `/api/templates`;
        const method = editingId ? 'PUT' : 'POST';
        const res = await fetch(url, { method, headers, body: JSON.stringify(payload) });

        if (res.ok) {
            const savedTemplate = await res.json();
            const templateId = savedTemplate.id || editingId;

            // Update plan inclusions
            if (templateId) {
                await Promise.all(allPlans.map(async plan => {
                    const isPlanSelected = selectedPlanIds.includes(plan.id);
                    const currentTemplateIds = plan.includedTemplateIds || [];
                    const isAlreadyIn = currentTemplateIds.includes(templateId);
                    let newTemplateIds = [...currentTemplateIds];
                    if (isPlanSelected && !isAlreadyIn) newTemplateIds.push(templateId);
                    else if (!isPlanSelected && isAlreadyIn) newTemplateIds = newTemplateIds.filter(id => id !== templateId);
                    else return;
                    await fetch(`/api/subscriptions/${plan.id}`, {
                        method: 'PUT', headers,
                        body: JSON.stringify({ ...plan, includedTemplateIds: newTemplateIds })
                    });
                }));
            }

            closeModal();
            await fetchAllData();
            Toast.show(editingId ? 'Template updated successfully!' : 'Template created successfully!', 'success');
        } else {
            const err = await res.json();
            Toast.show(err.error || 'Save failed', 'error');
        }
    } catch (err) {
        console.error('Submit template error:', err);
        Toast.show('Error saving template.', 'error');
    } finally {
        isUploading = false;
        submitBtn.disabled = false;
        submitBtn.textContent = document.getElementById('editing-id').value ? 'Update Details' : 'Generate Template';
    }
}

// ═══ CRUD ACTIONS ═════════════════════════════════════════════
async function handleDuplicate(id) {
    if (!hasPermission('templates.create')) {
        Toast.show('Access Denied. You lack the "templates.create" permission.', 'warning'); return;
    }
    try {
        const res = await fetch(`/api/templates/${id}/duplicate`, {
            method: 'POST',
            headers: { 'x-user-id': window.CurrentUser ? window.CurrentUser.id : 'admin_super' }
        });
        if (res.ok) {
            await fetchAllData();
            Toast.show('Template duplicated successfully!', 'success');
        } else {
            const err = await res.json();
            Toast.show(err.error || 'Failed to duplicate template.', 'error');
        }
    } catch (err) {
        Toast.show('Failed to duplicate template.', 'error');
    }
}

async function handleDelete(id) {
    if (!hasPermission('templates.delete')) {
        Toast.show('Access Denied. You lack the "templates.delete" permission.', 'warning'); return;
    }
    if (!confirm('Are you sure you want to delete this template? All designed card pages and elements will be lost.')) return;
    try {
        const res = await fetch(`/api/templates/${id}`, {
            method: 'DELETE',
            headers: { 'x-user-id': window.CurrentUser ? window.CurrentUser.id : 'admin_super' }
        });
        if (res.ok) {
            await fetchAllData();
            Toast.show('Template deleted successfully!', 'success');
        } else {
            const err = await res.json();
            Toast.show(err.error || 'Failed to delete template.', 'error');
        }
    } catch (err) {
        Toast.show('Failed to delete template.', 'error');
    }
}

async function handleToggleState(id, currentState) {
    // Optimistic update
    const tpl = allTemplates.find(t => t.id === id);
    if (tpl) tpl.isActive = !currentState;
    renderTemplates();

    try {
        if (!hasPermission('templates.publish')) {
            if (tpl) tpl.isActive = currentState;
            renderTemplates();
            Toast.show('Access Denied. You lack the "templates.publish" permission.', 'warning');
            return;
        }
        const res = await fetch(`/api/templates/${id}`, {
            method: 'PUT', headers,
            body: JSON.stringify({ isActive: !currentState })
        });
        if (res.ok) {
            Toast.show('Template status updated successfully!', 'success');
        } else {
            if (tpl) tpl.isActive = currentState;
            renderTemplates();
            const err = await res.json();
            Toast.show(err.error || 'Failed to toggle status.', 'error');
        }
    } catch (err) {
        if (tpl) tpl.isActive = currentState;
        renderTemplates();
        Toast.show('Network error. Failed to toggle status.', 'error');
    }
}

function handleExport(tpl) {
    try {
        const exportData = {
            name: tpl.name, slug: tpl.slug, categoryId: tpl.categoryId,
            isPremium: tpl.isPremium, isActive: tpl.isActive,
            fonts: tpl.fonts || [], languages: tpl.languages || [],
            thumbnail: tpl.thumbnail || tpl.thumbnailUrl || '',
            thumbnailUrl: tpl.thumbnailUrl || tpl.thumbnail || '',
            previewImages: tpl.previewImages || [],
            localAssetPaths: tpl.localAssetPaths || [], pages: tpl.pages || [],
            singlePurchasePrice: tpl.singlePurchasePrice || 49,
            includedInMonthlyPlan: tpl.includedInMonthlyPlan !== false,
            includedInYearlyPlan: tpl.includedInYearlyPlan !== false
        };
        const blob = new Blob([JSON.stringify(exportData, null, 2)], { type: 'application/json' });
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = `${tpl.slug || 'template'}.json`;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        URL.revokeObjectURL(url);
        Toast.show(`Template "${tpl.name}" exported successfully!`, 'success');
    } catch (err) {
        Toast.show('Failed to export template.', 'error');
    }
}

// ═══ JSON IMPORT ══════════════════════════════════════════════
function handleJsonImport(e) {
    const file = e.target.files[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = async (event) => {
        try {
            const imported = JSON.parse(event.target.result);
            if (!imported.name || !imported.pages) {
                Toast.show('Invalid template JSON file. Must contain name and pages.', 'error'); return;
            }

            let targetCategoryId = imported.categoryId;
            if (!allCategories.some(c => c.id === targetCategoryId)) {
                targetCategoryId = allCategories[0]?.id || '';
            }

            let targetSlug = imported.slug || imported.name.toLowerCase().replace(/[^a-z0-9_]/g, '_');
            const isConflict = allTemplates.some(t => t.slug === targetSlug);
            if (isConflict) targetSlug = `${targetSlug}_import_${Date.now().toString().slice(-4)}`;

            const payload = {
                categoryId: targetCategoryId,
                name: isConflict ? `${imported.name} (Imported)` : imported.name,
                slug: targetSlug,
                thumbnail: imported.thumbnail || imported.thumbnailUrl || '',
                thumbnailUrl: imported.thumbnailUrl || imported.thumbnail || '',
                previewImages: imported.previewImages || [],
                localAssetPaths: imported.localAssetPaths || [],
                isPremium: imported.isPremium === true,
                isActive: imported.isActive !== false,
                fonts: imported.fonts || [],
                languages: imported.languages || [],
                pages: imported.pages || [],
                singlePurchasePrice: imported.singlePurchasePrice !== undefined ? Number(imported.singlePurchasePrice) : 49,
                includedInMonthlyPlan: imported.includedInMonthlyPlan !== false,
                includedInYearlyPlan: imported.includedInYearlyPlan !== false
            };

            Toast.show('Importing template...', 'info');

            const response = await fetch(`/api/templates`, {
                method: 'POST', headers,
                body: JSON.stringify(payload)
            });

            if (!response.ok) {
                const errData = await response.json();
                throw new Error(errData.error || 'Failed to save imported template.');
            }

            const saved = await response.json();
            Toast.show(`Template "${saved.name}" imported successfully!`, 'success');
            await fetchAllData();
        } catch (err) {
            Toast.show(`Import failed: ${err.message}`, 'error');
        }
    };
    reader.readAsText(file);
    e.target.value = '';
}

// ═══ UTILS ═══════════════════════════════════════════════════
function escapeHtml(str) {
    if (!str) return '';
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#039;');
}

window.addEventListener('resize', checkScrollable);
</script>
@endpush
