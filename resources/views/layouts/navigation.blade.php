{{-- resources/views/layouts/navigation.blade.php --}}
<nav class="bg-white border-b border-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <span class="text-xl font-semibold">TOC SIS</span>
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('dashboard') ? 'border-indigo-400 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} text-sm font-medium">
                        Dashboard
                    </a>

                    @if(in_array(Auth::user()->role, ['manager', 'student_services']))
                        <a href="{{ route('students.index') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('students.*') ? 'border-indigo-400 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} text-sm font-medium">
                            Students
                        </a>
                        {{-- START: ADDED DEFERRALS LINK --}}
                        <a href="{{ route('deferrals.index') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('deferrals.*') ? 'border-indigo-400 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} text-sm font-medium">
                            Deferrals
                        </a>
                        {{-- END: ADDED DEFERRALS LINK --}}
                    @endif

                    @if(Auth::user()->role === 'manager')
                        <a href="{{ route('programmes.index') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('programmes.*') ? 'border-indigo-400 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} text-sm font-medium">
                            Programmes
                        </a>
                        <a href="{{ route('cohorts.index') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('cohorts.*') ? 'border-indigo-400 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} text-sm font-medium">
                            Cohorts
                        </a>
                        <a href="{{ route('modules.index') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('modules.*') ? 'border-indigo-400 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} text-sm font-medium">
                            Modules
                        </a>
                        {{-- START: ADDED MODULE INSTANCES LINK --}}
                        <a href="{{ route('module-instances.index') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('module-instances.*') ? 'border-indigo-400 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} text-sm font-medium">
                            Module Instances
                        </a>
                        {{-- END: ADDED MODULE INSTANCES LINK --}}
                    @endif
                </div>
            </div>

            <!-- Right side -->
            <div class="flex items-center">
                <span class="text-sm text-gray-500 mr-4">{{ Auth::user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-sm text-gray-500 hover:text-gray-700">
                        Log Out
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>