{{-- resources/views/reports/dashboard.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            System Dashboard
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Statistics Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-sm font-medium text-gray-500">Total Students</div>
                        <div class="mt-1 text-3xl font-semibold text-gray-900">{{ $stats['total_students'] }}</div>
                        <div class="mt-2 text-sm text-gray-600">
                            <span class="text-green-600">{{ $stats['active_students'] }} active</span>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-sm font-medium text-gray-500">Deferred Students</div>
                        <div class="mt-1 text-3xl font-semibold text-gray-900">{{ $stats['deferred_students'] }}</div>
                        <div class="mt-2 text-sm text-gray-600">
                            <span class="text-yellow-600">{{ $stats['pending_deferrals'] }} pending</span>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-sm font-medium text-gray-500">Active Programmes</div>
                        <div class="mt-1 text-3xl font-semibold text-gray-900">{{ $stats['total_programmes'] }}</div>
                        <div class="mt-2 text-sm text-gray-600">
                            <span class="text-blue-600">{{ $stats['active_cohorts'] }} cohorts</span>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-sm font-medium text-gray-500">Pending Actions</div>
                        <div class="mt-1 text-3xl font-semibold text-gray-900">
                            {{ $stats['pending_deferrals'] + $stats['pending_extensions'] }}
                        </div>
                        <div class="mt-2 text-sm text-gray-600">
                            <span class="text-orange-600">Require attention</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Programme Statistics -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Programme Enrolments</h3>
                    <div class="space-y-4">
                        @foreach($programmeStats as $programme)
                            <div class="flex items-center justify-between">
                                <div>
                                    <span class="font-medium">{{ $programme->code }} - {{ $programme->title }}</span>
                                    <span class="text-sm text-gray-500 ml-2">({{ ucfirst(str_replace('_', ' ', $programme->enrolment_type)) }})</span>
                                </div>
                                <div class="flex items-center">
                                    <span class="text-2xl font-semibold text-gray-900 mr-2">{{ $programme->enrolments_count }}</span>
                                    <span class="text-sm text-gray-500">active students</span>
                                </div>
                            </div>
                            @if(!$loop->last)
                                <div class="border-b"></div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Recent Activities -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Recent Activities</h3>
                    <div class="space-y-3">
                        @foreach($recentActivities as $activity)
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <div class="h-8 w-8 rounded-full bg-gray-100 flex items-center justify-center">
                                        <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-3 flex-1">
                                    <p class="text-sm text-gray-900">
                                        <span class="font-medium">{{ $activity->causer?->name ?? 'System' }}</span>
                                        {{ $activity->description }}
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        {{ $activity->created_at->diffForHumans() }}
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>