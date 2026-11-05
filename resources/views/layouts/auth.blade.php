<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>{{ $title ?? 'SafarFly – Tour & Expense Management' }}</title>
    
    <!-- Fonts -->
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
                        "on-secondary-fixed": "#071c36",
                        tertiary: "#a3371c",
                        "tertiary-container": "#c44f32",
                        "on-tertiary-container": "#fffbff",
                        "tertiary-fixed": "#ffdad2",
                        "tertiary-fixed-dim": "#ffb4a2",
                        surface: "#f8f9ff",
                        "surface-bright": "#f8f9ff",
                        "surface-container-lowest": "#ffffff",
                        "surface-container-low": "#eff4ff",
                        "surface-container": "#e5eeff",
                        "surface-container-high": "#dce9ff",
                        "on-surface": "#0b1c30",
                        "on-surface-variant": "#3d4946",
                        outline: "#6d7a76",
                        "outline-variant": "#bcc9c5",
                        error: "#ba1a1a",
                        "error-container": "#ffdad6"
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
<body class="bg-[#F7F5F1] text-on-surface antialiased">
    <div class="min-h-screen w-full flex flex-col lg:flex-row">
        <!-- Left Side: Brand & Visuals -->
        <div class="w-full lg:w-1/2 bg-gradient-to-br from-[#0B1F3A] to-[#12305A] text-on-primary relative overflow-hidden flex flex-col justify-between p-6 sm:p-8 lg:p-12 shrink-0">
            <!-- Topographic Line Pattern -->
            <svg class="absolute inset-0 w-full h-full pointer-events-none opacity-20" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <pattern height="120" id="topo-lines" patternUnits="userSpaceOnUse" width="120">
                        <path d="M0 40 Q30 20 60 40 T120 40 M0 80 Q30 60 60 80 T120 80 M0 120 Q30 100 60 120 T120 120" fill="none" stroke="currentColor" stroke-width="1.5"></path>
                    </pattern>
                </defs>
                <rect fill="url(#topo-lines)" height="100%" width="100%"></rect>
            </svg>

            <!-- Top Header Navigation -->
            <div class="relative z-10 flex items-center justify-between w-full">
                <div class="flex items-center gap-3">
                    <div class="relative flex items-center justify-center w-10 h-10 rounded-xl bg-primary shadow-md">
                        <span class="material-symbols-outlined text-white text-[22px]">near_me</span>
                        <span class="absolute -top-1 -right-1 w-3.5 h-3.5 rounded-full bg-[#FF7A59] border-2 border-[#0B1F3A]"></span>
                    </div>
                    <span class="font-display text-2xl text-white tracking-tight font-extrabold">SafarFly</span>
                </div>
                <div class="text-xs uppercase tracking-wider text-primary-fixed bg-white/10 px-3 py-1 rounded-full border border-white/10">
                    Tour & Expense Management
                </div>
            </div>

            <!-- Center Content Showcase -->
            <div class="relative z-10 flex flex-col my-auto py-8 max-w-lg">
                <!-- Circuit Waypoints -->
                <div class="mb-6 p-3 rounded-2xl bg-white/10 backdrop-blur-md border border-white/20 shadow-sm">
                    <div class="flex items-center justify-between px-2 text-[12px] font-semibold tracking-wide text-primary-fixed uppercase mb-2">
                        <span>Expedition Circuit</span>
                        <span class="text-white/70 lowercase font-normal">3 waypoints</span>
                    </div>
                    <div class="flex items-center justify-between relative px-2">
                        <div class="absolute left-4 right-4 top-1/2 -translate-y-1/2 h-[2px] border-b-2 border-dashed border-primary-fixed/60 z-0"></div>
                        <div class="relative z-10 flex items-center gap-1.5 bg-[#0B1F3A]/90 px-2 py-0.5 rounded-full border border-primary-fixed/40 text-[12px] font-semibold text-white">
                            <span class="w-2 h-2 rounded-full bg-primary-fixed"></span> Sylhet
                        </div>
                        <div class="relative z-10 flex items-center gap-1.5 bg-[#0B1F3A]/90 px-2 py-0.5 rounded-full border border-primary-fixed/40 text-[12px] font-semibold text-white">
                            <span class="w-2 h-2 rounded-full bg-primary-fixed animate-pulse"></span> Ratargul
                        </div>
                        <div class="relative z-10 flex items-center gap-1.5 bg-[#0B1F3A]/90 px-2 py-0.5 rounded-full border border-primary-fixed/40 text-[12px] font-semibold text-white">
                            <span class="w-2 h-2 rounded-full bg-primary-fixed"></span> Jaflong
                        </div>
                    </div>
                </div>

                <h1 class="font-display text-3xl sm:text-4xl text-white leading-tight mb-4 font-extrabold">
                    Plan Together. Travel Together. Manage Smarter.
                </h1>
                <p class="text-base sm:text-lg text-white/80 mb-8 leading-relaxed">
                    Effortless shared logistics, split ledgers, verified bKash & Nagad payments, and automated refund transparency for your travel squad.
                </p>

                <!-- Mini Glass Ledger Card -->
                <div class="p-6 rounded-2xl bg-white/[0.12] backdrop-blur-[16px] border border-white/25 shadow-2xl relative mb-8">
                    <div class="flex items-start justify-between mb-3">
                        <div>
                            <p class="text-[11px] uppercase tracking-wider text-primary-fixed font-semibold">Live Group Ledger</p>
                            <h3 class="font-display text-lg text-white font-bold">Sylhet Escape 2026</h3>
                        </div>
                        <span class="px-2.5 py-1 rounded-full bg-primary/40 text-primary-fixed text-xs font-semibold flex items-center gap-1 border border-primary-fixed/30">
                            <span class="w-1.5 h-1.5 rounded-full bg-primary-fixed animate-pulse"></span> Active
                        </span>
                    </div>

                    <div class="grid grid-cols-2 gap-3 py-3 px-4 rounded-xl bg-black/25 mb-4 border border-white/10">
                        <div class="border-r border-white/10">
                            <span class="text-xs text-white/70 block">Total Verified</span>
                            <span class="text-base text-white font-bold">৳25,000 paid</span>
                        </div>
                        <div>
                            <span class="text-xs text-tertiary-fixed-dim block">Balance Due</span>
                            <span class="text-base text-tertiary-fixed-dim font-bold">৳15,000 due</span>
                        </div>
                    </div>

                    <div class="flex items-center justify-between text-white/90 text-xs">
                        <div class="flex -space-x-2">
                            <div class="w-7 h-7 rounded-full bg-primary flex items-center justify-center text-white ring-2 ring-[#0B1F3A] font-semibold text-[10px]">TM</div>
                            <div class="w-7 h-7 rounded-full bg-[#12305A] flex items-center justify-center text-white ring-2 ring-[#0B1F3A] font-semibold text-[10px]">RU</div>
                            <div class="w-7 h-7 rounded-full bg-[#008373] flex items-center justify-center text-white ring-2 ring-[#0B1F3A] font-semibold text-[10px]">KA</div>
                            <div class="w-7 h-7 rounded-full bg-[#c44f32] flex items-center justify-center text-white ring-2 ring-[#0B1F3A] font-semibold text-[10px]">+2</div>
                        </div>
                        <span>5 Crew Members Active</span>
                    </div>
                </div>

                <!-- Features list -->
                <div class="space-y-2.5 text-sm text-white/90">
                    <div class="flex items-center gap-3">
                        <div class="w-5 h-5 rounded-full bg-primary-fixed/20 flex items-center justify-center text-primary-fixed">
                            <span class="material-symbols-outlined text-[15px]">check</span>
                        </div>
                        <span>Automatic equal splitting with remainder distribution</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="w-5 h-5 rounded-full bg-primary-fixed/20 flex items-center justify-center text-primary-fixed">
                            <span class="material-symbols-outlined text-[15px]">check</span>
                        </div>
                        <span>Every partial payment tracked & verified by Tour Manager</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="w-5 h-5 rounded-full bg-primary-fixed/20 flex items-center justify-center text-primary-fixed">
                            <span class="material-symbols-outlined text-[15px]">check</span>
                        </div>
                        <span>Calculated fair refunds after the journey completes</span>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="relative z-10 pt-4 flex items-center justify-between text-white/60 text-xs">
                <span>&copy; {{ date('Y') }} SafarFly System</span>
                <span class="inline-flex items-center gap-1">
                    <span class="material-symbols-outlined text-[14px]">shield</span> Verified Bank & Ledger Security
                </span>
            </div>
        </div>

        <!-- Right Side: Auth Form -->
        <main class="w-full lg:w-1/2 min-h-screen flex items-center justify-center p-4 sm:p-6 lg:p-12">
            @yield('content')
        </main>
    </div>
</body>
</html>
