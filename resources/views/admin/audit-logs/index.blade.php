@extends('admin.layouts.app')

@section('title', 'Audit Logs')
@section('header_title', 'Audit Trails & Security Logs')

@php $activeTab = 'audit-logs'; @endphp

@section('content')
<div class="space-y-4 sm:space-y-6 animate-fadeIn">
    <!-- Upper header section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-lg sm:text-xl lg:text-2xl font-extrabold text-wedding-charcoal-dark font-sans tracking-wide">
                AUDIT TRAILS & SECURITY LOGS
            </h2>
            <p class="text-[10px] sm:text-xs text-gray-500 font-semibold mt-1">
                Real-time tracking of platform configuration alterations and administrative changes.
            </p>
        </div>

        <button
            onclick="loadLogs()"
            class="flex items-center justify-center gap-2 px-4 py-2.5 bg-white border border-wedding-pink-medium/20 hover:border-wedding-pink-medium/40 hover:bg-gray-50 text-wedding-charcoal-dark text-xs font-bold rounded-xl transition-all shadow-xs w-full sm:w-auto"
        >
            <i data-lucide="refresh-cw" id="refresh-icon" class="w-3.5 h-3.5"></i>
            Refresh Audit Trails
        </button>
    </div>

    <!-- Filter Options Bar -->
    <div class="bg-white rounded-[20px] sm:rounded-[24px] border border-wedding-pink-medium/10 p-4 sm:p-5 shadow-[0_8px_30px_rgba(0,0,0,0.015)] grid grid-cols-1 sm:grid-cols-12 gap-3 sm:gap-4 items-center">
        <!-- Search Input -->
        <div class="relative sm:col-span-8">
            <i data-lucide="search" class="w-4 h-4 text-gray-400 absolute left-4 top-1/2 transform -translate-y-1/2"></i>
            <input
                type="text"
                id="search-query"
                oninput="handleFilterChange()"
                placeholder="Search logs by administrator email, action statement, resource name..."
                class="w-full pl-12 pr-4 py-2.5 sm:py-3 bg-[#FFF5F6]/30 border border-[#FFCAD2]/55 rounded-xl sm:rounded-2xl text-wedding-charcoal-dark placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-wedding-pink-dark/25 focus:bg-white text-xs sm:text-sm font-semibold transition-all"
            />
        </div>

        <!-- Resource Selection Dropdown -->
        <div class="relative sm:col-span-4">
            <i data-lucide="filter" class="w-4 h-4 text-gray-400 absolute left-4 top-1/2 transform -translate-y-1/2 pointer-events-none"></i>
            <select
                id="resource-filter"
                onchange="handleFilterChange()"
                class="w-full pl-11 pr-8 py-2.5 sm:py-3 bg-[#FFF5F6]/30 border border-[#FFCAD2]/55 rounded-xl sm:rounded-2xl text-wedding-charcoal-dark focus:outline-none focus:ring-2 focus:ring-wedding-pink-dark/25 focus:bg-white text-xs sm:text-sm font-semibold transition-all appearance-none cursor-pointer"
            >
                <option value="all">All Modules</option>
            </select>
            <i data-lucide="chevron-down" class="w-4 h-4 text-gray-400 absolute right-4 top-1/2 transform -translate-y-1/2 pointer-events-none"></i>
        </div>
    </div>

    <!-- Main Logs Table Card -->
    <div class="bg-white rounded-[20px] sm:rounded-[28px] border border-wedding-pink-medium/10 shadow-[0_8px_30px_rgba(0,0,0,0.02)] overflow-hidden">
        <div id="logs-loading" class="flex flex-col items-center justify-center py-16 sm:py-20 gap-3">
            <div class="animate-spin rounded-full h-8 w-8 sm:h-10 sm:w-10 border-b-2 border-wedding-pink-dark"></div>
            <p class="text-[10px] sm:text-xs text-gray-400 font-bold">Querying system log registry...</p>
        </div>

        <div id="logs-empty" class="text-center py-16 sm:py-20 space-y-3 px-4 hidden">
            <div class="w-12 h-12 sm:w-16 sm:h-16 rounded-full bg-gray-50 flex items-center justify-center mx-auto text-gray-400 border border-gray-100">
                <i data-lucide="scroll" class="w-6 h-6 sm:w-8 sm:h-8"></i>
            </div>
            <h4 class="text-xs sm:text-sm font-bold text-wedding-charcoal-dark uppercase">No Audit Logs Match Filter</h4>
            <p class="text-[10px] sm:text-xs text-gray-400 max-w-xs mx-auto leading-relaxed">
                We couldn't locate any events matching your active keyword queries or selected system module filters.
            </p>
        </div>

        <div id="table-container" class="overflow-x-auto -mx-4 sm:mx-0 px-4 sm:px-0 hidden">
            <table class="w-full text-left border-collapse text-xs sm:text-sm">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100">
                        <th class="px-3 sm:px-6 py-3 sm:py-4.5 text-[8px] sm:text-[10px] font-black text-gray-500 uppercase tracking-wider">User Account</th>
                        <th class="px-3 sm:px-6 py-3 sm:py-4.5 text-[8px] sm:text-[10px] font-black text-gray-500 uppercase tracking-wider hidden sm:table-cell">Resource Class</th>
                        <th class="px-3 sm:px-6 py-3 sm:py-4.5 text-[8px] sm:text-[10px] font-black text-gray-500 uppercase tracking-wider">Action Executed</th>
                        <th class="px-3 sm:px-6 py-3 sm:py-4.5 text-[8px] sm:text-[10px] font-black text-gray-500 uppercase tracking-wider">Timestamp</th>
                    </tr>
                </thead>
                <tbody id="logs-list-body" class="divide-y divide-gray-100">
                    <!-- Dynamic Logs -->
                </tbody>
            </table>
        </div>

        <!-- Elegant Pagination Footer -->
        <div id="pagination-footer" class="bg-gray-50/50 px-4 sm:px-6 py-3 sm:py-4 flex flex-col sm:flex-row items-center justify-between gap-3 sm:gap-0 border-t border-gray-100 hidden">
            <span id="pagination-info" class="text-[8px] sm:text-[10px] text-gray-400 font-extrabold uppercase">
                Showing 0 - 0 of 0 Records
            </span>
            <div class="flex items-center gap-2">
                <button
                    onclick="handlePageChange(currentPage - 1)"
                    id="btn-prev-page"
                    class="p-1.5 sm:p-2 bg-white border border-gray-200 hover:bg-gray-50 text-gray-600 rounded-lg sm:rounded-xl transition-all disabled:opacity-50 disabled:pointer-events-none"
                >
                    <i data-lucide="chevron-left" class="w-3.5 h-3.5"></i>
                </button>

                <div class="flex items-center gap-1" id="pages-container">
                    <!-- Dynamic Page Buttons -->
                </div>

                <button
                    onclick="handlePageChange(currentPage + 1)"
                    id="btn-next-page"
                    class="p-1.5 sm:p-2 bg-white border border-gray-200 hover:bg-gray-50 text-gray-600 rounded-lg sm:rounded-xl transition-all disabled:opacity-50 disabled:pointer-events-none"
                >
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let logsList = [];
    let filteredLogs = [];
    let currentPage = 1;
    const itemsPerPage = 12;

    const headers = {
        'x-user-id': window.CurrentUser ? window.CurrentUser.id : 'admin_super',
        'Content-Type': 'application/json'
    };

    document.addEventListener('DOMContentLoaded', () => {
        loadLogs();
    });

    async function loadLogs() {
        document.getElementById('logs-loading').classList.remove('hidden');
        document.getElementById('table-container').classList.add('hidden');
        document.getElementById('logs-empty').classList.add('hidden');
        document.getElementById('pagination-footer').classList.add('hidden');
        
        const refreshIcon = document.getElementById('refresh-icon');
        if (refreshIcon) refreshIcon.classList.add('animate-spin');

        try {
            const res = await fetch(`/api/audit-logs`, { headers });
            if (!res.ok) throw new Error('Failed to retrieve system audit trails.');

            const data = await res.json();
            if (Array.isArray(data)) {
                logsList = data.map(log => {
                    let dateStr = log.date || '';
                    let timeStr = log.time || '';

                    if (!dateStr || !timeStr) {
                        const dateObj = new Date(log.createdAt);
                        if (!isNaN(dateObj.getTime())) {
                            const day = String(dateObj.getDate()).padStart(2, '0');
                            const month = String(dateObj.getMonth() + 1).padStart(2, '0');
                            const year = dateObj.getFullYear();
                            dateStr = `${day}/${month}/${year}`;
                            timeStr = dateObj.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
                        }
                    }

                    return {
                        ...log,
                        user: log.user || (log.userId === 'anonymous' ? 'Anonymous User' : log.userId || 'System'),
                        userId: log.userId || 'system',
                        action: log.action || log.description || 'Performed general action',
                        resource: log.resource || log.type || 'General',
                        date: dateStr,
                        time: timeStr
                    };
                });

                populateResourceFilterDropdown();
                handleFilterChange();
            }
        } catch (e) {
            console.error(e);
            Toast.show('Error loading audit logs.', 'error');
        } finally {
            document.getElementById('logs-loading').classList.add('hidden');
            if (refreshIcon) refreshIcon.classList.remove('animate-spin');
        }
    }

    function populateResourceFilterDropdown() {
        const uniqueResources = Array.from(new Set(logsList.map(log => log.resource).filter(Boolean))).sort();
        const select = document.getElementById('resource-filter');
        select.innerHTML = '<option value="all">All Modules</option>';
        uniqueResources.forEach(res => {
            const opt = document.createElement('option');
            opt.value = res.toLowerCase();
            opt.innerText = res;
            select.appendChild(opt);
        });
    }

    function handleFilterChange() {
        const query = document.getElementById('search-query').value.toLowerCase().trim();
        const resourceFilterVal = document.getElementById('resource-filter').value;

        filteredLogs = logsList.filter(log => {
            const matchesSearch = 
                (log.user && log.user.toLowerCase().includes(query)) ||
                (log.action && log.action.toLowerCase().includes(query)) ||
                (log.resource && log.resource.toLowerCase().includes(query));

            const matchesResource = resourceFilterVal === 'all' || (log.resource && log.resource.toLowerCase() === resourceFilterVal);

            return matchesSearch && matchesResource;
        });

        currentPage = 1;
        renderLogs();
    }

    function getResourceBadgeStyle(resource) {
        const res = (resource || '').toLowerCase();
        if (res.includes('role') || res.includes('permission')) return 'bg-purple-50 text-purple-700 border-purple-100';
        if (res.includes('user')) return 'bg-emerald-50 text-emerald-700 border-emerald-100';
        if (res.includes('template')) return 'bg-blue-50 text-blue-700 border-blue-100';
        if (res.includes('category')) return 'bg-rose-50 text-rose-700 border-rose-100';
        if (res.includes('font') || res.includes('typography')) return 'bg-amber-50 text-amber-700 border-amber-100';
        if (res.includes('subscription')) return 'bg-indigo-50 text-indigo-700 border-indigo-100';
        if (res.includes('setting')) return 'bg-sky-50 text-sky-700 border-sky-100';
        return 'bg-gray-50 text-gray-700 border-gray-100';
    }

    function renderLogs() {
        const tbody = document.getElementById('logs-list-body');
        const emptyDiv = document.getElementById('logs-empty');
        const tableContainer = document.getElementById('table-container');
        const paginationFooter = document.getElementById('pagination-footer');

        if (filteredLogs.length === 0) {
            emptyDiv.classList.remove('hidden');
            tableContainer.classList.add('hidden');
            paginationFooter.classList.add('hidden');
            return;
        }

        emptyDiv.classList.add('hidden');
        tableContainer.classList.remove('hidden');

        // Pagination indices
        const totalPages = Math.ceil(filteredLogs.length / itemsPerPage);
        const startIndex = (currentPage - 1) * itemsPerPage;
        const paginated = filteredLogs.slice(startIndex, startIndex + itemsPerPage);

        let html = '';
        paginated.forEach(log => {
            const badgeStyle = getResourceBadgeStyle(log.resource);
            html += `
                <tr class="hover:bg-gray-50/70 transition-colors">
                    <td class="px-3 sm:px-6 py-3 sm:py-4">
                        <div class="flex items-center gap-2 sm:gap-3">
                            <div class="w-7 h-7 sm:w-8.5 sm:h-8.5 rounded-full bg-wedding-pink-dark/10 text-wedding-pink-dark flex items-center justify-center font-bold text-[10px] sm:text-xs border border-wedding-pink-medium/10 shrink-0">
                                ${(log.user || 'System').slice(0, 2).toUpperCase()}
                            </div>
                            <div class="min-w-0">
                                <p class="text-[10px] sm:text-xs font-extrabold text-wedding-charcoal-dark truncate">${log.user}</p>
                                <p class="text-[8px] sm:text-[9px] text-gray-400 font-semibold font-mono mt-0.5 hidden sm:block">${log.userId}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-3 sm:px-6 py-3 sm:py-4 hidden sm:table-cell">
                        <span class="inline-flex items-center gap-1 px-2 sm:px-2.5 py-1 text-[8px] sm:text-[9px] font-black border rounded-lg uppercase tracking-wide ${badgeStyle}">
                            <i data-lucide="tag" class="w-2.5 h-2.5"></i>
                            ${log.resource}
                        </span>
                    </td>
                    <td class="px-3 sm:px-6 py-3 sm:py-4">
                        <p class="text-[10px] sm:text-xs font-semibold text-wedding-charcoal-light leading-relaxed max-w-[150px] sm:max-w-md line-clamp-2">
                            ${log.action}
                        </p>
                    </td>
                    <td class="px-3 sm:px-6 py-3 sm:py-4">
                        <div class="space-y-1 text-right sm:text-left">
                            <div class="flex items-center gap-1 sm:gap-1.5 text-[9px] sm:text-[10px] font-bold text-gray-500">
                                <i data-lucide="calendar" class="w-3.5 h-3.5 text-gray-300"></i>
                                <span>${log.date}</span>
                            </div>
                            <div class="flex items-center gap-1 sm:gap-1.5 text-[8px] sm:text-[9px] font-semibold text-gray-400">
                                <i data-lucide="clock" class="w-3.5 h-3.5 text-gray-300"></i>
                                <span>${log.time}</span>
                            </div>
                        </div>
                    </td>
                </tr>
            `;
        });

        tbody.innerHTML = html;
        lucide.createIcons({ nodeList: [tbody] });

        // Render Pagination Info & Buttons
        if (totalPages > 1) {
            paginationFooter.classList.remove('hidden');
            document.getElementById('pagination-info').innerText = `Showing ${startIndex + 1} - ${Math.min(startIndex + itemsPerPage, filteredLogs.length)} of ${filteredLogs.length} Records`;
            
            document.getElementById('btn-prev-page').disabled = currentPage === 1;
            document.getElementById('btn-next-page').disabled = currentPage === totalPages;

            const pagesContainer = document.getElementById('pages-container');
            pagesContainer.innerHTML = '';
            
            // Build simple page list with dots
            const range = [];
            const delta = 2;
            for (let i = 1; i <= totalPages; i++) {
                if (i === 1 || i === totalPages || (i >= currentPage - delta && i <= currentPage + delta)) {
                    range.push(i);
                }
            }

            let l;
            range.forEach(i => {
                if (l !== undefined) {
                    if (i - l === 2) {
                        pagesContainer.appendChild(createPageButton(l + 1));
                    } else if (i - l > 2) {
                        const dots = document.createElement('span');
                        dots.className = 'w-7 h-7 sm:w-8.5 sm:h-8.5 flex items-center justify-center text-[10px] sm:text-xs font-bold text-gray-400';
                        dots.innerText = '...';
                        pagesContainer.appendChild(dots);
                    }
                }
                pagesContainer.appendChild(createPageButton(i, i === currentPage));
                l = i;
            });
            lucide.createIcons({ nodeList: [paginationFooter] });
        } else {
            paginationFooter.classList.add('hidden');
        }
    }

    function createPageButton(pNum, isCurrent = false) {
        const btn = document.createElement('button');
        btn.onclick = () => handlePageChange(pNum);
        btn.className = `w-7 h-7 sm:w-8.5 sm:h-8.5 rounded-lg sm:rounded-xl text-[10px] sm:text-xs font-bold transition-all border ${
            isCurrent
                ? 'bg-wedding-charcoal-dark border-wedding-charcoal-dark text-wedding-gold-light font-black shadow-xs'
                : 'bg-white border-gray-200 hover:bg-gray-50 text-gray-600'
        }`;
        btn.innerText = pNum;
        return btn;
    }

    function handlePageChange(page) {
        const totalPages = Math.ceil(filteredLogs.length / itemsPerPage);
        if (page >= 1 && page <= totalPages) {
            currentPage = page;
            renderLogs();
        }
    }
</script>
@endpush
