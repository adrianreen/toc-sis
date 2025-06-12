{{-- resources/views/notifications/create-announcement.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Create System Announcement
            </h2>
            <a href="{{ route('notifications.admin') }}" 
               class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                Back to Dashboard
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white shadow-sm rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Send Announcement</h3>
                    <p class="mt-1 text-sm text-gray-600">
                        Create a system-wide announcement that will be sent to selected users and appear in their notification feed.
                    </p>
                </div>

                <form method="POST" action="{{ route('notifications.announcement') }}" class="px-6 py-4">
                    @csrf

                    <!-- Title -->
                    <div class="mb-6">
                        <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                            Announcement Title *
                        </label>
                        <input type="text" 
                               id="title" 
                               name="title" 
                               value="{{ old('title') }}" 
                               required
                               maxlength="255"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                               placeholder="Enter announcement title">
                        <p class="mt-1 text-xs text-gray-500">Keep it clear and concise (max 255 characters)</p>
                    </div>

                    <!-- Target Audience -->
                    <div class="mb-6">
                        <label for="target_audience" class="block text-sm font-medium text-gray-700 mb-2">
                            Target Audience *
                        </label>
                        <select id="target_audience" 
                                name="target_audience" 
                                required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Select audience...</option>
                            <option value="all" {{ old('target_audience') === 'all' ? 'selected' : '' }}>
                                Everyone (All Users)
                            </option>
                            <option value="students" {{ old('target_audience') === 'students' ? 'selected' : '' }}>
                                Students Only
                            </option>
                            <option value="staff" {{ old('target_audience') === 'staff' ? 'selected' : '' }}>
                                Staff Only (Teachers, Managers, Student Services)
                            </option>
                        </select>
                    </div>

                    <!-- Message -->
                    <div class="mb-6">
                        <label for="message" class="block text-sm font-medium text-gray-700 mb-2">
                            Message *
                        </label>
                        <textarea id="message" 
                                  name="message" 
                                  rows="6" 
                                  required
                                  maxlength="1000"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                  placeholder="Enter your announcement message...">{{ old('message') }}</textarea>
                        <div class="mt-1 flex justify-between">
                            <p class="text-xs text-gray-500">Maximum 1000 characters</p>
                            <p class="text-xs text-gray-500" id="char-count">0 / 1000</p>
                        </div>
                    </div>

                    <!-- Optional Action URL -->
                    <div class="mb-6">
                        <label for="action_url" class="block text-sm font-medium text-gray-700 mb-2">
                            Action URL (Optional)
                        </label>
                        <input type="url" 
                               id="action_url" 
                               name="action_url" 
                               value="{{ old('action_url') }}" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                               placeholder="https://example.com/more-info">
                        <p class="mt-1 text-xs text-gray-500">
                            Optional link where users can get more information. Must be a valid URL starting with http:// or https://
                        </p>
                    </div>

                    <!-- Preview Section -->
                    <div class="mb-6 p-4 bg-gray-50 rounded-lg border">
                        <h4 class="text-sm font-medium text-gray-700 mb-2">Preview</h4>
                        <div class="bg-white p-4 rounded border shadow-sm">
                            <div class="flex items-start space-x-3">
                                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path>
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <h5 class="font-medium text-gray-900" id="preview-title">Announcement Title</h5>
                                    <p class="text-sm text-gray-600 mt-1" id="preview-message">Your message will appear here...</p>
                                    <p class="text-xs text-gray-500 mt-2">Just now • System Announcement</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex justify-end space-x-3">
                        <a href="{{ route('notifications.admin') }}" 
                           class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Cancel
                        </a>
                        <button type="submit" 
                                class="px-4 py-2 bg-blue-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Send Announcement
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- JavaScript for live preview and character counting -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const titleInput = document.getElementById('title');
            const messageInput = document.getElementById('message');
            const previewTitle = document.getElementById('preview-title');
            const previewMessage = document.getElementById('preview-message');
            const charCount = document.getElementById('char-count');

            // Update preview when inputs change
            titleInput.addEventListener('input', function() {
                previewTitle.textContent = this.value || 'Announcement Title';
            });

            messageInput.addEventListener('input', function() {
                previewMessage.textContent = this.value || 'Your message will appear here...';
                charCount.textContent = this.value.length + ' / 1000';
                
                // Change color when approaching limit
                if (this.value.length > 900) {
                    charCount.classList.add('text-red-500');
                } else if (this.value.length > 800) {
                    charCount.classList.add('text-yellow-500');
                } else {
                    charCount.classList.remove('text-red-500', 'text-yellow-500');
                }
            });

            // Initialize character count
            if (messageInput.value) {
                messageInput.dispatchEvent(new Event('input'));
            }
        });
    </script>
</x-app-layout>