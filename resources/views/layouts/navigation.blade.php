{{-- resources/views/layouts/navigation.blade.php --}}
<nav class="bg-gray-900 border-b border-gray-800 sticky top-0 z-50" x-data="{ open: false, userOpen: false, adminOpen: false }">
    <div class="w-full px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <!-- Logo - Far Left -->
            <div class="flex items-center">
                <div class="flex-shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" class="flex items-center">
                        <img src="{{ asset('images/logo-gold.png') }}" alt="TOC SIS Logo" class="h-10 w-auto">
                        <div class="hidden sm:block ml-2"> {{-- << ADDED ml-2 HERE (or ml-3, ml-4 for more space) --}}
                        </div>
                    </a>
                </div>
            </div>

            <!-- Main Navigation - Center (can be in container) -->
            <div class="hidden lg:flex lg:items-center lg:space-x-2">
                <!-- Dashboard -->
                <a href="{{ route('dashboard') }}" 
                   class="group flex items-center px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 {{ request()->routeIs('dashboard') ? 'bg-gray-800 text-white shadow-md' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                    <svg class="w-4 h-4 mr-3 {{ request()->routeIs('dashboard') ? 'text-blue-400' : 'text-gray-400 group-hover:text-gray-300' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                        <polyline points="9,22 9,12 15,12 15,22"/>
                    </svg>
                    Dashboard
                </a>

                @if(Auth::check() && in_array(Auth::user()->role, ['manager', 'student_services']))
                <!-- Students -->
                <a href="{{ route('students.index') }}" 
                   class="group flex items-center px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 {{ request()->routeIs('students.*') ? 'bg-gray-800 text-white shadow-md' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                    <svg class="w-4 h-4 mr-3 {{ request()->routeIs('students.*') ? 'text-blue-400' : 'text-gray-400 group-hover:text-gray-300' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                        <circle cx="9" cy="7" r="4"/>
                        <path d="m22 21-3-3m0 0a2 2 0 0 0 0-4 2 2 0 0 0 0 4z"/>
                    </svg>
                    Students
                </a>

                <!-- Deferrals -->
                <a href="{{ route('deferrals.index') }}" 
                   class="group flex items-center px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 {{ request()->routeIs('deferrals.*') ? 'bg-gray-800 text-white shadow-md' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                    <svg class="w-4 h-4 mr-3 {{ request()->routeIs('deferrals.*') ? 'text-yellow-400' : 'text-gray-400 group-hover:text-gray-300' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10"/>
                        <polyline points="12,6 12,12 16,14"/>
                    </svg>
                    Deferrals
                </a>
                @endif

                @if(Auth::check() && in_array(Auth::user()->role, ['manager','student_services', 'teacher']))
                <!-- Extensions -->
                <a href="{{ route('extensions.index') }}" 
                   class="group flex items-center px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 {{ request()->routeIs('extensions.*') ? 'bg-gray-800 text-white shadow-md' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                    <svg class="w-4 h-4 mr-3 {{ request()->routeIs('extensions.*') ? 'text-orange-400' : 'text-gray-400 group-hover:text-gray-300' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M8 2v4"/>
                        <path d="M16 2v4"/>
                        <rect width="18" height="18" x="3" y="4" rx="2"/>
                        <path d="M3 10h18"/>
                        <path d="m9 16 2 2 4-4"/>
                    </svg>
                    Extensions
                </a>

                <!-- Assessments -->
                <a href="{{ route('assessments.index') }}" 
                   class="group flex items-center px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 {{ request()->routeIs('assessments.*') ? 'bg-gray-800 text-white shadow-md' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                    <svg class="w-4 h-4 mr-3 {{ request()->routeIs('assessments.*') ? 'text-green-400' : 'text-gray-400 group-hover:text-gray-300' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <rect width="8" height="4" x="8" y="2" rx="1" ry="1"/>
                        <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/>
                        <path d="m9 14 2 2 4-4"/>
                    </svg>
                    Assessments
                </a>
                @endif

                @if(Auth::check() && Auth::user()->role === 'manager')
<!-- Administration Dropdown -->
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" 
                            class="group flex items-center px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 text-gray-300 hover:bg-gray-800 hover:text-white"
                            :class="{ 'bg-gray-800 text-white shadow-md': open }">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" 
                             class="w-4 h-4 mr-3 text-gray-400 group-hover:text-gray-300">
                            <path d="M4 20h16a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-7.93a2 2 0 0 1-1.66-.9l-.82-1.2A2 2 0 0 0 7.93 3H4a2 2 0 0 0-2 2v13c0 1.1.9 2 2 2Z"/>
                            <circle cx="12" cy="13" r="1"/>
                        </svg>
                        Administration
                        <svg class="w-4 h-4 ml-2 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="m6 9 6 6 6-6"/>
                        </svg>
                    </button>

                    <!-- Dropdown Menu -->
                    <div x-show="open" 
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="transform opacity-0 scale-95"
                         x-transition:enter-end="transform opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="transform opacity-100 scale-100"
                         x-transition:leave-end="transform opacity-0 scale-95"
                         @click.away="open = false"
                         class="absolute right-0 mt-2 w-56 rounded-xl shadow-2xl bg-white ring-1 ring-black ring-opacity-5 focus:outline-none z-50 border border-gray-100">
                        <div class="py-2">
                            <a href="{{ route('programmes.index') }}" 
                               class="group flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 transition-colors duration-150 {{ request()->routeIs('programmes.*') ? 'bg-blue-50 text-blue-700 border-r-2 border-blue-500' : '' }}">
                                <svg class="w-4 h-4 mr-3 text-gray-400 group-hover:text-gray-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/>
                                </svg>
                                Programmes
                            </a>
                            <a href="{{ route('cohorts.index') }}" 
                               class="group flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 transition-colors duration-150 {{ request()->routeIs('cohorts.*') ? 'bg-blue-50 text-blue-700 border-r-2 border-blue-500' : '' }}">
                                <svg class="w-4 h-4 mr-3 text-gray-400 group-hover:text-gray-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M18 21a8 8 0 0 0-16 0"/>
                                    <circle cx="10" cy="8" r="5"/>
                                    <path d="m21 8-2 2-1.5-1.5L21 8z"/>
                                </svg>
                                Cohorts
                            </a>
                            <a href="{{ route('modules.index') }}" 
                               class="group flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 transition-colors duration-150 {{ request()->routeIs('modules.*') ? 'bg-blue-50 text-blue-700 border-r-2 border-blue-500' : '' }}">
                                <svg class="w-4 h-4 mr-3 text-gray-400 group-hover:text-gray-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/>
                                    <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>
                                </svg>
                                Modules
                            </a>
                            <a href="{{ route('module-instances.index') }}" 
                               class="group flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 transition-colors duration-150 {{ request()->routeIs('module-instances.*') ? 'bg-blue-50 text-blue-700 border-r-2 border-blue-500' : '' }}">
                                <svg class="w-4 h-4 mr-3 text-gray-400 group-hover:text-gray-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M12.83 2.18a2 2 0 0 0-1.66 0L2.6 6.08a1 1 0 0 0 0 1.83l8.58 3.91a2 2 0 0 0 1.66 0l8.58-3.9a1 1 0 0 0 0-1.83z"/>
                                    <path d="M22 17.65c0 .42-.2.8-.53 1.05L12.83 22a2 2 0 0 1-1.66 0L2.53 18.7A1.33 1.33 0 0 1 2 17.65"/>
                                    <path d="M22 12.65c0 .42-.2.8-.53 1.05L12.83 17a2 2 0 0 1-1.66 0L2.53 13.7A1.33 1.33 0 0 1 2 12.65"/>
                                </svg>
                                Module Instances
                            </a>
                            <div class="border-t border-gray-100 my-2"></div>
                            <a href="{{ route('reports.dashboard') }}" 
                               class="group flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 transition-colors duration-150 {{ request()->routeIs('reports.*') ? 'bg-blue-50 text-blue-700 border-r-2 border-blue-500' : '' }}">
                                <svg class="w-4 h-4 mr-3 text-gray-400 group-hover:text-gray-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M3 3v18h18"/>
                                    <path d="m19 9-5 5-4-4-3 3"/>
                                </svg>
                                Reports & Analytics
                            </a>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <!-- User Profile - Extreme Right (outside container) -->
            @if(Auth::check())
            <div class="flex items-center space-x-6">
                <!-- Notifications -->
                <button class="relative p-2 rounded-full text-gray-400 hover:text-gray-300 hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-900 focus:ring-white transition-all duration-200">
                    <span class="sr-only">View notifications</span>
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/>
                        <path d="m13.73 21a2 2 0 0 1-3.46 0"/>
                    </svg>
                    <!-- Notification badge -->
                    <span class="absolute top-1 right-1 h-2 w-2 bg-red-500 rounded-full"></span>
                </button>

                <!-- Profile dropdown -->
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" 
                            class="flex items-center space-x-3 text-sm rounded-xl focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-900 focus:ring-white transition-all duration-200 p-2 hover:bg-gray-800"
                            :class="{ 'bg-gray-800': open }">
                        <div class="h-9 w-9 rounded-xl bg-gradient-to-br from-purple-500 to-purple-600 flex items-center justify-center text-white font-semibold text-sm shadow-lg">
                            {{ substr(Auth::user()->name, 0, 1) }}
                        </div>
                        <div class="text-left hidden sm:block">
                            <div class="text-sm font-medium text-white">{{ Auth::user()->name }}</div>
                            <div class="text-xs text-gray-400">{{ ucfirst(str_replace('_', ' ', Auth::user()->role)) }}</div>
                        </div>
                        <svg class="h-4 w-4 text-gray-400 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="m6 9 6 6 6-6"/>
                        </svg>
                    </button>

                    <!-- Profile Dropdown -->
                    <div x-show="open" 
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="transform opacity-0 scale-95"
                         x-transition:enter-end="transform opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="transform opacity-100 scale-100"
                         x-transition:leave-end="transform opacity-0 scale-95"
                         @click.away="open = false"
                         class="absolute right-0 mt-3 w-64 rounded-xl shadow-2xl bg-white ring-1 ring-black ring-opacity-5 focus:outline-none z-50 border border-gray-100">
                        
                        <!-- Profile Header -->
                        <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-purple-50 to-blue-50">
                            <div class="flex items-center space-x-3">
                                <div class="h-10 w-10 rounded-lg bg-gradient-to-br from-purple-500 to-purple-600 flex items-center justify-center text-white font-semibold">
                                    {{ substr(Auth::user()->name, 0, 1) }}
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-gray-900">{{ Auth::user()->name }}</p>
                                    <p class="text-xs text-gray-500">{{ Auth::user()->email }}</p>
                                    <p class="text-xs text-purple-600 font-medium">{{ ucfirst(str_replace('_', ' ', Auth::user()->role)) }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Menu Items -->
                        <div class="py-2">
                            <a href="#" class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 transition-colors duration-150">
                                <svg class="w-4 h-4 mr-3 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/>
                                    <circle cx="12" cy="7" r="4"/>
                                </svg>
                                Your Profile
                            </a>
                            <a href="#" class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 transition-colors duration-150">
                                <svg class="w-4 h-4 mr-3 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <circle cx="12" cy="12" r="3"/>
                                    <path d="M12 1v6m0 6v6"/>
                                    <path d="m21 12-6-6-6 6-6-6"/>
                                </svg>
                                Account Settings
                            </a>
                            <a href="#" class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 transition-colors duration-150">
                                <svg class="w-4 h-4 mr-3 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <circle cx="12" cy="12" r="10"/>
                                    <path d="m9 12 2 2 4-4"/>
                                </svg>
                                Help & Support
                            </a>
                            <div class="border-t border-gray-100 my-2"></div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="flex items-center w-full px-4 py-3 text-sm text-red-700 hover:bg-red-50 transition-colors duration-150">
                                    <svg class="w-4 h-4 mr-3 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                                        <polyline points="16,17 21,12 16,7"/>
                                        <line x1="21" x2="9" y1="12" y2="12"/>
                                    </svg>
                                    Sign Out
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Mobile menu button -->
                <div class="lg:hidden">
                    <button @click="open = !open" 
                            class="inline-flex items-center justify-center p-2 rounded-lg text-gray-400 hover:text-white hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-white transition-all duration-200">
                        <span class="sr-only">Open main menu</span>
                        <svg class="h-6 w-6" :class="{'hidden': open, 'block': !open }" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <line x1="4" x2="20" y1="12" y2="12"/>
                            <line x1="4" x2="20" y1="6" y2="6"/>
                            <line x1="4" x2="20" y1="18" y2="18"/>
                        </svg>
                        <svg class="h-6 w-6" :class="{'block': open, 'hidden': !open }" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="m18 6-12 12"/>
                            <path d="m6 6 12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Mobile menu -->
    <div x-show="open" class="lg:hidden">
        <div class="px-4 pt-4 pb-6 space-y-2 bg-gray-800 border-t border-gray-700">
            <a href="{{ route('dashboard') }}" 
               class="flex items-center px-4 py-3 rounded-lg text-base font-medium transition-colors duration-200 {{ request()->routeIs('dashboard') ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                <svg class="w-5 h-5 mr-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                    <polyline points="9,22 9,12 15,12 15,22"/>
                </svg>
                Dashboard
            </a>

            @if(Auth::check() && in_array(Auth::user()->role, ['manager', 'student_services']))
            <a href="{{ route('students.index') }}" 
               class="flex items-center px-4 py-3 rounded-lg text-base font-medium transition-colors duration-200 {{ request()->routeIs('students.*') ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                <svg class="w-5 h-5 mr-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="m22 21-3-3m0 0a2 2 0 0 0 0-4 2 2 0 0 0 0 4z"/>
                </svg>
                Students
            </a>
            @endif

            <!-- Add more mobile items as needed -->
        </div>
    </div>
</nav>