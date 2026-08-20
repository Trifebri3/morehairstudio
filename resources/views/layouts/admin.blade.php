<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Dashboard | MORE Admin</title>

    <!-- Styles & Scripts -->
    @vite(['resources/css/admin.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-stone-50 text-stone-900 min-h-screen flex antialiased" x-data="{ sidebarOpen: window.innerWidth >= 1024 }">
    <!-- Mobile Sidebar Backdrop Overlay -->
    <div x-show="sidebarOpen" @click="sidebarOpen = false" class="lg:hidden fixed inset-0 bg-black/40 z-30 transition-opacity" style="display: none;"></div>

    <!-- Sidebar -->
    <aside class="bg-stone-100 border-r border-stone-200 transition-all duration-300 flex flex-col z-40 fixed inset-y-0 left-0 lg:static h-screen" 
           :class="sidebarOpen ? 'w-64 translate-x-0' : 'w-20 -translate-x-full lg:translate-x-0 lg:w-20'">
        <!-- Header -->
        <div class="h-20 flex items-center justify-between px-6 border-b border-stone-200">
            <div class="flex items-center space-x-2" x-show="sidebarOpen">
                <span class="text-lg font-bold tracking-widest text-[#0A3D91]">MORE</span>
                <span class="text-[9px] uppercase tracking-wider bg-blue-50 text-blue-600 px-2 py-0.5 rounded font-extrabold border border-blue-100">
                    {{ auth()->user()->role === 'super_admin' ? 'Super' : (auth()->user()->role === 'outlet_admin' ? 'Outlet' : 'Stylist') }}
                </span>
            </div>
            <button @click="sidebarOpen = !sidebarOpen" class="text-stone-500 hover:text-blue-600 transition">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
        </div>

        <!-- Links -->
        <nav class="flex-grow p-4 space-y-1.5 select-none overflow-y-auto font-mono text-xs uppercase tracking-wider font-extrabold">
            @if(auth()->user()->role === 'super_admin')
                <!-- Super Admin Menu -->
                <a href="{{ route('admin.dashboard') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-stone-200/70 text-stone-700 hover:text-stone-900 transition">
                    <span>Dashboard</span>
                </a>
                <a href="{{ route('admin.outlets') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-stone-200/70 text-stone-700 hover:text-stone-900 transition">
                    <span>Outlets</span>
                </a>
                <a href="{{ route('admin.services') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-stone-200/70 text-stone-700 hover:text-stone-900 transition">
                    <span>Services</span>
                </a>
                <a href="{{ route('admin.stylists') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-stone-200/70 text-stone-700 hover:text-stone-900 transition">
                    <span>Stylists</span>
                </a>
                <a href="{{ route('admin.customers') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-stone-200/70 text-stone-700 hover:text-stone-900 transition">
                    <span>CRM Customers</span>
                </a>
                <a href="{{ route('admin.crm') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-stone-200/70 text-stone-700 hover:text-stone-900 transition">
                    <span>CRM Intelligence</span>
                </a>
                <a href="{{ route('admin.pos') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-stone-200/70 text-stone-700 hover:text-stone-900 transition">
                    <span>POS Cashier</span>
                </a>
                <a href="{{ route('admin.transactions') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-stone-200/70 text-stone-700 hover:text-stone-900 transition">
                    <span>POS Transactions</span>
                </a>
                <a href="{{ route('admin.promotions') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-stone-200/70 text-stone-700 hover:text-stone-900 transition">
                    <span>Promotions</span>
                </a>
                <a href="{{ route('admin.whatsapp-logs') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-stone-200/70 text-stone-700 hover:text-stone-900 transition">
                    <span>WhatsApp Logs</span>
                </a>
                <a href="{{ route('admin.cms') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-stone-200/70 text-stone-700 hover:text-stone-900 transition">
                    <span>CMS Manager</span>
                </a>
                <a href="{{ route('admin.seo') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-stone-200/70 text-stone-700 hover:text-stone-900 transition">
                    <span>SEO Meta</span>
                </a>
                <a href="{{ route('admin.analytics') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-stone-200/70 text-stone-700 hover:text-stone-900 transition">
                    <span>Analytics</span>
                </a>
                <a href="{{ route('admin.settings') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-stone-200/70 text-stone-700 hover:text-stone-900 transition">
                    <span>System Settings</span>
                </a>
                <a href="{{ route('admin.whatsapp') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-stone-200/70 text-stone-700 hover:text-stone-900 transition">
                    <span>WhatsApp Center</span>
                </a>
                <a href="{{ route('admin.email') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-stone-200/70 text-stone-700 hover:text-stone-900 transition">
                    <span>Email Center</span>
                </a>
            @elseif(auth()->user()->role === 'outlet_admin')
                <!-- Outlet Admin Menu -->
                <a href="{{ route('outlet.dashboard') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-stone-200/70 text-stone-700 hover:text-stone-900 transition">
                    <span>Dashboard</span>
                </a>
                <a href="{{ route('outlet.bookings') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-stone-200/70 text-stone-700 hover:text-stone-900 transition">
                    <span>Bookings</span>
                </a>
                <a href="{{ route('outlet.stylists') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-stone-200/70 text-stone-700 hover:text-stone-900 transition">
                    <span>Stylists</span>
                </a>
                <a href="{{ route('outlet.attendance') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-stone-200/70 text-stone-700 hover:text-stone-900 transition">
                    <span>Attendance</span>
                </a>
                <a href="{{ route('admin.crm') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-stone-200/70 text-stone-700 hover:text-stone-900 transition">
                    <span>CRM Intelligence</span>
                </a>
                <a href="{{ route('outlet.pos') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-stone-200/70 text-stone-700 hover:text-stone-900 transition">
                    <span>POS Cashier</span>
                </a>
                <a href="{{ route('outlet.transactions') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-stone-200/70 text-stone-700 hover:text-stone-900 transition">
                    <span>POS Transactions</span>
                </a>
            @elseif(auth()->user()->role === 'stylist')
                <!-- Stylist Menu -->
                <a href="{{ route('stylist.dashboard') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-stone-200/70 text-stone-700 hover:text-stone-900 transition">
                    <span>Dasbor Stylist</span>
                </a>
            @endif
        </nav>

        <!-- Footer profile and logout -->
        <div class="p-4 border-t border-stone-200 flex items-center justify-between">
            <div class="flex items-center space-x-3" x-show="sidebarOpen">
                <div class="h-9 w-9 rounded bg-[#0A3D91] text-white font-extrabold flex items-center justify-center text-sm uppercase">
                    {{ substr(auth()->user()->name, 0, 1) }}
                </div>
                <div class="overflow-hidden">
                    <p class="text-xs font-semibold text-stone-800 truncate">{{ auth()->user()->name }}</p>
                    <p class="text-xxs text-stone-500 truncate font-mono">{{ auth()->user()->email }}</p>
                </div>
            </div>
            <!-- Logout Button -->
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="text-stone-500 hover:text-blue-600 transition duration-300 font-extrabold text-xs">
                    Exit
                </button>
            </form>
        </div>
    </aside>

    <!-- Main View Area -->
    <div class="flex-grow flex flex-col min-w-0">
        @if(session()->has('impersonator_id'))
            <div class="bg-blue-600 text-white text-xs py-3 px-4 md:px-8 flex flex-col sm:flex-row justify-between items-center z-50 select-none gap-2">
                <div class="text-center sm:text-left">
                    <span>Anda sedang menguji sistem sebagai <strong>{{ auth()->user()->name }}</strong> (Role: {{ auth()->user()->role }}).</span>
                </div>
                <a href="{{ route('impersonate.stop') }}" class="bg-white text-blue-600 px-3 py-1 rounded font-extrabold hover:bg-stone-100 transition shadow-sm text-xxs sm:text-xs">
                    Kembali ke Super Admin
                </a>
            </div>
        @endif

        <!-- Top Bar -->
        <header class="h-20 bg-white border-b border-stone-200 flex items-center justify-between px-4 lg:px-8 shrink-0">
            <div class="flex items-center space-x-3 min-w-0">
                <!-- Hamburger Menu Button on Mobile -->
                <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden text-stone-500 hover:text-blue-600 transition focus:outline-none shrink-0">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
                <h1 class="text-xs sm:text-sm md:text-base font-bold uppercase tracking-wider font-mono text-stone-850 truncate">@yield('page_title', 'Control Center')</h1>
            </div>
            
            <div class="flex items-center space-x-2 md:space-x-4 shrink-0">
                @if(auth()->user()->role === 'super_admin')
                    <!-- Prominent Public Config button for Super Admin -->
                    <a href="{{ route('admin.cms') }}" class="hidden sm:inline-block text-[10px] md:text-xs uppercase tracking-widest bg-[#0A3D91] hover:bg-[#062e70] text-white px-3 py-2 rounded-lg transition font-extrabold shadow-sm">
                        Config
                    </a>
                @endif
                <a href="{{ route('home') }}" target="_blank" class="hidden md:inline-block text-[10px] md:text-xs uppercase tracking-widest text-stone-500 hover:text-stone-800 transition font-extrabold">
                    Preview Website &rarr;
                </a>
                <a href="{{ route('tablet.dashboard') }}" target="_blank" class="text-[10px] md:text-xs uppercase tracking-widest bg-stone-100 hover:bg-stone-200 text-blue-600 px-3 py-2 rounded-lg border border-stone-200 transition font-extrabold">
                    Tablet View
                </a>
            </div>
        </header>

        <!-- Content -->
        <main class="flex-grow p-8 overflow-y-auto bg-stone-50">
            {{ $slot }}
        </main>
    </div>

    @livewireScripts
</body>
</html>
