{{-- resources/views/layouts/navigation.blade.php --}}
<nav class="bg-gradient-to-r from-slate-900 via-slate-900 to-slate-800 border-b border-slate-700/50 sticky top-0 z-50 backdrop-blur-sm" x-data="{ open: false, userOpen: false, adminOpen: false }">
    <!-- Subtle top accent line -->
    <div class="h-0.5 bg-gradient-to-r from-blue-500 via-purple-500 to-blue-600"></div>
    <div class="w-full px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <!-- Logo Section -->
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <a href="{{ route('dashboard') }}" class="flex items-center group transition-all duration-300 hover:scale-105">
                        <img src="{{ asset('images/logo-gold.png') }}" alt="TOC SIS Logo" class="h-11 w-auto filter drop-shadow-lg transition-all duration-300 group-hover:drop-shadow-xl">
                        <div class="hidden sm:block ml-3">
                            <div class="text-white font-bold text-lg tracking-tight">TOC SIS</div>
                            <div class="text-slate-400 text-xs font-medium">Student Portal</div>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Main Navigation -->
            <div class="hidden lg:flex lg:items-center lg:space-x-1">
                <!-- Dashboard -->
                <a href="{{ route('dashboard') }}" 
                   class="group relative flex items-center px-5 py-2.5 rounded-xl text-sm font-semibold transition-all duration-300 
                          {{ request()->routeIs('dashboard') 
                             ? 'bg-gradient-to-r from-blue-500/20 to-purple-500/20 text-white shadow-lg shadow-blue-500/10 border border-blue-400/30' 
                             : 'text-slate-300 hover:text-white hover:bg-slate-800/60 hover:shadow-lg hover:shadow-slate-900/20' }}">
                    
                    <!-- Active state glow -->
                    @if(request()->routeIs('dashboard'))
                        <div class="absolute inset-0 rounded-xl bg-gradient-to-r from-blue-500/10 to-purple-500/10 blur-sm"></div>
                    @endif
                    
                    <svg class="w-4 h-4 mr-3 relative z-10 {{ request()->routeIs('dashboard') ? 'text-blue-400' : 'text-slate-400 group-hover:text-slate-300' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                        <polyline points="9,22 9,12 15,12 15,22"/>
                    </svg>
                    <span class="relative z-10">Dashboard</span>
                </a>

                @if(Auth::check() && in_array(Auth::user()->role, ['manager', 'student_services']))
                <!-- Student Management Dropdown -->
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" 
                            class="group relative flex items-center px-5 py-2.5 rounded-xl text-sm font-semibold transition-all duration-300
                                   {{ request()->routeIs('enquiries.*', 'students.*', 'deferrals.*') 
                                      ? 'bg-gradient-to-r from-blue-500/20 to-purple-500/20 text-white shadow-lg shadow-blue-500/10 border border-blue-400/30' 
                                      : 'text-slate-300 hover:text-white hover:bg-slate-800/60 hover:shadow-lg hover:shadow-slate-900/20' }}"
                            :class="{ 'bg-slate-800/60 shadow-lg': open && !{{ request()->routeIs('enquiries.*', 'students.*', 'deferrals.*') ? 'true' : 'false' }} }">
                        
                        <!-- Active state glow -->
                        @if(request()->routeIs('enquiries.*', 'students.*', 'deferrals.*'))
                            <div class="absolute inset-0 rounded-xl bg-gradient-to-r from-blue-500/10 to-purple-500/10 blur-sm"></div>
                        @endif
                        
                        <svg class="w-4 h-4 mr-3 relative z-10 {{ request()->routeIs('enquiries.*', 'students.*', 'deferrals.*') ? 'text-blue-400' : 'text-slate-400 group-hover:text-slate-300' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                            <circle cx="9" cy="7" r="4"/>
                            <path d="m22 21-3-3m0 0a2 2 0 0 0 0-4 2 2 0 0 0 0 4z"/>
                        </svg>
                        <span class="relative z-10">Students</span>
                        <svg class="w-4 h-4 ml-2 relative z-10 transition-transform duration-300" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="m6 9 6 6 6-6"/>
                        </svg>
                    </button>

                    <div x-show="open" 
                         x-cloak
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="transform opacity-0 scale-95 translate-y-1"
                         x-transition:enter-end="transform opacity-100 scale-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="transform opacity-100 scale-100 translate-y-0"
                         x-transition:leave-end="transform opacity-0 scale-95 translate-y-1"
                         @click.away="open = false"
                         class="absolute left-0 mt-3 w-56 rounded-2xl shadow-2xl bg-white/95 backdrop-blur-xl ring-1 ring-slate-200/50 focus:outline-none z-50 border border-white/20">
                        <div class="py-2">
                            <a href="{{ route('enquiries.index') }}" 
                               class="group flex items-center mx-3 px-4 py-3 rounded-xl text-sm font-medium transition-all duration-200
                                      {{ request()->routeIs('enquiries.*') 
                                         ? 'bg-gradient-to-r from-blue-500 to-purple-600 text-white shadow-lg shadow-blue-500/25' 
                                         : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80' }}">
                                <div class="flex items-center justify-center w-8 h-8 rounded-lg {{ request()->routeIs('enquiries.*') ? 'bg-white/20' : 'bg-purple-100 group-hover:bg-purple-200' }} mr-3">
                                    <svg class="w-4 h-4 {{ request()->routeIs('enquiries.*') ? 'text-white' : 'text-purple-600' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </div>
                                Enquiries
                            </a>
                            <a href="{{ route('students.index') }}" 
                               class="group flex items-center mx-3 px-4 py-3 rounded-xl text-sm font-medium transition-all duration-200
                                      {{ request()->routeIs('students.*') 
                                         ? 'bg-gradient-to-r from-blue-500 to-purple-600 text-white shadow-lg shadow-blue-500/25' 
                                         : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80' }}">
                                <div class="flex items-center justify-center w-8 h-8 rounded-lg {{ request()->routeIs('students.*') ? 'bg-white/20' : 'bg-blue-100 group-hover:bg-blue-200' }} mr-3">
                                    <svg class="w-4 h-4 {{ request()->routeIs('students.*') ? 'text-white' : 'text-blue-600' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                                        <circle cx="9" cy="7" r="4"/>
                                    </svg>
                                </div>
                                Student Records
                            </a>
                            <a href="{{ route('deferrals.index') }}" 
                               class="group flex items-center mx-3 px-4 py-3 rounded-xl text-sm font-medium transition-all duration-200
                                      {{ request()->routeIs('deferrals.*') 
                                         ? 'bg-gradient-to-r from-blue-500 to-purple-600 text-white shadow-lg shadow-blue-500/25' 
                                         : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80' }}">
                                <div class="flex items-center justify-center w-8 h-8 rounded-lg {{ request()->routeIs('deferrals.*') ? 'bg-white/20' : 'bg-amber-100 group-hover:bg-amber-200' }} mr-3">
                                    <svg class="w-4 h-4 {{ request()->routeIs('deferrals.*') ? 'text-white' : 'text-amber-600' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <circle cx="12" cy="12" r="10"/>
                                        <polyline points="12,6 12,12 16,14"/>
                                    </svg>
                                </div>
                                Deferrals
                            </a>
                        </div>
                    </div>
                </div>
                @endif

                @if(Auth::check() && in_array(Auth::user()->role, ['manager','student_services', 'teacher']))
                <!-- Assessment Management Dropdown -->
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" 
                            class="group relative flex items-center px-5 py-2.5 rounded-xl text-sm font-semibold transition-all duration-300
                                   {{ request()->routeIs('assessments.*', 'extensions.*', 'extension-requests.*') 
                                      ? 'bg-gradient-to-r from-green-500/20 to-emerald-500/20 text-white shadow-lg shadow-green-500/10 border border-green-400/30' 
                                      : 'text-slate-300 hover:text-white hover:bg-slate-800/60 hover:shadow-lg hover:shadow-slate-900/20' }}"
                            :class="{ 'bg-slate-800/60 shadow-lg': open && !{{ request()->routeIs('assessments.*', 'extensions.*', 'extension-requests.*') ? 'true' : 'false' }} }">
                        
                        <!-- Active state glow -->
                        @if(request()->routeIs('assessments.*', 'extensions.*', 'extension-requests.*'))
                            <div class="absolute inset-0 rounded-xl bg-gradient-to-r from-green-500/10 to-emerald-500/10 blur-sm"></div>
                        @endif
                        
                        <svg class="w-4 h-4 mr-3 relative z-10 {{ request()->routeIs('assessments.*', 'extensions.*', 'extension-requests.*') ? 'text-green-400' : 'text-slate-400 group-hover:text-slate-300' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <rect width="8" height="4" x="8" y="2" rx="1" ry="1"/>
                            <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/>
                            <path d="m9 14 2 2 4-4"/>
                        </svg>
                        <span class="relative z-10">Assessments</span>
                        <svg class="w-4 h-4 ml-2 relative z-10 transition-transform duration-300" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="m6 9 6 6 6-6"/>
                        </svg>
                    </button>

                    <div x-show="open" 
                         x-cloak
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="transform opacity-0 scale-95 translate-y-1"
                         x-transition:enter-end="transform opacity-100 scale-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="transform opacity-100 scale-100 translate-y-0"
                         x-transition:leave-end="transform opacity-0 scale-95 translate-y-1"
                         @click.away="open = false"
                         class="absolute left-0 mt-3 w-56 rounded-2xl shadow-2xl bg-white/95 backdrop-blur-xl ring-1 ring-slate-200/50 focus:outline-none z-50 border border-white/20">
                        <div class="py-2">
                            <a href="{{ route('assessments.index') }}" 
                               class="group flex items-center mx-3 px-4 py-3 rounded-xl text-sm font-medium transition-all duration-200
                                      {{ request()->routeIs('assessments.*') 
                                         ? 'bg-gradient-to-r from-green-500 to-emerald-600 text-white shadow-lg shadow-green-500/25' 
                                         : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80' }}">
                                <div class="flex items-center justify-center w-8 h-8 rounded-lg {{ request()->routeIs('assessments.*') ? 'bg-white/20' : 'bg-green-100 group-hover:bg-green-200' }} mr-3">
                                    <svg class="w-4 h-4 {{ request()->routeIs('assessments.*') ? 'text-white' : 'text-green-600' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <rect width="8" height="4" x="8" y="2" rx="1" ry="1"/>
                                        <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/>
                                        <path d="m9 14 2 2 4-4"/>
                                    </svg>
                                </div>
                                Grade Management
                            </a>
                            <a href="{{ route('extensions.index') }}" 
                               class="group flex items-center mx-3 px-4 py-3 rounded-xl text-sm font-medium transition-all duration-200
                                      {{ request()->routeIs('extensions.*') 
                                         ? 'bg-gradient-to-r from-green-500 to-emerald-600 text-white shadow-lg shadow-green-500/25' 
                                         : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80' }}">
                                <div class="flex items-center justify-center w-8 h-8 rounded-lg {{ request()->routeIs('extensions.*') ? 'bg-white/20' : 'bg-orange-100 group-hover:bg-orange-200' }} mr-3">
                                    <svg class="w-4 h-4 {{ request()->routeIs('extensions.*') ? 'text-white' : 'text-orange-600' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M8 2v4"/>
                                        <path d="M16 2v4"/>
                                        <rect width="18" height="18" x="3" y="4" rx="2"/>
                                        <path d="M3 10h18"/>
                                        <path d="m9 16 2 2 4-4"/>
                                    </svg>
                                </div>
                                Extensions (Legacy)
                            </a>
                            <a href="{{ route('extension-requests.staff-index') }}" 
                               class="group flex items-center mx-3 px-4 py-3 rounded-xl text-sm font-medium transition-all duration-200
                                      {{ request()->routeIs('extension-requests.*') 
                                         ? 'bg-gradient-to-r from-green-500 to-emerald-600 text-white shadow-lg shadow-green-500/25' 
                                         : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80' }}">
                                <div class="flex items-center justify-center w-8 h-8 rounded-lg {{ request()->routeIs('extension-requests.*') ? 'bg-white/20' : 'bg-teal-100 group-hover:bg-teal-200' }} mr-3">
                                    <svg class="w-4 h-4 {{ request()->routeIs('extension-requests.*') ? 'text-white' : 'text-teal-600' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        <path d="M12 6v6m0 0v6"/>
                                    </svg>
                                </div>
                                Extension Requests
                            </a>
                        </div>
                    </div>
                </div>
                @endif

                @if(Auth::check() && Auth::user()->role === 'manager')
                <!-- Administration Dropdown -->
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" 
                            class="group relative flex items-center px-5 py-2.5 rounded-xl text-sm font-semibold transition-all duration-300
                                   {{ request()->routeIs('programmes.*', 'cohorts.*', 'modules.*', 'module-instances.*', 'reports.*', 'notifications.admin', 'notifications.announcement') 
                                      ? 'bg-gradient-to-r from-purple-500/20 to-indigo-500/20 text-white shadow-lg shadow-purple-500/10 border border-purple-400/30' 
                                      : 'text-slate-300 hover:text-white hover:bg-slate-800/60 hover:shadow-lg hover:shadow-slate-900/20' }}"
                            :class="{ 'bg-slate-800/60 shadow-lg': open && !{{ request()->routeIs('programmes.*', 'cohorts.*', 'modules.*', 'module-instances.*', 'reports.*', 'notifications.admin', 'notifications.announcement') ? 'true' : 'false' }} }">
                        
                        <!-- Active state glow -->
                        @if(request()->routeIs('programmes.*', 'cohorts.*', 'modules.*', 'module-instances.*', 'reports.*', 'notifications.admin', 'notifications.announcement'))
                            <div class="absolute inset-0 rounded-xl bg-gradient-to-r from-purple-500/10 to-indigo-500/10 blur-sm"></div>
                        @endif
                        
                        <svg class="w-4 h-4 mr-3 relative z-10 {{ request()->routeIs('programmes.*', 'cohorts.*', 'modules.*', 'module-instances.*', 'reports.*', 'notifications.admin', 'notifications.announcement') ? 'text-purple-400' : 'text-slate-400 group-hover:text-slate-300' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M4 20h16a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-7.93a2 2 0 0 1-1.66-.9l-.82-1.2A2 2 0 0 0 7.93 3H4a2 2 0 0 0-2 2v13c0 1.1.9 2 2 2Z"/>
                            <circle cx="12" cy="13" r="1"/>
                        </svg>
                        <span class="relative z-10">Administration</span>
                        <svg class="w-4 h-4 ml-2 relative z-10 transition-transform duration-300" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="m6 9 6 6 6-6"/>
                        </svg>
                    </button>

                    <!-- Dropdown Menu -->
                    <div x-show="open" 
                         x-cloak
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="transform opacity-0 scale-95 translate-y-1"
                         x-transition:enter-end="transform opacity-100 scale-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="transform opacity-100 scale-100 translate-y-0"
                         x-transition:leave-end="transform opacity-0 scale-95 translate-y-1"
                         @click.away="open = false"
                         class="absolute right-0 mt-3 w-64 rounded-2xl shadow-2xl bg-white/95 backdrop-blur-xl ring-1 ring-slate-200/50 focus:outline-none z-50 border border-white/20">
                        <div class="py-2">
                            <a href="{{ route('programmes.index') }}" 
                               class="group flex items-center mx-3 px-4 py-3 rounded-xl text-sm font-medium transition-all duration-200
                                      {{ request()->routeIs('programmes.*') 
                                         ? 'bg-gradient-to-r from-purple-500 to-indigo-600 text-white shadow-lg shadow-purple-500/25' 
                                         : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80' }}">
                                <div class="flex items-center justify-center w-8 h-8 rounded-lg {{ request()->routeIs('programmes.*') ? 'bg-white/20' : 'bg-indigo-100 group-hover:bg-indigo-200' }} mr-3">
                                    <svg class="w-4 h-4 {{ request()->routeIs('programmes.*') ? 'text-white' : 'text-indigo-600' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/>
                                    </svg>
                                </div>
                                Programmes
                            </a>
                            <a href="{{ route('cohorts.index') }}" 
                               class="group flex items-center mx-3 px-4 py-3 rounded-xl text-sm font-medium transition-all duration-200
                                      {{ request()->routeIs('cohorts.*') 
                                         ? 'bg-gradient-to-r from-purple-500 to-indigo-600 text-white shadow-lg shadow-purple-500/25' 
                                         : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80' }}">
                                <div class="flex items-center justify-center w-8 h-8 rounded-lg {{ request()->routeIs('cohorts.*') ? 'bg-white/20' : 'bg-blue-100 group-hover:bg-blue-200' }} mr-3">
                                    <svg class="w-4 h-4 {{ request()->routeIs('cohorts.*') ? 'text-white' : 'text-blue-600' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M18 21a8 8 0 0 0-16 0"/>
                                        <circle cx="10" cy="8" r="5"/>
                                        <path d="m21 8-2 2-1.5-1.5L21 8z"/>
                                    </svg>
                                </div>
                                Cohorts
                            </a>
                            <a href="{{ route('modules.index') }}" 
                               class="group flex items-center mx-3 px-4 py-3 rounded-xl text-sm font-medium transition-all duration-200
                                      {{ request()->routeIs('modules.*') 
                                         ? 'bg-gradient-to-r from-purple-500 to-indigo-600 text-white shadow-lg shadow-purple-500/25' 
                                         : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80' }}">
                                <div class="flex items-center justify-center w-8 h-8 rounded-lg {{ request()->routeIs('modules.*') ? 'bg-white/20' : 'bg-emerald-100 group-hover:bg-emerald-200' }} mr-3">
                                    <svg class="w-4 h-4 {{ request()->routeIs('modules.*') ? 'text-white' : 'text-emerald-600' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/>
                                        <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>
                                    </svg>
                                </div>
                                Modules
                            </a>
                            <a href="{{ route('module-instances.index') }}" 
                               class="group flex items-center mx-3 px-4 py-3 rounded-xl text-sm font-medium transition-all duration-200
                                      {{ request()->routeIs('module-instances.*') 
                                         ? 'bg-gradient-to-r from-purple-500 to-indigo-600 text-white shadow-lg shadow-purple-500/25' 
                                         : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80' }}">
                                <div class="flex items-center justify-center w-8 h-8 rounded-lg {{ request()->routeIs('module-instances.*') ? 'bg-white/20' : 'bg-cyan-100 group-hover:bg-cyan-200' }} mr-3">
                                    <svg class="w-4 h-4 {{ request()->routeIs('module-instances.*') ? 'text-white' : 'text-cyan-600' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M12.83 2.18a2 2 0 0 0-1.66 0L2.6 6.08a1 1 0 0 0 0 1.83l8.58 3.91a2 2 0 0 0 1.66 0l8.58-3.9a1 1 0 0 0 0-1.83z"/>
                                        <path d="M22 17.65c0 .42-.2.8-.53 1.05L12.83 22a2 2 0 0 1-1.66 0L2.53 18.7A1.33 1.33 0 0 1 2 17.65"/>
                                        <path d="M22 12.65c0 .42-.2.8-.53 1.05L12.83 17a2 2 0 0 1-1.66 0L2.53 13.7A1.33 1.33 0 0 1 2 12.65"/>
                                    </svg>
                                </div>
                                Module Instances
                            </a>
                            <div class="border-t border-gray-100 my-2"></div>
                            <a href="{{ route('notifications.admin') }}" 
                               class="group flex items-center mx-3 px-4 py-3 rounded-xl text-sm font-medium transition-all duration-200
                                      {{ request()->routeIs('notifications.admin', 'notifications.announcement') 
                                         ? 'bg-gradient-to-r from-purple-500 to-indigo-600 text-white shadow-lg shadow-purple-500/25' 
                                         : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80' }}">
                                <div class="flex items-center justify-center w-8 h-8 rounded-lg {{ request()->routeIs('notifications.admin', 'notifications.announcement') ? 'bg-white/20' : 'bg-purple-100 group-hover:bg-purple-200' }} mr-3">
                                    <svg class="w-4 h-4 {{ request()->routeIs('notifications.admin', 'notifications.announcement') ? 'text-white' : 'text-purple-600' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                                    </svg>
                                </div>
                                System Messages
                            </a>
                            <a href="{{ route('reports.dashboard') }}" 
                               class="group flex items-center mx-3 px-4 py-3 rounded-xl text-sm font-medium transition-all duration-200
                                      {{ request()->routeIs('reports.*') 
                                         ? 'bg-gradient-to-r from-purple-500 to-indigo-600 text-white shadow-lg shadow-purple-500/25' 
                                         : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80' }}">
                                <div class="flex items-center justify-center w-8 h-8 rounded-lg {{ request()->routeIs('reports.*') ? 'bg-white/20' : 'bg-rose-100 group-hover:bg-rose-200' }} mr-3">
                                    <svg class="w-4 h-4 {{ request()->routeIs('reports.*') ? 'text-white' : 'text-rose-600' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M3 3v18h18"/>
                                        <path d="m19 9-5 5-4-4-3 3"/>
                                    </svg>
                                </div>
                                Reports & Analytics
                            </a>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <!-- User Profile Section -->
            @if(Auth::check())
            <div class="flex items-center space-x-4">
                <!-- Notifications -->
                <a href="{{ route('notifications.index') }}" class="relative group p-3 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800/60 transition-all duration-300 hover:shadow-lg hover:shadow-slate-900/20">
                    <span class="sr-only">View notifications</span>
                    <svg class="h-5 w-5 transition-transform duration-300 group-hover:scale-110" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/>
                        <path d="m13.73 21a2 2 0 0 1-3.46 0"/>
                    </svg>
                    <!-- Notification badge with glow -->
                    @if(Auth::user()->getUnreadNotificationCount() > 0)
                    <span class="absolute -top-1 -right-1 h-3 w-3 bg-gradient-to-r from-red-500 to-red-600 rounded-full shadow-lg shadow-red-500/30">
                        <span class="absolute inset-0 h-3 w-3 bg-red-400 rounded-full animate-ping"></span>
                    </span>
                    @endif
                </a>

                <!-- Profile dropdown -->
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" 
                            class="flex items-center space-x-3 text-sm rounded-2xl transition-all duration-300 p-2 hover:bg-slate-800/60 hover:shadow-lg hover:shadow-slate-900/20"
                            :class="{ 'bg-slate-800/60 shadow-lg': open }">
                        <div class="h-10 w-10 rounded-xl bg-gradient-to-br from-blue-500 via-purple-500 to-indigo-600 flex items-center justify-center text-white font-bold text-sm shadow-xl shadow-purple-500/20 ring-2 ring-white/10">
                            {{ substr(Auth::user()->name, 0, 1) }}
                        </div>
                        <div class="text-left hidden sm:block">
                            <div class="text-sm font-semibold text-white">{{ Auth::user()->name }}</div>
                            <div class="text-xs text-slate-400 font-medium">{{ ucfirst(str_replace('_', ' ', Auth::user()->role)) }}</div>
                        </div>
                        <svg class="h-4 w-4 text-slate-400 transition-transform duration-300" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="m6 9 6 6 6-6"/>
                        </svg>
                    </button>

                    <!-- Profile Dropdown -->
                    <div x-show="open" 
                         x-cloak
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
                            class="group inline-flex items-center justify-center p-3 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800/60 transition-all duration-300 hover:shadow-lg hover:shadow-slate-900/20"
                            :class="{ 'bg-slate-800/60 shadow-lg': open }">
                        <span class="sr-only">Open main menu</span>
                        <svg class="h-6 w-6 transition-transform duration-300 group-hover:scale-110" :class="{'hidden': open, 'block': !open }" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <line x1="4" x2="20" y1="12" y2="12"/>
                            <line x1="4" x2="20" y1="6" y2="6"/>
                            <line x1="4" x2="20" y1="18" y2="18"/>
                        </svg>
                        <svg class="h-6 w-6 transition-transform duration-300 group-hover:scale-110" :class="{'block': open, 'hidden': !open }" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
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
    <div x-show="open" x-cloak class="lg:hidden">
        <div class="px-4 pt-4 pb-6 space-y-3 bg-gradient-to-b from-slate-800 to-slate-900 border-t border-slate-700/50 backdrop-blur-sm">
            <a href="{{ route('dashboard') }}" 
               class="flex items-center px-4 py-3 rounded-lg text-base font-medium transition-colors duration-200 {{ request()->routeIs('dashboard') ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                <svg class="w-5 h-5 mr-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                    <polyline points="9,22 9,12 15,12 15,22"/>
                </svg>
                Dashboard
            </a>

            @if(Auth::check() && in_array(Auth::user()->role, ['manager', 'student_services']))
            <a href="{{ route('enquiries.index') }}" 
               class="flex items-center px-4 py-3 rounded-lg text-base font-medium transition-colors duration-200 {{ request()->routeIs('enquiries.*') ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                <svg class="w-5 h-5 mr-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Enquiries
            </a>
            
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