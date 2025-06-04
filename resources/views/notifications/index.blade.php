<x-app-layout>
    <style>
        .line-clamp-2 {
            overflow: hidden;
            display: -webkit-box;
            -webkit-box-orient: vertical;
            -webkit-line-clamp: 2;
        }
        
        .notification-clickable {
            cursor: pointer !important;
        }
        
        .notification-clickable * {
            cursor: pointer !important;
        }
    </style>
    <div class="py-6 sm:py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Header -->
            <div class="mb-8 flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Notifications</h1>
                    <p class="mt-2 text-gray-600">Stay up to date with your academic progress</p>
                </div>
                
                @if(auth()->user()->getUnreadNotificationCount() > 0)
                <div class="flex items-center space-x-4">
                    <span class="text-sm text-gray-500">
                        <span class="font-medium">{{ auth()->user()->getUnreadNotificationCount() }}</span> unread
                    </span>
                    <form method="POST" action="{{ route('notifications.mark-all-read') }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit" 
                                onclick="return confirm('Mark all notifications as read?')"
                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-toc-600 hover:bg-toc-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-toc-500 transition-colors duration-200">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Mark All Read
                        </button>
                    </form>
                </div>
                @endif
            </div>

            <!-- Notifications List -->
            @if($notifications->count() > 0)
            <div class="space-y-3">
                @foreach($notifications as $notification)
                <div class="bg-white shadow-sm rounded-lg border border-gray-200 transition-all duration-200 
                    {{ $notification->is_read ? 'opacity-75' : 'border-l-4 border-l-toc-500' }}
                    {{ $notification->action_url ? 'hover:shadow-lg hover:border-toc-300 notification-clickable' : '' }}">
                    
                    <!-- Main clickable area -->
                    @if($notification->action_url)
                    <form method="POST" action="{{ route('notifications.read', $notification) }}" class="block">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="w-full text-left p-6 hover:bg-gradient-to-r hover:from-toc-50 hover:to-blue-50 transition-all duration-200 group">
                    @else
                    <div class="p-6">
                    @endif
                    
                        <div class="flex items-start space-x-4">
                            <!-- Notification Type Icon -->
                            @switch($notification->type)
                                @case('assessment_due')
                                    <div class="flex-shrink-0 p-2 bg-yellow-100 rounded-full {{ $notification->action_url ? 'group-hover:bg-yellow-200 transition-colors duration-200' : '' }}">
                                        <svg class="w-5 h-5 text-yellow-600 {{ $notification->action_url ? 'group-hover:text-yellow-700' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                    @break
                                @case('grade_released')
                                    <div class="flex-shrink-0 p-2 bg-green-100 rounded-full {{ $notification->action_url ? 'group-hover:bg-green-200 transition-colors duration-200' : '' }}">
                                        <svg class="w-5 h-5 text-green-600 {{ $notification->action_url ? 'group-hover:text-green-700' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                    @break
                                @case('extension_approved')
                                @case('deferral_approved')
                                    <div class="flex-shrink-0 p-2 bg-blue-100 rounded-full {{ $notification->action_url ? 'group-hover:bg-blue-200 transition-colors duration-200' : '' }}">
                                        <svg class="w-5 h-5 text-blue-600 {{ $notification->action_url ? 'group-hover:text-blue-700' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                    @break
                                @case('announcement')
                                    <div class="flex-shrink-0 p-2 bg-purple-100 rounded-full {{ $notification->action_url ? 'group-hover:bg-purple-200 transition-colors duration-200' : '' }}">
                                        <svg class="w-5 h-5 text-purple-600 {{ $notification->action_url ? 'group-hover:text-purple-700' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path>
                                        </svg>
                                    </div>
                                    @break
                                @default
                                    <div class="flex-shrink-0 p-2 bg-gray-100 rounded-full {{ $notification->action_url ? 'group-hover:bg-gray-200 transition-colors duration-200' : '' }}">
                                        <svg class="w-5 h-5 text-gray-600 {{ $notification->action_url ? 'group-hover:text-gray-700' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                            @endswitch
                            
                            <!-- Notification Content -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center space-x-2 mb-1">
                                    <h3 class="text-base font-semibold {{ !$notification->is_read ? 'text-gray-900' : 'text-gray-700' }} 
                                        {{ $notification->action_url ? 'group-hover:text-toc-700' : '' }}">
                                        {{ $notification->title }}
                                    </h3>
                                    
                                    @if(!$notification->is_read)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-toc-100 text-toc-700">
                                        New
                                    </span>
                                    @endif
                                    
                                    @if($notification->action_url)
                                    <svg class="w-4 h-4 text-toc-500 group-hover:text-toc-600 transition-colors duration-200 group-hover:translate-x-1 transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                                    </svg>
                                    @endif
                                </div>
                                
                                <p class="text-sm text-gray-600 mb-2 line-clamp-2 {{ $notification->action_url ? 'group-hover:text-gray-700' : '' }}">{{ $notification->message }}</p>
                                
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-gray-500">{{ $notification->created_at->diffForHumans() }}</span>
                                    
                                    @if($notification->action_url)
                                    <div class="flex items-center space-x-1">
                                        <span class="text-xs text-toc-600 font-medium group-hover:text-toc-700 transition-colors duration-200">
                                            {{ $notification->is_read ? 'View details' : 'View & mark read' }}
                                        </span>
                                        <svg class="w-3 h-3 text-toc-500 group-hover:text-toc-600 transition-all duration-200 group-hover:translate-x-0.5 transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                        </svg>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                    @if($notification->action_url)
                        </button>
                    </form>
                    @else
                    </div>
                    @endif
                    
                    <!-- Mark as read button for notifications without action URL -->
                    @if(!$notification->action_url && !$notification->is_read)
                    <div class="px-6 pb-4">
                        <form method="POST" action="{{ route('notifications.read', $notification) }}" class="inline">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="text-xs text-gray-500 hover:text-gray-700 underline">
                                Mark as read
                            </button>
                        </form>
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
            
            <!-- Pagination -->
            <div class="mt-8">
                {{ $notifications->links() }}
            </div>
            @else
            <div class="text-center py-16">
                <div class="mx-auto w-24 h-24 bg-gradient-to-br from-toc-100 to-blue-100 rounded-full flex items-center justify-center mb-4">
                    <svg class="h-10 w-10 text-toc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM11 21H5a2 2 0 01-2-2V5a2 2 0 012-2h5m5 5V7a1 1 0 00-1-1H9a1 1 0 00-1 1v1"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">All caught up!</h3>
                <p class="text-sm text-gray-500 max-w-sm mx-auto">You don't have any notifications right now. We'll let you know when there's something important to see.</p>
            </div>
            @endif
        </div>
    </div>
</x-app-layout>