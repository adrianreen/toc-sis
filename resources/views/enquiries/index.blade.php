{{-- resources/views/enquiries/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Enquiries
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('enquiries.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Add New Enquiry
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12" x-data="enquiryIndex()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Search and Filter Section -->
            <div class="bg-white shadow-sm border border-gray-200 rounded-xl mb-6">
                <div class="p-6">
                    <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                        <!-- Search Input -->
                        <div class="md:col-span-2">
                            <label for="search" class="block text-sm font-semibold text-gray-700 mb-2">Search Enquiries</label>
                            <div class="relative">
                                <input 
                                    type="text" 
                                    id="search"
                                    name="search"
                                    value="{{ request('search') }}"
                                    placeholder="Search by enquiry number, name, or email..."
                                    class="block w-full pl-11 pr-10 py-3 border border-gray-300 rounded-lg leading-5 bg-white placeholder-gray-400 focus:outline-none focus:placeholder-gray-400 focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200"
                                >
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <!-- Status Filter -->
                        <div>
                            <label for="status" class="block text-sm font-semibold text-gray-700 mb-2">Status</label>
                            <select name="status" id="status" class="block w-full py-3 pl-4 pr-8 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                <option value="">All Statuses</option>
                                <option value="enquiry" {{ request('status') == 'enquiry' ? 'selected' : '' }}>Enquiry</option>
                                <option value="application" {{ request('status') == 'application' ? 'selected' : '' }}>Application</option>
                                <option value="accepted" {{ request('status') == 'accepted' ? 'selected' : '' }}>Accepted</option>
                                <option value="converted" {{ request('status') == 'converted' ? 'selected' : '' }}>Converted</option>
                                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                <option value="withdrawn" {{ request('status') == 'withdrawn' ? 'selected' : '' }}>Withdrawn</option>
                            </select>
                        </div>

                        <!-- Payment Status Filter -->
                        <div>
                            <label for="payment_status" class="block text-sm font-semibold text-gray-700 mb-2">Payment Status</label>
                            <select name="payment_status" id="payment_status" class="block w-full py-3 pl-4 pr-8 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                <option value="">All Payment Statuses</option>
                                <option value="pending" {{ request('payment_status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="deposit_paid" {{ request('payment_status') == 'deposit_paid' ? 'selected' : '' }}>Deposit Paid</option>
                                <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>Paid</option>
                                <option value="overdue" {{ request('payment_status') == 'overdue' ? 'selected' : '' }}>Overdue</option>
                            </select>
                        </div>

                        <!-- Programme Filter -->
                        <div>
                            <label for="programme_id" class="block text-sm font-semibold text-gray-700 mb-2">Programme</label>
                            <select name="programme_id" id="programme_id" class="block w-full py-3 pl-4 pr-8 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                <option value="">All Programmes</option>
                                @foreach($programmes as $programme)
                                    <option value="{{ $programme->id }}" {{ request('programme_id') == $programme->id ? 'selected' : '' }}>
                                        {{ $programme->title }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Submit Button -->
                        <div class="md:col-span-5 flex justify-end">
                            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-6 rounded-lg transition-colors duration-200">
                                Apply Filters
                            </button>
                            <a href="{{ route('enquiries.index') }}" class="ml-2 bg-gray-300 hover:bg-gray-400 text-gray-700 font-medium py-2 px-6 rounded-lg transition-colors duration-200">
                                Clear
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Enquiries Table -->
            <div class="bg-white shadow-sm border border-gray-200 rounded-xl overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Enquiry
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Applicant
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Programme
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Status
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Payment
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Created
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($enquiries as $enquiry)
                                <tr class="hover:bg-gray-50 transition-colors duration-150">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">{{ $enquiry->enquiry_number }}</div>
                                            <div class="text-sm text-gray-500">{{ $enquiry->email }}</div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $enquiry->full_name }}</div>
                                        <div class="text-sm text-gray-500">{{ $enquiry->phone }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $enquiry->programme->title }}</div>
                                        @if($enquiry->prospectiveCohort)
                                            <div class="text-sm text-gray-500">{{ $enquiry->prospectiveCohort->name }}</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                            @switch($enquiry->status)
                                                @case('enquiry')
                                                    bg-gray-100 text-gray-800
                                                    @break
                                                @case('application')
                                                    bg-blue-100 text-blue-800
                                                    @break
                                                @case('accepted')
                                                    bg-green-100 text-green-800
                                                    @break
                                                @case('converted')
                                                    bg-purple-100 text-purple-800
                                                    @break
                                                @case('rejected')
                                                    bg-red-100 text-red-800
                                                    @break
                                                @case('withdrawn')
                                                    bg-yellow-100 text-yellow-800
                                                    @break
                                            @endswitch
                                        ">
                                            {{ ucfirst($enquiry->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                                @switch($enquiry->payment_status)
                                                    @case('pending')
                                                        bg-yellow-100 text-yellow-800
                                                        @break
                                                    @case('deposit_paid')
                                                        bg-blue-100 text-blue-800
                                                        @break
                                                    @case('paid')
                                                        bg-green-100 text-green-800
                                                        @break
                                                    @case('overdue')
                                                        bg-red-100 text-red-800
                                                        @break
                                                @endswitch
                                            ">
                                                {{ ucfirst(str_replace('_', ' ', $enquiry->payment_status)) }}
                                            </span>
                                        </div>
                                        @if($enquiry->outstanding_balance > 0)
                                            <div class="text-xs text-gray-500">€{{ number_format($enquiry->outstanding_balance, 2) }} due</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $enquiry->created_at->format('M j, Y') }}
                                        <div class="text-xs">{{ $enquiry->createdBy->name }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-2">
                                            <a href="{{ route('enquiries.show', $enquiry) }}" class="text-indigo-600 hover:text-indigo-900">View</a>
                                            <a href="{{ route('enquiries.edit', $enquiry) }}" class="text-blue-600 hover:text-blue-900">Edit</a>
                                            @if($enquiry->canConvertToStudent())
                                                <form action="{{ route('enquiries.convert', $enquiry) }}" method="POST" class="inline" 
                                                      onsubmit="return confirm('Are you sure you want to convert this enquiry to a student?')">
                                                    @csrf
                                                    <button type="submit" class="text-green-600 hover:text-green-900">Convert</button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                                        No enquiries found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($enquiries->hasPages())
                    <div class="px-6 py-4 border-t border-gray-200">
                        {{ $enquiries->appends(request()->query())->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        function enquiryIndex() {
            return {
                // Any Alpine.js functionality needed
            }
        }
    </script>
</x-app-layout>