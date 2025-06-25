{{-- resources/views/layouts/navigation.blade.php - Updated {{ date('Y-m-d H:i:s') }} --}}
<nav class="bg-white border-b border-gray-200 sticky top-0 z-50 shadow-sm" x-data="{ open: false, userOpen: false, adminOpen: false }">
    <div class="w-full px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <!-- Logo Section -->
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <a href="{{ route('dashboard') }}" class="flex items-center group transition-all duration-300 hover:scale-105">
                        <img src="{{ asset('images/logo-gold.png') }}" alt="TOC SIS Logo" class="h-11 w-auto filter drop-shadow-lg transition-all duration-300 group-hover:drop-shadow-xl">

                    </a>
                </div>
            </div>

            <!-- Main Navigation -->
            <div class="hidden lg:flex lg:items-center lg:space-x-1">
                <!-- Dashboard -->
                <a href="{{ route('dashboard') }}" 
                   class="group relative flex items-center px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 
                          {{ request()->routeIs('dashboard') 
                             ? 'bg-toc-50 text-toc-700 border border-toc-200 shadow-sm' 
                             : 'text-slate-700 hover:text-slate-900 hover:bg-slate-50' }}">
                    
                    <!-- Active state glow -->
                    @if(request()->routeIs('dashboard'))
                        <div class="absolute inset-0 rounded-xl bg-gradient-to-r from-toc-500/10 to-toc-400/10 blur-sm"></div>
                    @endif
                    
                    <svg class="w-4 h-4 mr-3 {{ request()->routeIs('dashboard') ? 'text-toc-600' : 'text-slate-500 group-hover:text-slate-700' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                        <polyline points="9,22 9,12 15,12 15,22"/>
                    </svg>
                    <span>Dashboard</span>
                </a>

                @if(Auth::check() && in_array(Auth::user()->role, ['manager', 'student_services']))
                <!-- Students -->
                <a href="{{ route('students.index') }}" 
                   class="group relative flex items-center px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 
                          {{ request()->routeIs('students.*') 
                             ? 'bg-toc-50 text-toc-700 border border-toc-200 shadow-sm' 
                             : 'text-slate-700 hover:text-slate-900 hover:bg-slate-50' }}">
                    
                    <svg class="w-4 h-4 mr-3 {{ request()->routeIs('students.*') ? 'text-toc-600' : 'text-slate-500 group-hover:text-slate-700' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                        <circle cx="9" cy="7" r="4"/>
                    </svg>
                    <span>Students</span>
                </a>
                @endif

                @if(Auth::check() && in_array(Auth::user()->role, ['manager', 'student_services']))
                <!-- Student Services -->
                <div class="relative" x-data="{ open: false }" 
                     @mouseenter="open = true" 
                     @mouseleave="open = false">
                    <button @click="open = !open" 
                            class="group relative flex items-center px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200
                                   {{ request()->routeIs('enquiries.*', 'deferrals.*', 'students.recycle-bin') 
                                      ? 'bg-toc-50 text-toc-700 border border-toc-200 shadow-sm' 
                                      : 'text-slate-700 hover:text-slate-900 hover:bg-slate-50' }}"
                            :class="{ 'bg-slate-100': open && !{{ request()->routeIs('enquiries.*', 'deferrals.*', 'students.recycle-bin') ? 'true' : 'false' }} }">
                        
                        <!-- Active state glow -->
                        @if(request()->routeIs('enquiries.*', 'deferrals.*', 'students.recycle-bin'))
                            <div class="absolute inset-0 rounded-xl bg-gradient-to-r from-toc-500/10 to-toc-400/10 blur-sm"></div>
                        @endif
                        
                        <svg class="w-4 h-4 mr-3 {{ request()->routeIs('enquiries.*', 'deferrals.*', 'students.recycle-bin') ? 'text-toc-600' : 'text-slate-500 group-hover:text-slate-700' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                            <circle cx="9" cy="7" r="4"/>
                            <path d="m22 21-3-3m0 0a2 2 0 0 0 0-4 2 2 0 0 0 0 4z"/>
                        </svg>
                        <span>Student Services</span>
                        <svg class="w-4 h-4 ml-2 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
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
                         class="absolute left-0 mt-3 w-56 rounded-2xl shadow-2xl bg-white/98 backdrop-blur-xl ring-1 ring-slate-200/60 focus:outline-none z-50 border border-white/30">
                        <div class="py-2">
                            <a href="{{ route('enquiries.index') }}" 
                               class="group flex items-center mx-3 px-4 py-3 rounded-xl text-sm font-medium transition-all duration-200
                                      {{ request()->routeIs('enquiries.*') 
                                         ? 'bg-toc-50 text-toc-700 border border-toc-200 shadow-sm' 
                                         : 'text-gray-700 hover:text-slate-900 hover:bg-slate-100' }}">
                                <div class="flex items-center justify-center w-8 h-8 rounded-lg {{ request()->routeIs('enquiries.*') ? 'bg-blue-100' : 'bg-purple-100 group-hover:bg-purple-200' }} mr-3">
                                    <svg class="w-4 h-4 {{ request()->routeIs('enquiries.*') ? 'text-toc-600' : 'text-purple-600' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </div>
                                Enquiries
                            </a>
                            <a href="{{ route('deferrals.index') }}" 
                               class="group flex items-center mx-3 px-4 py-3 rounded-xl text-sm font-medium transition-all duration-200
                                      {{ request()->routeIs('deferrals.*') 
                                         ? 'bg-toc-50 text-toc-700 border border-toc-200 shadow-sm' 
                                         : 'text-gray-700 hover:text-slate-900 hover:bg-slate-100' }}">
                                <div class="flex items-center justify-center w-8 h-8 rounded-lg {{ request()->routeIs('deferrals.*') ? 'bg-blue-100' : 'bg-amber-100 group-hover:bg-amber-200' }} mr-3">
                                    <svg class="w-4 h-4 {{ request()->routeIs('deferrals.*') ? 'text-toc-600' : 'text-amber-600' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <circle cx="12" cy="12" r="10"/>
                                        <polyline points="12,6 12,12 16,14"/>
                                    </svg>
                                </div>
                                Deferrals
                            </a>
                            <a href="{{ route('students.recycle-bin') }}" 
                               class="group flex items-center mx-3 px-4 py-3 rounded-xl text-sm font-medium transition-all duration-200
                                      {{ request()->routeIs('students.recycle-bin') 
                                         ? 'bg-toc-50 text-toc-700 border border-toc-200 shadow-sm' 
                                         : 'text-gray-700 hover:text-slate-900 hover:bg-slate-100' }}">
                                <div class="flex items-center justify-center w-8 h-8 rounded-lg {{ request()->routeIs('students.recycle-bin') ? 'bg-blue-100' : 'bg-red-100 group-hover:bg-red-200' }} mr-3">
                                    <svg class="w-4 h-4 {{ request()->routeIs('students.recycle-bin') ? 'text-toc-600' : 'text-red-600' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M3 6h18"/>
                                        <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/>
                                        <path d="M8 6V4c0-1 1-2 2-2h4c0-1 1-2 2-2v2"/>
                                        <line x1="10" x2="10" y1="11" y2="17"/>
                                        <line x1="14" x2="14" y1="11" y2="17"/>
                                    </svg>
                                </div>
                                Student Recycle Bin
                            </a>
                        </div>
                    </div>
                </div>
                @endif

                @if(Auth::check() && in_array(Auth::user()->role, ['manager','student_services', 'teacher']))
                <!-- Assessment Management Dropdown -->
                <div class="relative" x-data="{ open: false }" 
                     @mouseenter="open = true" 
                     @mouseleave="open = false">
                    <button @click="open = !open" 
                            class="group relative flex items-center px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200
                                   {{ request()->routeIs('assessments.*', 'extensions.*', 'extension-requests.*', 'repeat-assessments.*') 
                                      ? 'bg-toc-50 text-toc-700 border border-toc-200 shadow-sm' 
                                      : 'text-slate-700 hover:text-slate-900 hover:bg-slate-50' }}"
                            :class="{ 'bg-slate-100': open && !{{ request()->routeIs('assessments.*', 'extensions.*', 'extension-requests.*', 'repeat-assessments.*') ? 'true' : 'false' }} }">
                        
                        
                        <svg class="w-4 h-4 mr-3 {{ request()->routeIs('assessments.*', 'extensions.*', 'extension-requests.*', 'repeat-assessments.*') ? 'text-toc-600' : 'text-slate-500 group-hover:text-slate-700' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <rect width="8" height="4" x="8" y="2" rx="1" ry="1"/>
                            <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/>
                            <path d="m9 14 2 2 4-4"/>
                        </svg>
                        <span>Assessments</span>
                        <svg class="w-4 h-4 ml-2 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
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
                         class="absolute left-0 mt-3 w-56 rounded-2xl shadow-2xl bg-white/98 backdrop-blur-xl ring-1 ring-slate-200/60 focus:outline-none z-50 border border-white/30">
                        <div class="py-2">
                            <a href="{{ route('assessments.index') }}" 
                               class="group flex items-center mx-3 px-4 py-3 rounded-xl text-sm font-medium transition-all duration-200
                                      {{ request()->routeIs('assessments.*') 
                                         ? 'bg-toc-50 text-toc-700 border border-toc-200 shadow-sm' 
                                         : 'text-gray-700 hover:text-slate-900 hover:bg-slate-100' }}">
                                <div class="flex items-center justify-center w-8 h-8 rounded-lg {{ request()->routeIs('assessments.*') ? 'bg-blue-100' : 'bg-green-100 group-hover:bg-green-200' }} mr-3">
                                    <svg class="w-4 h-4 {{ request()->routeIs('assessments.*') ? 'text-toc-600' : 'text-green-600' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
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
                                         ? 'bg-toc-50 text-toc-700 border border-toc-200 shadow-sm' 
                                         : 'text-gray-700 hover:text-slate-900 hover:bg-slate-100' }}">
                                <div class="flex items-center justify-center w-8 h-8 rounded-lg {{ request()->routeIs('extensions.*') ? 'bg-blue-100' : 'bg-orange-100 group-hover:bg-orange-200' }} mr-3">
                                    <svg class="w-4 h-4 {{ request()->routeIs('extensions.*') ? 'text-toc-600' : 'text-orange-600' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
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
                                         ? 'bg-toc-50 text-toc-700 border border-toc-200 shadow-sm' 
                                         : 'text-gray-700 hover:text-slate-900 hover:bg-slate-100' }}">
                                <div class="flex items-center justify-center w-8 h-8 rounded-lg {{ request()->routeIs('extension-requests.*') ? 'bg-blue-100' : 'bg-teal-100 group-hover:bg-teal-200' }} mr-3">
                                    <svg class="w-4 h-4 {{ request()->routeIs('extension-requests.*') ? 'text-toc-600' : 'text-teal-600' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        <path d="M12 6v6m0 0v6"/>
                                    </svg>
                                </div>
                                Extension Requests
                            </a>
                            <a href="{{ route('repeat-assessments.index') }}" 
                               class="group flex items-center mx-3 px-4 py-3 rounded-xl text-sm font-medium transition-all duration-200
                                      {{ request()->routeIs('repeat-assessments.*') 
                                         ? 'bg-toc-50 text-toc-700 border border-toc-200 shadow-sm' 
                                         : 'text-gray-700 hover:text-slate-900 hover:bg-slate-100' }}">
                                <div class="flex items-center justify-center w-8 h-8 rounded-lg {{ request()->routeIs('repeat-assessments.*') ? 'bg-blue-100' : 'bg-red-100 group-hover:bg-red-200' }} mr-3">
                                    <svg class="w-4 h-4 {{ request()->routeIs('repeat-assessments.*') ? 'text-toc-600' : 'text-red-600' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                    </svg>
                                </div>
                                Repeat Assessments
                            </a>
                        </div>
                    </div>
                </div>
                @endif

                @if(Auth::check() && Auth::user()->role === 'manager')
                <!-- Administration Dropdown -->
                <div class="relative" x-data="{ open: false }" 
                     @mouseenter="open = true" 
                     @mouseleave="open = false">
                    <button @click="open = !open" 
                            class="group relative flex items-center px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200
                                   {{ request()->routeIs('programmes.*', 'programme-instances.*', 'modules.*', 'module-instances.*', 'reports.*', 'notifications.admin', 'notifications.announcement', 'admin.email-templates.*', 'admin.system-health.*', 'moodle.*', 'policies.manage', 'policies.create', 'policies.edit') 
                                      ? 'bg-toc-50 text-toc-700 border border-toc-200 shadow-sm' 
                                      : 'text-slate-700 hover:text-slate-900 hover:bg-slate-50' }}"
                            :class="{ 'bg-slate-100': open && !{{ request()->routeIs('programmes.*', 'programme-instances.*', 'modules.*', 'module-instances.*', 'reports.*', 'notifications.admin', 'notifications.announcement', 'admin.email-templates.*', 'admin.system-health.*', 'moodle.*', 'policies.manage', 'policies.create', 'policies.edit') ? 'true' : 'false' }} }">
                        
                        
                        <svg class="w-4 h-4 mr-3 {{ request()->routeIs('programmes.*', 'programme-instances.*', 'modules.*', 'module-instances.*', 'reports.*', 'notifications.admin', 'notifications.announcement', 'admin.email-templates.*', 'admin.system-health.*', 'moodle.*') ? 'text-toc-600' : 'text-slate-500 group-hover:text-slate-700' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M4 20h16a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-7.93a2 2 0 0 1-1.66-.9l-.82-1.2A2 2 0 0 0 7.93 3H4a2 2 0 0 0-2 2v13c0 1.1.9 2 2 2Z"/>
                            <circle cx="12" cy="13" r="1"/>
                        </svg>
                        <span>Administration</span>
                        <svg class="w-4 h-4 ml-2 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
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
                         class="absolute right-0 mt-3 w-64 rounded-2xl shadow-2xl bg-white/98 backdrop-blur-xl ring-1 ring-slate-200/60 focus:outline-none z-50 border border-white/30">
                        <div class="py-2">
                            <a href="{{ route('programmes.index') }}" 
                               class="group flex items-center mx-3 px-4 py-3 rounded-xl text-sm font-medium transition-all duration-200
                                      {{ request()->routeIs('programmes.*') 
                                         ? 'bg-toc-50 text-toc-700 border border-toc-200 shadow-sm' 
                                         : 'text-gray-700 hover:text-slate-900 hover:bg-slate-100' }}">
                                <div class="flex items-center justify-center w-8 h-8 rounded-lg {{ request()->routeIs('programmes.*') ? 'bg-blue-100' : 'bg-indigo-100 group-hover:bg-indigo-200' }} mr-3">
                                    <svg class="w-4 h-4 {{ request()->routeIs('programmes.*') ? 'text-toc-600' : 'text-indigo-600' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/>
                                    </svg>
                                </div>
                                Programmes
                            </a>
                            <a href="{{ route('programme-instances.index') }}" 
                               class="group flex items-center mx-3 px-4 py-3 rounded-xl text-sm font-medium transition-all duration-200
                                      {{ request()->routeIs('programme-instances.*') 
                                         ? 'bg-toc-50 text-toc-700 border border-toc-200 shadow-sm' 
                                         : 'text-gray-700 hover:text-slate-900 hover:bg-slate-100' }}">
                                <div class="flex items-center justify-center w-8 h-8 rounded-lg {{ request()->routeIs('programme-instances.*') ? 'bg-blue-100' : 'bg-purple-100 group-hover:bg-purple-200' }} mr-3">
                                    <svg class="w-4 h-4 {{ request()->routeIs('programme-instances.*') ? 'text-toc-600' : 'text-purple-600' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                    </svg>
                                </div>
                                Programme Instances
                            </a>
                            <a href="{{ route('modules.index') }}" 
                               class="group flex items-center mx-3 px-4 py-3 rounded-xl text-sm font-medium transition-all duration-200
                                      {{ request()->routeIs('modules.*') 
                                         ? 'bg-toc-50 text-toc-700 border border-toc-200 shadow-sm' 
                                         : 'text-gray-700 hover:text-slate-900 hover:bg-slate-100' }}">
                                <div class="flex items-center justify-center w-8 h-8 rounded-lg {{ request()->routeIs('modules.*') ? 'bg-blue-100' : 'bg-emerald-100 group-hover:bg-emerald-200' }} mr-3">
                                    <svg class="w-4 h-4 {{ request()->routeIs('modules.*') ? 'text-toc-600' : 'text-emerald-600' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/>
                                        <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>
                                    </svg>
                                </div>
                                Modules
                            </a>
                            <a href="{{ route('module-instances.index') }}" 
                               class="group flex items-center mx-3 px-4 py-3 rounded-xl text-sm font-medium transition-all duration-200
                                      {{ request()->routeIs('module-instances.*') 
                                         ? 'bg-toc-50 text-toc-700 border border-toc-200 shadow-sm' 
                                         : 'text-gray-700 hover:text-slate-900 hover:bg-slate-100' }}">
                                <div class="flex items-center justify-center w-8 h-8 rounded-lg {{ request()->routeIs('module-instances.*') ? 'bg-blue-100' : 'bg-cyan-100 group-hover:bg-cyan-200' }} mr-3">
                                    <svg class="w-4 h-4 {{ request()->routeIs('module-instances.*') ? 'text-toc-600' : 'text-cyan-600' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
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
                                         ? 'bg-toc-50 text-toc-700 border border-toc-200 shadow-sm' 
                                         : 'text-gray-700 hover:text-slate-900 hover:bg-slate-100' }}">
                                <div class="flex items-center justify-center w-8 h-8 rounded-lg {{ request()->routeIs('notifications.admin', 'notifications.announcement') ? 'bg-blue-100' : 'bg-purple-100 group-hover:bg-purple-200' }} mr-3">
                                    <svg class="w-4 h-4 {{ request()->routeIs('notifications.admin', 'notifications.announcement') ? 'text-toc-600' : 'text-purple-600' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                                    </svg>
                                </div>
                                System Messages
                            </a>
                            @if(in_array(Auth::user()->role, ['manager', 'student_services']))
                                <a href="{{ route('admin.email-templates.index') }}" 
                                   class="group flex items-center mx-3 px-4 py-3 rounded-xl text-sm font-medium transition-all duration-200
                                          {{ request()->routeIs('admin.email-templates.*') 
                                             ? 'bg-toc-50 text-toc-700 border border-toc-200 shadow-sm' 
                                             : 'text-gray-700 hover:text-slate-900 hover:bg-slate-100' }}">
                                    <div class="flex items-center justify-center w-8 h-8 rounded-lg {{ request()->routeIs('admin.email-templates.*') ? 'bg-blue-100' : 'bg-orange-100 group-hover:bg-orange-200' }} mr-3">
                                        <svg class="w-4 h-4 {{ request()->routeIs('admin.email-templates.*') ? 'text-toc-600' : 'text-orange-600' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path d="M4 6l16 0"/>
                                            <path d="M4 12l16 0"/>
                                            <path d="M4 18l7 0"/>
                                            <path d="M16 18l4 0"/>
                                        </svg>
                                    </div>
                                    Email Templates
                                </a>
                            @endif
                            <a href="{{ route('admin.system-health') }}" 
                               class="group flex items-center mx-3 px-4 py-3 rounded-xl text-sm font-medium transition-all duration-200
                                      {{ request()->routeIs('admin.system-health.*') 
                                         ? 'bg-toc-50 text-toc-700 border border-toc-200 shadow-sm' 
                                         : 'text-gray-700 hover:text-slate-900 hover:bg-slate-100' }}">
                                <div class="flex items-center justify-center w-8 h-8 rounded-lg {{ request()->routeIs('admin.system-health.*') ? 'bg-blue-100' : 'bg-emerald-100 group-hover:bg-emerald-200' }} mr-3">
                                    <svg class="w-4 h-4 {{ request()->routeIs('admin.system-health.*') ? 'text-toc-600' : 'text-emerald-600' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                System Health Dashboard
                            </a>
                            <a href="{{ route('reports.dashboard') }}" 
                               class="group flex items-center mx-3 px-4 py-3 rounded-xl text-sm font-medium transition-all duration-200
                                      {{ request()->routeIs('reports.*') 
                                         ? 'bg-toc-50 text-toc-700 border border-toc-200 shadow-sm' 
                                         : 'text-gray-700 hover:text-slate-900 hover:bg-slate-100' }}">
                                <div class="flex items-center justify-center w-8 h-8 rounded-lg {{ request()->routeIs('reports.*') ? 'bg-blue-100' : 'bg-rose-100 group-hover:bg-rose-200' }} mr-3">
                                    <svg class="w-4 h-4 {{ request()->routeIs('reports.*') ? 'text-toc-600' : 'text-rose-600' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M3 3v18h18"/>
                                        <path d="m19 9-5 5-4-4-3 3"/>
                                    </svg>
                                </div>
                                Reports & Analytics
                            </a>
                            @if(in_array(Auth::user()->role, ['manager', 'student_services']))
                                <a href="{{ route('moodle.index') }}" 
                                   class="group flex items-center mx-3 px-4 py-3 rounded-xl text-sm font-medium transition-all duration-200
                                          {{ request()->routeIs('moodle.*') 
                                             ? 'bg-toc-50 text-toc-700 border border-toc-200 shadow-sm' 
                                             : 'text-gray-700 hover:text-slate-900 hover:bg-slate-100' }}">
                                    <div class="flex items-center justify-center w-8 h-8 rounded-lg {{ request()->routeIs('moodle.*') ? 'bg-blue-100' : 'bg-indigo-100 group-hover:bg-indigo-200' }} mr-3">
                                        <svg class="w-4 h-4 {{ request()->routeIs('moodle.*') ? 'text-toc-600' : 'text-indigo-600' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path d="M12 2L2 7l10 5 10-5-10-5z"/>
                                            <path d="m2 17 10 5 10-5"/>
                                            <path d="m2 12 10 5 10-5"/>
                                        </svg>
                                    </div>
                                    Moodle Integration
                                </a>
                            @endif
                            <a href="{{ route('policies.manage') }}" 
                               class="group flex items-center mx-3 px-4 py-3 rounded-xl text-sm font-medium transition-all duration-200
                                      {{ request()->routeIs('policies.manage', 'policies.create', 'policies.edit') 
                                         ? 'bg-toc-50 text-toc-700 border border-toc-200 shadow-sm' 
                                         : 'text-gray-700 hover:text-slate-900 hover:bg-slate-100' }}">
                                <div class="flex items-center justify-center w-8 h-8 rounded-lg {{ request()->routeIs('policies.manage', 'policies.create', 'policies.edit') ? 'bg-blue-100' : 'bg-amber-100 group-hover:bg-amber-200' }} mr-3">
                                    <svg class="w-4 h-4 {{ request()->routeIs('policies.manage', 'policies.create', 'policies.edit') ? 'text-toc-600' : 'text-amber-600' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </div>
                                Policy Management
                            </a>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Policies Link - Available to all authenticated users -->
                <a href="{{ route('policies.index') }}" 
                   class="group relative flex items-center px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 
                          {{ request()->routeIs('policies.index', 'policies.show') 
                             ? 'bg-toc-50 text-toc-700 border border-toc-200 shadow-sm' 
                             : 'text-slate-700 hover:text-slate-900 hover:bg-slate-50' }}">
                    
                    <!-- Active state glow -->
                    @if(request()->routeIs('policies.index', 'policies.show'))
                        <div class="absolute inset-0 rounded-xl bg-gradient-to-r from-toc-500/10 to-toc-400/10 blur-sm"></div>
                    @endif
                    
                    <svg class="w-4 h-4 mr-3 {{ request()->routeIs('policies.index', 'policies.show') ? 'text-toc-600' : 'text-slate-500 group-hover:text-slate-700' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span>Policies</span>
                </a>
            </div>

            <!-- User Profile Section -->
            @if(Auth::check())
            <div class="flex items-center space-x-4">
                <!-- Notifications -->
                <a href="{{ route('notifications.index') }}" class="relative group p-3 rounded-lg text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition-all duration-200">
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
                <div class="relative" x-data="{ open: false }" 
                     @mouseenter="open = true" 
                     @mouseleave="open = false">
                    <button @click="open = !open" 
                            class="flex items-center space-x-3 text-sm rounded-lg transition-all duration-200 p-2 hover:bg-slate-100"
                            :class="{ 'bg-slate-100': open }">
                        <div class="h-10 w-10 rounded-lg bg-blue-600 flex items-center justify-center text-white font-bold text-sm">
                            {{ substr(Auth::user()->name, 0, 1) }}
                        </div>
                        <div class="text-left hidden sm:block">
                            <div class="text-sm font-semibold text-slate-900">{{ Auth::user()->name }}</div>
                            <div class="text-xs text-slate-600 font-medium">{{ ucfirst(str_replace('_', ' ', Auth::user()->role)) }}</div>
                        </div>
                        <svg class="h-4 w-4 text-slate-600 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="m6 9 6 6 6-6"/>
                        </svg>
                    </button>

                    <!-- Profile Dropdown -->
                    <div x-show="open" 
                         x-cloak
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="transform opacity-0 scale-95 translate-y-1"
                         x-transition:enter-end="transform opacity-100 scale-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="transform opacity-100 scale-100 translate-y-0"
                         x-transition:leave-end="transform opacity-0 scale-95 translate-y-1"
                         @click.away="open = false"
                         class="absolute right-0 mt-3 w-72 rounded-xl shadow-2xl bg-white ring-1 ring-slate-200 focus:outline-none z-50 overflow-hidden">
                        
                        <!-- Profile Header -->
                        <div class="px-5 py-6 bg-gradient-to-br from-slate-50 to-slate-100 border-b border-slate-200">
                            <div class="flex items-center space-x-3">
                                <div class="h-12 w-12 rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center text-white font-bold text-lg shadow-lg">
                                    {{ substr(Auth::user()->name, 0, 1) }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="text-sm font-bold text-slate-900 truncate">
                                        {{ Auth::user()->name }}
                                    </h3>
                                    <p class="text-xs text-slate-600 font-medium truncate">
                                        {{ Auth::user()->email }}
                                    </p>
                                    <div class="mt-1">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-toc-100 text-toc-800">
                                            {{ ucfirst(str_replace('_', ' ', Auth::user()->role)) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Menu Items -->
                        <div class="p-3">
                            <div class="space-y-1">
                                @if(Auth::user()->role === 'student')
                                    <!-- Student Profile Link -->
                                    <a href="{{ route('students.profile') }}" class="group flex items-center px-3 py-2.5 text-sm text-slate-700 hover:bg-slate-50 hover:text-slate-900 transition-all duration-200 rounded-lg">
                                        <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-slate-100 group-hover:bg-toc-100 mr-3 transition-colors">
                                            <svg class="w-4 h-4 text-slate-600 group-hover:text-toc-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/>
                                                <circle cx="12" cy="7" r="4"/>
                                            </svg>
                                        </div>
                                        <span class="font-medium text-slate-900">My Profile</span>
                                    </a>
                                @else
                                    <!-- Staff Profile (placeholder for future implementation) -->
                                    <a href="#" class="group flex items-center px-3 py-2.5 text-sm text-slate-700 hover:bg-slate-50 hover:text-slate-900 transition-all duration-200 rounded-lg">
                                        <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-slate-100 group-hover:bg-slate-200 mr-3 transition-colors">
                                            <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/>
                                                <circle cx="12" cy="7" r="4"/>
                                            </svg>
                                        </div>
                                        <span class="font-medium text-slate-900">My Profile</span>
                                    </a>
                                @endif

                                <!-- Notifications -->
                                <a href="{{ route('notifications.index') }}" class="group flex items-center px-3 py-2.5 text-sm text-slate-700 hover:bg-slate-50 hover:text-slate-900 transition-all duration-200 rounded-lg">
                                    <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-slate-100 group-hover:bg-blue-100 mr-3 transition-colors relative">
                                        <svg class="w-4 h-4 text-slate-600 group-hover:text-toc-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/>
                                            <path d="m13.73 21a2 2 0 0 1-3.46 0"/>
                                        </svg>
                                        @if(Auth::user()->getUnreadNotificationCount() > 0)
                                            <span class="absolute -top-1 -right-1 h-3 w-3 bg-red-500 rounded-full ring-2 ring-white"></span>
                                        @endif
                                    </div>
                                    <span class="font-medium text-slate-900 flex-1">Notifications</span>
                                    @if(Auth::user()->getUnreadNotificationCount() > 0)
                                        <span class="bg-red-500 text-white text-xs rounded-full px-2 py-0.5 font-semibold shadow-sm">
                                            {{ Auth::user()->getUnreadNotificationCount() }}
                                        </span>
                                    @endif
                                </a>

                                <!-- Help & Support -->
                                <a href="mailto:support@theopencollege.com" class="group flex items-center px-3 py-2.5 text-sm text-slate-700 hover:bg-slate-50 hover:text-slate-900 transition-all duration-200 rounded-lg">
                                    <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-slate-100 group-hover:bg-emerald-100 mr-3 transition-colors">
                                        <svg class="w-4 h-4 text-slate-600 group-hover:text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <circle cx="12" cy="12" r="10"/>
                                            <path d="M12 16v-4"/>
                                            <path d="M12 8h.01"/>
                                        </svg>
                                    </div>
                                    <span class="font-medium text-slate-900">Help & Support</span>
                                </a>
                            </div>

                            <div class="border-t border-slate-200 my-3"></div>
                            
                            <!-- Sign Out -->
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="group flex items-center w-full px-3 py-2.5 text-sm text-red-700 hover:bg-red-50 hover:text-red-800 transition-all duration-200 rounded-lg">
                                    <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-red-100 group-hover:bg-red-200 mr-3 transition-colors">
                                        <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                                            <polyline points="16,17 21,12 16,7"/>
                                            <line x1="21" x2="9" y1="12" y2="12"/>
                                        </svg>
                                    </div>
                                    <span class="font-medium text-red-900">Sign Out</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Mobile menu button -->
                <div class="lg:hidden">
                    <button @click="open = !open" 
                            class="group inline-flex items-center justify-center p-3 rounded-lg text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition-all duration-200"
                            :class="{ 'bg-slate-100': open }">
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
        <div class="px-4 pt-4 pb-6 space-y-2 bg-white border-t border-gray-200 max-h-[70vh] overflow-y-auto">
            <!-- Dashboard -->
            <a href="{{ route('dashboard') }}" 
               class="flex items-center px-4 py-3 rounded-lg text-base font-medium transition-colors duration-200 {{ request()->routeIs('dashboard') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-50 hover:text-slate-900' }}">
                <svg class="w-5 h-5 mr-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                    <polyline points="9,22 9,12 15,12 15,22"/>
                </svg>
                Dashboard
            </a>

            @if(Auth::check() && in_array(Auth::user()->role, ['manager', 'student_services']))
            <!-- Students -->
            <a href="{{ route('students.index') }}" 
               class="flex items-center px-4 py-3 rounded-lg text-base font-medium transition-colors duration-200 {{ request()->routeIs('students.*') && !request()->routeIs('students.recycle-bin') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-50 hover:text-slate-900' }}">
                <svg class="w-5 h-5 mr-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                </svg>
                Students
            </a>

            <!-- Student Services Section -->
            <div class="border-l-2 border-purple-200 pl-4 ml-2 space-y-1">
                <div class="text-xs font-semibold text-purple-600 uppercase tracking-wide px-4 py-2">Student Services</div>
                
                <a href="{{ route('enquiries.index') }}" 
                   class="flex items-center px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200 {{ request()->routeIs('enquiries.*') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-50 hover:text-slate-900' }}">
                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Enquiries
                </a>
                
                <a href="{{ route('deferrals.index') }}" 
                   class="flex items-center px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200 {{ request()->routeIs('deferrals.*') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-50 hover:text-slate-900' }}">
                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10"/>
                        <polyline points="12,6 12,12 16,14"/>
                    </svg>
                    Deferrals
                </a>
                
                <a href="{{ route('students.recycle-bin') }}" 
                   class="flex items-center px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200 {{ request()->routeIs('students.recycle-bin') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-50 hover:text-slate-900' }}">
                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M3 6h18"/>
                        <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/>
                        <path d="M8 6V4c0-1 1-2 2-2h4c0-1 1-2 2-2v2"/>
                        <line x1="10" x2="10" y1="11" y2="17"/>
                        <line x1="14" x2="14" y1="11" y2="17"/>
                    </svg>
                    Student Recycle Bin
                </a>
            </div>
            @endif

            @if(Auth::check() && in_array(Auth::user()->role, ['manager','student_services', 'teacher']))
            <!-- Assessment Management Section -->
            <div class="border-l-2 border-green-200 pl-4 ml-2 space-y-1 mt-4">
                <div class="text-xs font-semibold text-green-600 uppercase tracking-wide px-4 py-2">Assessment Management</div>
                
                <a href="{{ route('assessments.index') }}" 
                   class="flex items-center px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200 {{ request()->routeIs('assessments.*') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-50 hover:text-slate-900' }}">
                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <rect width="8" height="4" x="8" y="2" rx="1" ry="1"/>
                        <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/>
                        <path d="m9 14 2 2 4-4"/>
                    </svg>
                    Grade Management
                </a>
                
                <a href="{{ route('extensions.index') }}" 
                   class="flex items-center px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200 {{ request()->routeIs('extensions.*') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-50 hover:text-slate-900' }}">
                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M8 2v4"/>
                        <path d="M16 2v4"/>
                        <rect width="18" height="18" x="3" y="4" rx="2"/>
                        <path d="M3 10h18"/>
                        <path d="m9 16 2 2 4-4"/>
                    </svg>
                    Extensions (Legacy)
                </a>
                
                <a href="{{ route('extension-requests.staff-index') }}" 
                   class="flex items-center px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200 {{ request()->routeIs('extension-requests.*') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-50 hover:text-slate-900' }}">
                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        <path d="M12 6v6m0 0v6"/>
                    </svg>
                    Extension Requests
                </a>
                
                <a href="{{ route('repeat-assessments.index') }}" 
                   class="flex items-center px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200 {{ request()->routeIs('repeat-assessments.*') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-50 hover:text-slate-900' }}">
                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Repeat Assessments
                </a>
            </div>
            @endif

            @if(Auth::check() && Auth::user()->role === 'manager')
            <!-- Administration Section -->
            <div class="border-l-2 border-indigo-200 pl-4 ml-2 space-y-1 mt-4">
                <div class="text-xs font-semibold text-indigo-600 uppercase tracking-wide px-4 py-2">Administration</div>
                
                <a href="{{ route('programmes.index') }}" 
                   class="flex items-center px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200 {{ request()->routeIs('programmes.*') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-50 hover:text-slate-900' }}">
                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/>
                    </svg>
                    Programmes
                </a>
                
                <a href="{{ route('programme-instances.index') }}" 
                   class="flex items-center px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200 {{ request()->routeIs('programme-instances.*') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-50 hover:text-slate-900' }}">
                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                    Programme Instances
                </a>
                
                <a href="{{ route('modules.index') }}" 
                   class="flex items-center px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200 {{ request()->routeIs('modules.*') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-50 hover:text-slate-900' }}">
                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/>
                        <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>
                    </svg>
                    Modules
                </a>
                
                <a href="{{ route('module-instances.index') }}" 
                   class="flex items-center px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200 {{ request()->routeIs('module-instances.*') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-50 hover:text-slate-900' }}">
                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M12.83 2.18a2 2 0 0 0-1.66 0L2.6 6.08a1 1 0 0 0 0 1.83l8.58 3.91a2 2 0 0 0 1.66 0l8.58-3.9a1 1 0 0 0 0-1.83z"/>
                        <path d="M22 17.65c0 .42-.2.8-.53 1.05L12.83 22a2 2 0 0 1-1.66 0L2.53 18.7A1.33 1.33 0 0 1 2 17.65"/>
                        <path d="M22 12.65c0 .42-.2.8-.53 1.05L12.83 17a2 2 0 0 1-1.66 0L2.53 13.7A1.33 1.33 0 0 1 2 12.65"/>
                    </svg>
                    Module Instances
                </a>
                
                <!-- System & Tools -->
                <div class="border-t border-gray-100 my-2 pt-2">
                    <a href="{{ route('notifications.admin') }}" 
                       class="flex items-center px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200 {{ request()->routeIs('notifications.admin', 'notifications.announcement') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-50 hover:text-slate-900' }}">
                        <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                        </svg>
                        System Messages
                    </a>
                    
                    @if(in_array(Auth::user()->role, ['manager', 'student_services']))
                    <a href="{{ route('admin.email-templates.index') }}" 
                       class="flex items-center px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200 {{ request()->routeIs('admin.email-templates.*') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-50 hover:text-slate-900' }}">
                        <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M4 6l16 0"/>
                            <path d="M4 12l16 0"/>
                            <path d="M4 18l7 0"/>
                            <path d="M16 18l4 0"/>
                        </svg>
                        Email Templates
                    </a>
                    @endif
                    
                    <a href="{{ route('reports.dashboard') }}" 
                       class="flex items-center px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200 {{ request()->routeIs('reports.*') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-50 hover:text-slate-900' }}">
                        <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M3 3v18h18"/>
                            <path d="m19 9-5 5-4-4-3 3"/>
                        </svg>
                        Reports & Analytics
                    </a>
                    
                    @if(in_array(Auth::user()->role, ['manager', 'student_services']))
                    <a href="{{ route('moodle.index') }}" 
                       class="flex items-center px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200 {{ request()->routeIs('moodle.*') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-50 hover:text-slate-900' }}">
                        <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M12 2L2 7l10 5 10-5-10-5z"/>
                            <path d="m2 17 10 5 10-5"/>
                            <path d="m2 12 10 5 10-5"/>
                        </svg>
                        Moodle Integration
                    </a>
                    @endif
                </div>
            </div>
            @endif

            <!-- Mobile Profile Section -->
            <div class="border-t border-gray-200 pt-4 mt-6">
                <div class="px-4">
                    <div class="flex items-center space-x-3 mb-3">
                        <div class="h-10 w-10 rounded-lg bg-blue-600 flex items-center justify-center text-white font-bold text-sm">
                            {{ substr(Auth::user()->name, 0, 1) }}
                        </div>
                        <div>
                            <div class="text-sm font-semibold text-slate-900">{{ Auth::user()->name }}</div>
                            <div class="text-xs text-slate-600 font-medium">{{ ucfirst(str_replace('_', ' ', Auth::user()->role)) }}</div>
                        </div>
                    </div>
                    
                    <div class="space-y-1">
                        <a href="{{ route('notifications.index') }}" class="flex items-center px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-lg transition-colors duration-150">
                            <svg class="w-4 h-4 mr-3 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/>
                                <path d="m13.73 21a2 2 0 0 1-3.46 0"/>
                            </svg>
                            Notifications
                            @if(Auth::user()->getUnreadNotificationCount() > 0)
                                <span class="ml-auto bg-red-500 text-white text-xs rounded-full px-2 py-0.5">{{ Auth::user()->getUnreadNotificationCount() }}</span>
                            @endif
                        </a>
                        
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="flex items-center w-full px-3 py-2 text-sm text-red-700 hover:bg-red-50 rounded-lg transition-colors duration-150">
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
        </div>
    </div>
</nav>