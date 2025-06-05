{{-- resources/views/programmes/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Programmes
            </h2>
            @if(Auth::user()->role === 'manager')
                <a href="{{ route('programmes.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Add New Programme
                </a>
            @endif
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse ($programmes as $programme)
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold mb-2">{{ $programme->title }}</h3>
                            <p class="text-gray-600 mb-2">Code: {{ $programme->code }}</p>
                            <p class="text-sm text-gray-500 mb-4">{{ $programme->description }}</p>
                            
                            <div class="mb-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                    @if($programme->enrolment_type === 'cohort') bg-blue-100 text-blue-800
                                    @elseif($programme->enrolment_type === 'rolling') bg-green-100 text-green-800
                                    @else bg-purple-100 text-purple-800
                                    @endif">
                                    {{ ucfirst(str_replace('_', ' ', $programme->enrolment_type)) }}
                                </span>
                            </div>

                            @if($programme->isCohortBased() && $programme->cohorts->count() > 0)
                                <p class="text-sm text-gray-600 mb-2">Active Cohorts: {{ $programme->cohorts->where('status', 'active')->count() }}</p>
                            @endif

                            <div class="flex space-x-2">
                                <a href="{{ route('programmes.show', $programme) }}" class="text-indigo-600 hover:text-indigo-900 text-sm">View</a>
                                @if(Auth::user()->role === 'manager')
                                    <a href="{{ route('programmes.edit', $programme) }}" class="text-indigo-600 hover:text-indigo-900 text-sm">Edit</a>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-3 text-center text-gray-500 py-8">
                        No programmes found.
                    </div>
                @endforelse
            </div>

            {{-- Pagination --}}
            <div class="mt-6">
                {{ $programmes->links() }}
            </div>
        </div>
    </div>
</x-app-layout>