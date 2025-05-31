{{-- resources/views/dashboard-student-error.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Student Dashboard
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-12 text-center">
                    <div class="w-24 h-24 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-12 h-12 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-semibold text-gray-900 mb-4">Student Record Not Found</h3>
                    <p class="text-gray-600 mb-6 max-w-md mx-auto">
                        {{ $message }}
                    </p>
                    
                    <div class="bg-gray-50 rounded-lg p-4 mb-6 max-w-md mx-auto">
                        <h4 class="font-medium text-gray-900 mb-2">Your Account Details:</h4>
                        <p class="text-sm text-gray-600">Name: {{ $user->name }}</p>
                        <p class="text-sm text-gray-600">Email: {{ $user->email }}</p>
                        <p class="text-sm text-gray-600">Role: {{ ucfirst($user->role) }}</p>
                        <p class="text-sm text-gray-600">User ID: {{ $user->id }}</p>
                        <p class="text-sm text-gray-600">Student ID Link: {{ $user->student_id ?? 'Not set' }}</p>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                        <a href="mailto:student.services@theopencollege.com" 
                           class="inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                            Contact Student Services
                        </a>
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="inline-flex items-center justify-center px-6 py-3 border border-gray-300 text-base font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                Sign Out
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>