<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'TOC SIS') }}</title>

    <!-- Google Fonts - Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 3px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
        
        /* Smooth transitions */
        * {
            transition-duration: 150ms;
            transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        /* Focus styles */
        .focus-visible {
            outline: 2px solid #3b82f6;
            outline-offset: 2px;
        }
    </style>
</head>
<body class="font-inter antialiased bg-slate-50 text-slate-900 selection:bg-blue-100 selection:text-blue-900">
    <div class="min-h-screen flex flex-col">
        @include('layouts.navigation')

        <!-- Page Heading -->
        @isset($header)
            <header class="bg-white border-b border-slate-200 shadow-sm">
                <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
        @endisset

        <!-- Page Content -->
        <main class="flex-1">
            {{ $slot }}
        </main>

        <!-- Footer -->
        <footer class="bg-white border-t border-slate-200 mt-auto">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-slate-500">
                        © {{ date('Y') }} The Open College. Student Information System.
                    </div>
                    <div class="text-sm text-slate-500">
                        Version 1.0 | <a href="mailto:support@theopencollege.com" class="text-blue-600 hover:text-blue-700">Support</a>
                    </div>
                </div>
            </div>
        </footer>

        {{-- WARNING: DEVELOPMENT ONLY - REMOVE THIS LINE BEFORE DEPLOYMENT --}}
        <x-dev-role-switcher />
    </div>

    <!-- Loading Overlay Template -->
    <div id="loading-overlay" class="fixed inset-0 bg-slate-900/20 backdrop-blur-sm z-50 hidden">
        <div class="flex items-center justify-center min-h-screen">
            <div class="bg-white rounded-xl shadow-lg p-6 max-w-sm mx-4">
                <div class="flex items-center space-x-4">
                    <div class="animate-spin rounded-full h-8 w-8 border-2 border-blue-600 border-t-transparent"></div>
                    <div>
                        <div class="font-medium text-slate-900">Processing...</div>
                        <div class="text-sm text-slate-500">Please wait a moment</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>