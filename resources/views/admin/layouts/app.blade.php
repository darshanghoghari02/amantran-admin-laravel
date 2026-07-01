<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Amantran CMS Admin') - Amantran CMS</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Playfair+Display:ital,wght@0,400..900;1,400..900&display=swap" rel="stylesheet">
    
    <!-- Lucide Icons CDN -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- Tailwind Styles and Scripts -->
    @php
        $pathPrefix = request()->getHost() === '127.0.0.1' || request()->getHost() === 'localhost' ? '' : '/public';
    @endphp
    <link rel="stylesheet" href="{{ $pathPrefix }}/css/app.css">
    <script src="{{ $pathPrefix }}/js/app.js" defer></script>
    
    <!-- Additional Styles -->
    @stack('styles')
</head>
<body class="bg-wedding-bg text-[#161112] font-sans antialiased overflow-hidden">

    @php
        $user = session('admin_user');
        $roleId = $user['roleId'] ?? $user['role'] ?? 'viewer';
        $permissions = $user['permissions'] ?? [];
        $activeTab = $activeTab ?? 'dashboard';

        $menuItems = [
            ['id' => 'dashboard', 'name' => 'Dashboard', 'icon' => 'layout-dashboard', 'permission' => 'dashboard.view', 'route' => 'admin.dashboard'],
            ['id' => 'templates', 'name' => 'Templates', 'icon' => 'palette', 'permission' => 'templates.view', 'route' => 'admin.templates'],
            ['id' => 'categories', 'name' => 'Categories', 'icon' => 'folder-heart', 'permission' => 'categories.view', 'route' => 'admin.categories'],
            ['id' => 'fonts', 'name' => 'Typography & Fonts', 'icon' => 'type', 'permission' => 'fonts.view', 'route' => 'admin.fonts'],
            ['id' => 'languages', 'name' => 'Languages', 'icon' => 'languages', 'permission' => 'languages.view', 'route' => 'admin.languages'],
            ['id' => 'subscriptions', 'name' => 'Subscription Settings', 'icon' => 'sparkles', 'permission' => 'subscriptions.view', 'route' => 'admin.subscriptions'],
            ['id' => 'users', 'name' => 'User Management', 'icon' => 'users', 'permission' => 'users.view', 'route' => 'admin.users'],
            ['id' => 'roles', 'name' => 'Role & Permissions', 'icon' => 'settings', 'permission' => 'roles.view', 'route' => 'admin.roles'],
            ['id' => 'audit-logs', 'name' => 'Audit Logs', 'icon' => 'scroll', 'permission' => 'roles.view', 'route' => 'admin.audit-logs'],
            ['id' => 'settings', 'name' => 'Settings', 'icon' => 'sliders', 'permission' => 'settings.view', 'route' => 'admin.settings'],
        ];

        $filteredMenu = array_filter($menuItems, function($item) use ($roleId, $permissions) {
            if ($roleId === 'super_admin') return true;
            if (in_array('*', $permissions)) return true;
            return in_array($item['permission'], $permissions);
        });

        // Resolve initials
        $name = $user['displayName'] ?? $user['name'] ?? 'Super Admin';
        $parts = preg_split('/\s+/', trim($name));
        $initials = 'AD';
        if (count($parts) >= 2) {
            $initials = strtoupper(substr($parts[0], 0, 1) . substr($parts[1], 0, 1));
        } else {
            $initials = strtoupper(substr($name, 0, 2));
        }

        $roleLabels = [
            'super_admin' => 'Super Admin',
            'admin' => 'Admin',
            'content_manager' => 'Content Manager',
            'subscription_manager' => 'Subscription Manager',
            'editor' => 'Editor',
            'user' => 'Standard User'
        ];
        $roleLabel = $roleLabels[$roleId] ?? ucwords(str_replace('_', ' ', $roleId));
    @endphp

    <div class="flex h-screen overflow-hidden bg-wedding-bg relative">
        
        <!-- Sidebar Navigation -->
        <aside id="admin-sidebar" class="w-72 bg-wedding-charcoal-dark border-r border-wedding-pink-medium/10 flex flex-col justify-between text-white shrink-0 fixed inset-y-0 left-0 z-50 md:static transform -translate-x-full transition-transform duration-300 ease-in-out md:translate-x-0 shadow-xl">
            <div>
                <!-- Logo Title -->
                <div class="p-8 border-b border-wedding-pink-medium/10 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-[#ff6b81] to-wedding-pink-dark flex items-center justify-center shadow-lg shadow-wedding-pink-medium/30">
                            <i data-lucide="heart" class="w-5 h-5 text-white fill-white animate-pulse"></i>
                        </div>
                        <div>
                            <h1 class="font-extrabold text-lg tracking-wider text-white uppercase">AMANTRAN</h1>
                            <p class="text-[10px] text-wedding-pink-dark uppercase tracking-widest font-bold mt-0.5">Invitation Card Maker</p>
                        </div>
                    </div>
                    <button type="button" onclick="toggleSidebar()" class="md:hidden text-gray-400 hover:text-white p-2 hover:bg-wedding-pink-light/10 rounded-xl transition-colors">
                        ✕
                    </button>
                </div>

                <!-- Navigation Menu Links -->
                <nav class="p-4 space-y-1 overflow-y-auto max-h-[calc(100vh-230px)]">
                    @foreach($filteredMenu as $item)
                        @php $isActive = $activeTab === $item['id']; @endphp
                        <a href="{{ route($item['route']) }}" class="group w-full flex items-center gap-4 px-4 py-3.5 rounded-xl text-sm font-bold transition-all duration-300 {{ $isActive ? 'bg-gradient-to-r from-wedding-pink-dark to-[#ff6b81] text-white border-l-4 border-white shadow-lg shadow-wedding-pink-dark/20 scale-[1.02]' : 'text-gray-400 hover:bg-wedding-pink-light/10 hover:text-white hover:pl-6' }}">
                            <i data-lucide="{{ $item['icon'] }}" class="w-5 h-5 transition-transform duration-300 {{ $isActive ? 'text-white scale-110' : 'text-gray-500 group-hover:text-wedding-pink-dark group-hover:scale-105' }}"></i>
                            <span>{{ $item['name'] }}</span>
                        </a>
                    @endforeach
                </nav>
            </div>

            <!-- Profile Footer -->
            <div class="p-4 border-t border-wedding-pink-medium/10">
                <div class="flex items-center gap-3 p-3 bg-wedding-charcoal-light/35 border border-wedding-pink-medium/10 rounded-2xl mb-3">
                    <div class="w-9 h-9 rounded-full bg-wedding-pink-dark flex items-center justify-center font-bold text-white text-xs select-none border border-wedding-pink-medium/30">
                        {{ $initials }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-bold text-white truncate">{{ $name }}</p>
                        <p class="text-[9px] font-extrabold text-wedding-pink-dark uppercase tracking-wider mb-0.5">{{ $roleLabel }}</p>
                        <p class="text-[10px] text-gray-400 truncate">{{ $user['email'] ?? 'admin@amantran.com' }}</p>
                    </div>
                </div>
                
                <form action="{{ route('admin.logout') }}" method="POST" class="m-0">
                    @csrf
                    <button type="submit" class="w-full flex items-center gap-4 px-4 py-3 text-red-400 hover:bg-red-950/20 hover:text-red-300 rounded-xl text-sm font-bold transition-all duration-300">
                        <i data-lucide="log-out" class="w-5 h-5"></i>
                        Logout
                    </button>
                </form>
            </div>
        </aside>

        <!-- Sidebar mobile overlay -->
        <div id="sidebar-overlay" onclick="toggleSidebar()" class="fixed inset-0 bg-wedding-charcoal-dark/50 backdrop-blur-xs z-40 hidden md:hidden"></div>

        <!-- Main screen area -->
        <div class="flex-1 flex flex-col overflow-hidden w-full">
            
            <!-- Topbar Header -->
            <header class="h-20 bg-white border-b border-wedding-pink-medium/40 px-6 sm:px-8 flex items-center justify-between shadow-sm shrink-0">
                <div class="flex items-center gap-4 min-w-0">
                    <button type="button" onclick="toggleSidebar()" class="md:hidden p-2 hover:bg-wedding-pink-light/60 text-wedding-charcoal-dark hover:text-wedding-pink-dark rounded-xl transition-colors border border-wedding-pink-medium/20 shadow-xs flex items-center justify-center shrink-0">
                        <i data-lucide="menu" class="w-5 h-5"></i>
                    </button>
                    <div class="min-w-0">
                        <h2 class="text-base sm:text-xl font-extrabold text-wedding-charcoal-dark tracking-tight truncate">
                            @yield('header_title')
                        </h2>
                        <p class="text-[10px] sm:text-xs text-gray-500 font-semibold truncate">Amantran Invitation App CMS</p>
                    </div>
                </div>

                <div class="flex items-center gap-2 sm:gap-4 shrink-0 select-none">
                    <span class="flex items-center gap-1.5 px-3 py-1.5 bg-amber-50 text-amber-700 text-xs font-bold rounded-full border border-amber-200 shadow-sm">
                        <i data-lucide="cloud-off" class="w-3.5 h-3.5 shrink-0"></i>
                        <span class="hidden sm:inline">MySQL Database</span>
                    </span>
                    <span class="flex items-center gap-1.5 px-3 py-1.5 bg-wedding-pink-light text-wedding-pink-dark text-xs font-bold rounded-full border border-wedding-pink-medium/40">
                        <i data-lucide="refresh-cw" class="w-3.5 h-3.5 text-wedding-pink-dark shrink-0"></i>
                        <span class="hidden sm:inline">Auto-Sync Connected</span>
                    </span>
                </div>
            </header>

            <!-- Dynamic Content Body -->
            <main class="flex-1 p-6 sm:p-8 overflow-y-auto bg-wedding-bg">
                @yield('content')
            </main>
        </div>
    </div>

    <!-- Toast Notifications container -->
    <div id="toast-container" class="fixed bottom-5 right-5 z-50 flex flex-col gap-2"></div>

    <script>
        // Init Lucide icons
        lucide.createIcons();

        // Sidebar toggle
        function toggleSidebar() {
            const sidebar = document.getElementById('admin-sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            if (sidebar.classList.contains('-translate-x-full')) {
                sidebar.classList.remove('-translate-x-full');
                overlay.classList.remove('hidden');
            } else {
                sidebar.classList.add('-translate-x-full');
                overlay.classList.add('hidden');
            }
        }

        // Global Toast Notification Helper
        const Toast = {
            show(message, type = 'success') {
                const container = document.getElementById('toast-container');
                const toast = document.createElement('div');
                
                let bgClass = 'bg-white border-green-200 text-green-800';
                let icon = 'check-circle';
                if (type === 'error') {
                    bgClass = 'bg-white border-red-200 text-red-800';
                    icon = 'alert-circle';
                } else if (type === 'warning') {
                    bgClass = 'bg-white border-amber-200 text-amber-800';
                    icon = 'alert-triangle';
                }

                toast.className = `flex items-center gap-3 px-4 py-3 rounded-2xl border shadow-lg ${bgClass} animate-slideUp transition-all duration-300`;
                toast.innerHTML = `
                    <i data-lucide="${icon}" class="w-5 h-5 shrink-0"></i>
                    <span class="text-xs font-bold">${message}</span>
                `;
                
                container.appendChild(toast);
                lucide.createIcons({nodeList: [toast]});

                setTimeout(() => {
                    toast.classList.add('opacity-0', 'translate-y-2');
                    setTimeout(() => toast.remove(), 300);
                }, 4000);
            }
        };

        // Access current user context easily from pages JS
        window.CurrentUser = @json($user);
    </script>
    @stack('scripts')
</body>
</html>
