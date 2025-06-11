<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>TOC Student Information System</title>
    
    <!-- Google Fonts - Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        .gradient-mesh {
            background: linear-gradient(135deg, #14639b 0%, #150d00 100%);
            background-size: 400% 400%;
            animation: gradientShift 15s ease infinite;
        }
        
        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        .float-animation {
            animation: float 6s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
        
        .glow-effect {
            box-shadow: 0 0 40px rgba(59, 130, 246, 0.15);
        }
        
        .bg-dots {
            background-image: radial-gradient(circle, #e2e8f0 1px, transparent 1px);
            background-size: 40px 40px;
        }
    </style>
</head>
<body class="font-inter antialiased">
    <div class="min-h-screen flex">
        <!-- Left Side - Branding & Imagery -->
        <div class="hidden lg:flex lg:w-1/2 gradient-mesh relative overflow-hidden">
            <!-- Background Pattern -->
            <div class="absolute inset-0 bg-dots opacity-10"></div>
            
            <!-- Floating Elements -->
            <div class="absolute top-20 left-20 w-20 h-20 bg-white/10 rounded-full blur-xl float-animation"></div>
            <div class="absolute top-40 right-32 w-32 h-32 bg-white/5 rounded-full blur-2xl float-animation" style="animation-delay: -2s;"></div>
            <div class="absolute bottom-32 left-40 w-24 h-24 bg-white/10 rounded-full blur-xl float-animation" style="animation-delay: -4s;"></div>
            
            <!-- Content -->
            <div class="relative z-10 flex flex-col justify-center px-12 py-16 text-white">
                <div class="max-w-lg">
                    <!-- Logo -->
                    <div class="mb-8">
                        <a href="{{ url('https://www.theopencollege.com') }}">
                            <img src="{{ asset('images/logo.png') }}"
                                 alt="My Company Logo"
                                 class="h-32 w-auto">
                        </a>
                    </div>
                    
                    <!-- Hero Content -->
<h2 class="mb-6">
    <span class="block text-6xl font-extrabold leading-tight">
        TOC-SIS
    </span>
    <span class="block text-4xl font-bold leading-tight" style="color: #ddae59;">
        The Open College Student Information System
    </span>
</h2>
                    
                    <p class="text-xl text-white/90 mb-8 leading-relaxed">
                        Comprehensive student lifecycle management designed specifically for The Open College. 
                        Manage enrolments, track progress, and deliver student success.
                    </p>

                    <!-- Feature Highlights -->
                    <div class="space-y-4">
                        <div class="flex items-center space-x-3">
                            <div class="w-6 h-6 bg-[#ddae59] rounded-full flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <span class="text-white/90">Unified student data management</span>
                        </div>
                        <div class="flex items-center space-x-3">
                            <div class="w-6 h-6 bg-[#ddae59] rounded-full flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <span class="text-white/90">Advanced assessment tracking</span>
                        </div>
                        <div class="flex items-center space-x-3">
                            <div class="w-6 h-6 bg-[#ddae59] rounded-full flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <span class="text-white/90">Automated workflow management</span>
                        </div>
                        <div class="flex items-center space-x-3">
                            <div class="w-6 h-6 bg-[#ddae59] rounded-full flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <span class="text-white/90">Real-time reporting & analytics</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Right Side - Login Form -->
        <div class="flex-1 flex flex-col justify-center px-6 py-12 lg:px-16 bg-slate-50">
            <div class="mx-auto w-full max-w-md">
                <!-- Mobile Logo -->
                <div class="lg:hidden flex items-center justify-center space-x-3 mb-8">
                    <div class="w-10 h-10 bg-gradient-to-br from-toc-500 to-toc-600 rounded-xl flex items-center justify-center shadow-lg">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-slate-900">TOC SIS</h1>
                        <p class="text-slate-600 text-sm">Student Information System</p>
                    </div>
                </div>
                
                <!-- Welcome Text -->
                <div class="text-center mb-8">
                    <h2 class="text-3xl font-bold text-slate-900 mb-2">
                        Welcome Back
                    </h2>
                    <p class="text-slate-600">
                        Sign in to access your Student Information System
                    </p>
                </div>
                
                <!-- Login Card -->
                <div class="bg-white rounded-2xl shadow-lg border border-slate-200/60 p-8 glow-effect">
                    @auth
                        <!-- Already Logged In -->
                        <div class="text-center">
                            <div class="w-16 h-16 bg-gradient-to-br from-green-100 to-green-200 rounded-2xl flex items-center justify-center mx-auto mb-6">
                                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <h3 class="text-xl font-semibold text-slate-900 mb-2">
                                Welcome, {{ Auth::user()->name }}!
                            </h3>
                            <p class="text-slate-600 mb-6">
                                You're successfully logged in to the system.
                            </p>
                            <a href="{{ route('dashboard') }}" 
                               class="w-full bg-gradient-to-r from-blue-500 to-blue-600 text-white py-3 px-6 rounded-xl font-semibold hover:from-blue-600 hover:to-blue-700 transition-all duration-200 transform hover:scale-105 shadow-lg hover:shadow-xl flex items-center justify-center space-x-2 group">
                                <span>Go to Dashboard</span>
                                <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </a>
                        </div>
                    @else
                        <!-- Login Form -->
                        <div class="space-y-6">
                            <!-- Status Messages -->
                            @if(session('error'))
                                <div class="bg-red-50 border border-red-200 rounded-xl p-4">
                                    <div class="flex items-center space-x-3">
                                        <svg class="w-5 h-5 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <p class="text-red-700 text-sm font-medium">{{ session('error') }}</p>
                                    </div>
                                </div>
                            @endif
                            
                            @if(session('success'))
                                <div class="bg-green-50 border border-green-200 rounded-xl p-4">
                                    <div class="flex items-center space-x-3">
                                        <svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                        <p class="text-green-700 text-sm font-medium">{{ session('success') }}</p>
                                    </div>
                                </div>
                            @endif
                            
                            <!-- Microsoft Login Button -->
                            <a href="{{ route('login') }}"
                               class="w-full bg-blue-500 text-white <!-- TEMPORARY: Simple background and text --> py-4 px-6 rounded-xl font-semibold hover:bg-blue-600 transition-all duration-200 transform hover:scale-105 shadow-lg hover:shadow-xl flex items-center justify-center space-x-3 group">
                                <!-- Microsoft Icon Image -->
                                <img src="{{ asset('images/microsoft-logo.png') }}" alt="Microsoft Logo" class="w-6 h-6">

                                <span>Sign in with OpenId Connect</span>
                                <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </a>
                            
                            <!-- Divider -->
                            <div class="relative">
                                <div class="absolute inset-0 flex items-center">
                                    <div class="w-full border-t border-slate-200"></div>
                                </div>
                                <div class="relative flex justify-center text-sm">
                                    <span class="px-3 bg-white text-slate-500 font-medium">Secure authentication via Azure AD</span>
                                </div>
                            </div>
                            
                            <!-- Info Section -->
                            <div class="bg-slate-50 rounded-xl p-6 text-center">
                                <div class="w-12 h-12 bg-gradient-to-br from-blue-100 to-blue-200 rounded-xl flex items-center justify-center mx-auto mb-4">
                                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                </div>
                                <h4 class="font-semibold text-slate-900 mb-2">Secure Access</h4>
                                <p class="text-sm text-slate-600 leading-relaxed">
                                    Your login is protected by Microsoft Azure Active Directory. 
                                    Use your Open College email to access the system securely.
                                </p>
                            </div>
                        </div>
                    @endauth
                </div>
                
                <!-- Footer -->
                <div class="mt-8 text-center">
                    <p class="text-sm text-slate-500 mb-2">
                        Need help? Contact <a href="mailto:adrian.reen@theopencollege.com" class="text-toc-600 hover:text-toc-700 font-medium">adrian.reen@theopencollege.com</a>
                    </p>
                    <p class="text-xs text-slate-400">
                        © {{ date('Y') }} Adrian Reen. All rights reserved.
                    </p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>