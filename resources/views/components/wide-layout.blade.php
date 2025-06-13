@props([
    'title' => null,
    'subtitle' => null,
    'actions' => null
])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ? $title . ' - ' : '' }}{{ config('app.name', 'TOC SIS') }}</title>

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
    </style>
</head>

<body class="font-sans antialiased bg-slate-50">
    <div class="min-h-screen">
        @include('layouts.navigation')

        <!-- Page Header -->
        @if($title || $subtitle || $actions)
        <header class="bg-white shadow-sm border-b border-slate-200">
            <div class="mx-auto px-6 py-4">
                <div class="flex justify-between items-center">
                    <div>
                        @if($title)
                            <h1 class="text-2xl font-bold text-slate-900">{{ $title }}</h1>
                        @endif
                        @if($subtitle)
                            <p class="mt-1 text-sm text-slate-600">{{ $subtitle }}</p>
                        @endif
                    </div>
                    @if($actions)
                        <div class="flex items-center space-x-3">
                            {{ $actions }}
                        </div>
                    @endif
                </div>
            </div>
        </header>
        @endif

        <!-- Page Content -->
        <main class="flex-1">
            <div class="max-w-none mx-auto px-4 sm:px-6 lg:px-8 py-8">
                {{ $slot }}
            </div>
        </main>

        <!-- Footer -->
        <footer class="bg-white border-t border-slate-200 mt-auto">
            <div class="max-w-none mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <div class="flex justify-between items-center text-sm text-slate-500">
                    <p>&copy; {{ date('Y') }} The Open College. Student Information System.</p>
                    <p class="hidden sm:block">Powered by Laravel & Tailwind CSS</p>
                </div>
            </div>
        </footer>
    </div>

    <!-- Loading Overlay Template -->
    <div id="loading-overlay" class="fixed inset-0 bg-slate-900/20 backdrop-blur-sm z-50 hidden">
        <div class="flex items-center justify-center min-h-screen">
            <div class="bg-white rounded-lg shadow-xl p-6 flex items-center space-x-4">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-toc-600"></div>
                <span class="text-slate-700 font-medium">Loading...</span>
            </div>
        </div>
    </div>
</body>
</html>