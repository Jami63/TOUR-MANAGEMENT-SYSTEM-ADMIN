<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>{{ $title ?? 'Tour Manager Console – SafarFlow' }}</title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Manrope:wght@600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        primary: "#00685b",
                        "primary-container": "#008373",
                        "on-primary": "#ffffff",
                        "primary-fixed": "#83f6e0",
                        secondary: "#4d5f7d",
                        "secondary-container": "#c8dbfe",
                        "on-secondary-fixed": "#071c36",
                        tertiary: "#a3371c",
                        "tertiary-container": "#c44f32",
                        surface: "#f8f9ff",
                        "surface-container-lowest": "#ffffff",
                        "surface-container-low": "#eff4ff",
                        "surface-container": "#e5eeff",
                        "on-surface": "#0b1c30",
                        "on-surface-variant": "#3d4946",
                        outline: "#6d7a76",
                        "outline-variant": "#bcc9c5",
                        error: "#ba1a1a",
                        background: "#f8f9ff"
                    },
                    fontFamily: {
                        display: ["Manrope", "sans-serif"],
                        body: ["Inter", "sans-serif"]
                    }
                }
            }
        };
    </script>
    <style>
        @layer base {
            html, body { margin: 0; padding: 0; }
            body { font-family: 'Inter', sans-serif; }
            h1, h2, h3, h4, .font-display { font-family: 'Manrope', sans-serif; }
        }
    </style>
</head>
<body class="bg-background text-on-surface antialiased">
    <!-- Sidebar Navigation (Tour Manager Management Console) -->
    <aside class="fixed left-0 top-0 h-full w-[72px] lg:w-[264px] bg-[#0B1F3A] z-50 flex flex-col justify-between transition-all duration-300 shadow-xl">
        <div class="flex flex-col">
            <!-- Brand Logo -->
            <div class="h-[72px] px-4 lg:px-6 flex items-center justify-between border-b border-white/10">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-primary flex items-center justify-center relative flex-shrink-0 shadow-sm">
                        <span class="material-symbols-outlined text-white text-[22px]">explore</span>
                    </div>
                    <div class="hidden lg:flex flex-col">
                        <span class="font-display text-lg font-bold tracking-tight text-white leading-none">SafarFlow</span>
                        <span class="text-[10px] text-primary-fixed tracking-wider uppercase mt-1">Tour Manager</span>
                    </div>
                </div>
            </div>

            <!-- Role Badge -->
            <div class="hidden lg:block px-6 my-4">
                <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/10 text-primary-fixed text-xs tracking-wide border border-white/15">
                    <span class="w-1.5 h-1.5 rounded-full bg-teal-400"></span>
                    Tour Manager Console
                </div>
            </div>

            <!-- Navigation Links -->
            <nav class="flex flex-col gap-1 px-2 lg:px-3 pt-2 text-sm">
                <!-- My Tours -->
                <a href="{{ route('manager.dashboard') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('manager.dashboard') || (request()->routeIs('manager.tours.*') && !request()->routeIs('manager.tours.create')) ? 'bg-primary text-white shadow-sm font-semibold' : 'text-outline-variant hover:bg-white/5 hover:text-white' }} transition-colors">
                    <span class="material-symbols-outlined text-[20px]">luggage</span>
                    <span class="hidden lg:inline truncate">My Expeditions</span>
                </a>

                <!-- Create Tour -->
                <a href="{{ route('manager.tours.create') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('manager.tours.create') ? 'bg-primary text-white shadow-sm font-semibold' : 'text-outline-variant hover:bg-white/5 hover:text-white' }} transition-colors">
                    <span class="material-symbols-outlined text-[20px]">add_circle</span>
                    <span class="hidden lg:inline truncate">Create New Tour</span>
                </a>
            </nav>
        </div>

        <!-- Logout Action -->
        <div class="p-3 border-t border-white/10">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-outline-variant hover:bg-error/20 hover:text-red-300 transition-colors group text-sm cursor-pointer">
                    <span class="material-symbols-outlined text-[20px]">logout</span>
                    <span class="hidden lg:inline truncate font-medium">Logout</span>
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Container -->
    <div class="pl-[72px] lg:pl-[264px] min-h-screen flex flex-col">
        <!-- Top Header -->
        <header class="sticky top-0 z-40 h-[72px] bg-white/90 backdrop-blur-xl border-b border-outline-variant/30 px-4 lg:px-8">
            <div class="h-full max-w-[1440px] mx-auto flex items-center justify-between gap-4">
                <div class="flex items-center gap-2">
                    <span class="text-xs font-semibold text-secondary uppercase tracking-wider">Expedition Management</span>
                    <span class="w-1.5 h-1.5 rounded-full bg-teal-500"></span>
                    <span class="text-xs text-outline font-medium">Tour Manager Workspace</span>
                </div>

                <!-- Manager User Info & Quick Action -->
                <div class="flex items-center gap-3">
                    <a href="{{ route('manager.tours.create') }}" class="hidden sm:inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-primary text-white text-xs font-bold hover:bg-primary-container transition-all shadow-sm">
                        <span class="material-symbols-outlined text-[16px]">add</span>
                        <span>New Tour</span>
                    </a>

                    <div class="flex items-center gap-2.5 p-1 rounded-full bg-surface-container-low px-3 py-1.5 border border-outline-variant/30">
                        <div class="w-7 h-7 rounded-full bg-[#00685b] flex items-center justify-center text-white font-bold text-xs">
                            {{ strtoupper(substr(Auth::user()->full_name, 0, 2)) }}
                        </div>
                        <div class="flex flex-col text-left">
                            <span class="text-xs font-bold text-on-surface leading-tight">{{ Auth::user()->full_name }}</span>
                            <span class="text-[10px] text-primary font-semibold">Tour Manager</span>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Flash Alerts -->
        @if (session('success'))
            <div class="max-w-[1440px] mx-auto w-full px-4 lg:px-8 pt-4">
                <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs sm:text-sm flex items-center gap-2 shadow-sm">
                    <span class="material-symbols-outlined text-[20px] text-emerald-600">check_circle</span>
                    <span>{{ session('success') }}</span>
                </div>
            </div>
        @endif

        @if (session('error'))
            <div class="max-w-[1440px] mx-auto w-full px-4 lg:px-8 pt-4">
                <div class="p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 text-xs sm:text-sm flex items-center gap-2 shadow-sm">
                    <span class="material-symbols-outlined text-[20px] text-red-600">error</span>
                    <span>{{ session('error') }}</span>
                </div>
            </div>
        @endif

        <!-- Main Content -->
        <main class="flex-1 w-full bg-surface py-6 px-4 lg:px-8">
            <div class="max-w-[1440px] mx-auto w-full">
                @yield('content')
            </div>
        </main>
    </div>
</body>
</html>
