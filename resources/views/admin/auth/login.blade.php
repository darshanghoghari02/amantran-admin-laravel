<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - Amantran Admin CMS</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Lucide CDN -->
    <script src="https://unpkg.com/lucide@latest"></script>

    @php
        $pathPrefix = request()->getHost() === '127.0.0.1' || request()->getHost() === 'localhost' ? '' : '/public';
    @endphp
    <link rel="stylesheet" href="{{ $pathPrefix }}/css/app.css">
    <script src="{{ $pathPrefix }}/js/app.js" defer></script>
</head>
<body class="min-h-screen bg-[#FFF0F2] flex items-center justify-center p-4 relative overflow-hidden font-sans antialiased">
    
    <!-- Floating concentric design circles -->
    <div class="absolute top-[-10%] left-[-10%] w-[45%] aspect-square rounded-full border border-[#FFCAD2]/30 pointer-events-none z-0"></div>
    <div class="absolute top-[-5%] left-[-5%] w-[33%] aspect-square rounded-full border border-[#FFCAD2]/45 pointer-events-none z-0"></div>
    <div class="absolute top-[0%] left-[0%] w-[22%] aspect-square rounded-full border border-[#FFCAD2]/55 pointer-events-none z-0"></div>

    <!-- Concentric rings at the bottom right -->
    <div class="absolute bottom-[-15%] right-[-15%] w-[50%] aspect-square rounded-full border border-[#FFCAD2]/25 pointer-events-none z-0"></div>
    <div class="absolute bottom-[-8%] right-[-8%] w-[35%] aspect-square rounded-full border border-[#FFCAD2]/35 pointer-events-none z-0"></div>

    <!-- Layered wave curves at the bottom -->
    <div class="absolute bottom-0 left-0 right-0 w-full overflow-hidden leading-none z-0 pointer-events-none">
        <svg class="relative block w-full h-[150px] md:h-[220px]" viewBox="0 0 1200 120" preserveAspectRatio="none">
            <path d="M0,0 C150,90 350,120 600,80 C850,40 1050,100 1200,20 L1200,120 L0,120 Z" fill="#FFAEC0" opacity="0.3"></path>
            <path d="M0,40 C180,100 320,40 600,90 C880,140 1020,30 1200,60 L1200,120 L0,120 Z" fill="#FF7FA0" opacity="0.4"></path>
            <path d="M0,70 C200,110 400,60 700,100 C1000,140 1100,80 1200,100 L1200,120 L0,120 Z" fill="#FF3E5C" opacity="0.65"></path>
        </svg>
    </div>

    <!-- Dotted grid layouts -->
    <div class="absolute top-[20%] right-[10%] opacity-20 hidden md:block z-0 pointer-events-none">
        <div class="grid grid-cols-6 gap-3">
            @for ($i = 0; $i < 30; $i++)
                <div class="w-1.5 h-1.5 rounded-full bg-wedding-pink-dark"></div>
            @endfor
        </div>
    </div>
    <div class="absolute bottom-[20%] left-[5%] opacity-20 hidden md:block z-0 pointer-events-none">
        <div class="grid grid-cols-6 gap-3">
            @for ($i = 0; $i < 30; $i++)
                <div class="w-1.5 h-1.5 rounded-full bg-wedding-pink-dark"></div>
            @endfor
        </div>
    </div>

    <!-- Small floating outline rings -->
    <div class="absolute top-[25%] left-[20%] w-5 h-5 rounded-full border-[2.5px] border-[#FF3E5C]/35 z-0"></div>
    <div class="absolute top-[20%] right-[25%] w-6 h-6 rounded-full border-[2.5px] border-[#FF3E5C]/35 z-0"></div>

    <!-- Central auth card -->
    <div class="w-full max-w-md bg-white p-8 sm:p-10 rounded-[36px] shadow-[0_20px_60px_-15px_rgba(255,62,92,0.14)] space-y-6 z-10 border border-wedding-pink-medium/10 animate-slideUp">
        
        <!-- Logo Heading -->
        <div class="flex flex-col items-center text-center space-y-3">
            <div class="w-16 h-16 rounded-[22px] bg-[#FF3E5C] flex items-center justify-center shadow-lg shadow-wedding-pink-dark/20 transition-transform duration-300 hover:scale-105">
                <i data-lucide="heart" class="w-8 h-8 text-white fill-white"></i>
            </div>
            <div>
                <h1 class="font-extrabold text-2xl tracking-wide text-wedding-charcoal-dark uppercase">
                    AMANTRAN <span class="text-wedding-pink-dark">ADMIN</span>
                </h1>
                <p class="text-xs text-gray-500 font-semibold mt-1">Professional Invitation CMS Portal</p>
            </div>
        </div>

        <!-- Divider with heart -->
        <div class="flex items-center justify-center gap-3 py-1">
            <div class="h-[1px] w-20 bg-gradient-to-r from-transparent to-wedding-pink-medium/40"></div>
            <i data-lucide="heart" class="w-3 h-3 text-[#FF3E5C] fill-[#FF3E5C]"></i>
            <div class="h-[1px] w-20 bg-gradient-to-l from-transparent to-wedding-pink-medium/40"></div>
        </div>

        <!-- Login Form -->
        <form action="{{ route('admin.login.submit') }}" method="POST" class="space-y-5" onsubmit="onSubmitLogin()">
            @csrf

            <!-- Errors Alert -->
            @if ($errors->any())
                <div class="p-3.5 bg-red-50 text-red-600 text-xs font-semibold rounded-2xl border border-red-200">
                    ✕ {{ $errors->first() }}
                </div>
            @endif

            @if (session('success'))
                <div class="p-3.5 bg-green-50 text-green-600 text-xs font-semibold rounded-2xl border border-green-200">
                    ✓ {{ session('success') }}
                </div>
            @endif

            <div class="space-y-1.5">
                <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block">Administrator Email</label>
                <div class="relative">
                    <i data-lucide="mail" class="w-4 h-4 text-wedding-pink-dark absolute left-4 top-1/2 transform -translate-y-1/2"></i>
                    <input
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        placeholder="admin@gmail.com"
                        class="w-full pl-12 pr-4 py-3 bg-[#FFF5F6] border border-[#FFCAD2] rounded-2xl text-wedding-charcoal-dark placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-wedding-pink-dark/30 focus:bg-white text-sm font-semibold transition-all"
                        required
                    />
                </div>
            </div>

            <div class="space-y-1.5">
                <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block">Security Password</label>
                <div class="relative">
                    <i data-lucide="lock" class="w-4 h-4 text-wedding-pink-dark absolute left-4 top-1/2 transform -translate-y-1/2"></i>
                    <input
                        id="password-input"
                        type="password"
                        name="password"
                        placeholder="••••••••"
                        class="w-full pl-12 pr-12 py-3 bg-[#FFF5F6] border border-[#FFCAD2] rounded-2xl text-wedding-charcoal-dark placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-wedding-pink-dark/30 focus:bg-white text-sm font-semibold transition-all"
                        required
                    />
                    <button
                        type="button"
                        onclick="togglePassword()"
                        class="absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 focus:outline-none"
                    >
                        <i id="password-toggle-icon" data-lucide="eye" class="w-4 h-4"></i>
                    </button>
                </div>
            </div>

            <button
                id="submit-btn"
                type="submit"
                class="w-full py-3.5 bg-gradient-to-r from-[#FF3E5C] to-[#FF6B81] hover:from-[#E62E47] hover:to-[#FF526E] text-white font-extrabold text-sm rounded-2xl shadow-lg shadow-wedding-pink-dark/20 transition-all duration-300 transform hover:-translate-y-0.5 flex items-center justify-center gap-2"
            >
                <i data-lucide="log-in" class="w-4 h-4 text-white"></i>
                <span id="btn-text">Sign In to Dashboard</span>
            </button>
        </form>
    </div>

    <script>
        lucide.createIcons();

        function togglePassword() {
            const pwdInput = document.getElementById('password-input');
            const icon = document.getElementById('password-toggle-icon');
            if (pwdInput.type === 'password') {
                pwdInput.type = 'text';
                icon.setAttribute('data-lucide', 'eye-off');
            } else {
                pwdInput.type = 'password';
                icon.setAttribute('data-lucide', 'eye');
            }
            lucide.createIcons({ nodeList: [icon] });
        }

        function onSubmitLogin() {
            const btn = document.getElementById('submit-btn');
            const text = document.getElementById('btn-text');
            btn.disabled = true;
            btn.classList.add('opacity-70');
            text.innerText = 'Authenticating...';
        }
    </script>
</body>
</html>
