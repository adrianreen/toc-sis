{{-- resources/views/enquiries/show.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Enquiry Details: {{ $enquiry->enquiry_number }}
            </h2>
            <div class="flex space-x-2">
                @if($enquiry->canConvertToStudent())
                    <form action="{{ route('enquiries.convert', $enquiry) }}" method="POST" class="inline"
                          onsubmit="return confirm('Are you sure you want to convert this enquiry to a student? This action cannot be undone.')">
                        @csrf
                        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                            Convert to Student
                        </button>
                    </form>
                @endif
                <a href="{{ route('enquiries.edit', $enquiry) }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Edit
                </a>
                <a href="{{ route('enquiries.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Back to Enquiries
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
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

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main Information -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Personal Information -->
                    <div class="bg-white shadow-sm border border-gray-200 rounded-xl">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900">Personal Information</h3>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Full Name</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ $enquiry->full_name }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Email</label>
                                    <p class="mt-1 text-sm text-gray-900">
                                        <a href="mailto:{{ $enquiry->email }}" class="text-blue-600 hover:text-blue-800">
                                            {{ $enquiry->email }}
                                        </a>
                                    </p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Phone</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ $enquiry->phone ?: 'Not provided' }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Date of Birth</label>
                                    <p class="mt-1 text-sm text-gray-900">
                                        {{ $enquiry->date_of_birth ? $enquiry->date_of_birth->format('M j, Y') : 'Not provided' }}
                                    </p>
                                </div>
                            </div>
                            
                            @if($enquiry->address || $enquiry->city || $enquiry->county || $enquiry->eircode)
                                <div class="mt-6">
                                    <label class="block text-sm font-medium text-gray-500">Address</label>
                                    <div class="mt-1 text-sm text-gray-900">
                                        @if($enquiry->address)
                                            <div>{{ $enquiry->address }}</div>
                                        @endif
                                        <div>
                                            {{ collect([$enquiry->city, $enquiry->county, $enquiry->eircode])->filter()->implode(', ') }}
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Programme Information -->
                    <div class="bg-white shadow-sm border border-gray-200 rounded-xl">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900">Programme Information</h3>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Programme</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ $enquiry->programme->title }}</p>
                                    <p class="text-xs text-gray-500">{{ $enquiry->programme->code }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Prospective Cohort</label>
                                    <p class="mt-1 text-sm text-gray-900">
                                        @if($enquiry->prospectiveCohort)
                                            {{ $enquiry->prospectiveCohort->name }}
                                            <span class="text-xs text-gray-500">
                                                (Starts {{ $enquiry->prospectiveCohort->start_date->format('M j, Y') }})
                                            </span>
                                        @else
                                            Not assigned
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Information -->
                    <div class="bg-white shadow-sm border border-gray-200 rounded-xl">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900">Payment Information</h3>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Amount Due</label>
                                    <p class="mt-1 text-lg font-semibold text-gray-900">€{{ number_format($enquiry->amount_due, 2) }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Amount Paid</label>
                                    <p class="mt-1 text-lg font-semibold text-gray-900">€{{ number_format($enquiry->amount_paid, 2) }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Outstanding Balance</label>
                                    <p class="mt-1 text-lg font-semibold {{ $enquiry->outstanding_balance > 0 ? 'text-red-600' : 'text-green-600' }}">
                                        €{{ number_format($enquiry->outstanding_balance, 2) }}
                                    </p>
                                </div>
                            </div>
                            
                            @if($enquiry->payment_due_date)
                                <div class="mt-4">
                                    <label class="block text-sm font-medium text-gray-500">Payment Due Date</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ $enquiry->payment_due_date->format('M j, Y') }}</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Additional Information -->
                    @if($enquiry->notes || $enquiry->microsoft_account_required)
                        <div class="bg-white shadow-sm border border-gray-200 rounded-xl">
                            <div class="px-6 py-4 border-b border-gray-200">
                                <h3 class="text-lg font-semibold text-gray-900">Additional Information</h3>
                            </div>
                            <div class="p-6">
                                @if($enquiry->microsoft_account_required)
                                    <div class="mb-4">
                                        <label class="block text-sm font-medium text-gray-500">Microsoft 365 Account</label>
                                        <div class="mt-1 flex items-center">
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $enquiry->microsoft_account_created ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                                {{ $enquiry->microsoft_account_created ? 'Created' : 'Required' }}
                                            </span>
                                        </div>
                                    </div>
                                @endif
                                
                                @if($enquiry->notes)
                                    <div>
                                        <label class="block text-sm font-medium text-gray-500">Notes</label>
                                        <p class="mt-1 text-sm text-gray-900 whitespace-pre-wrap">{{ $enquiry->notes }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Status Overview -->
                    <div class="bg-white shadow-sm border border-gray-200 rounded-xl">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900">Status Overview</h3>
                        </div>
                        <div class="p-6 space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Enquiry Status</label>
                                <span class="mt-1 inline-flex px-3 py-1 text-sm font-semibold rounded-full
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
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Payment Status</label>
                                <span class="mt-1 inline-flex px-3 py-1 text-sm font-semibold rounded-full
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
                        </div>
                    </div>

                    <!-- Conversion Status -->
                    @if($enquiry->convertedStudent)
                        <div class="bg-white shadow-sm border border-gray-200 rounded-xl">
                            <div class="px-6 py-4 border-b border-gray-200">
                                <h3 class="text-lg font-semibold text-gray-900">Conversion Details</h3>
                            </div>
                            <div class="p-6">
                                <p class="text-sm text-gray-600 mb-2">This enquiry has been converted to a student:</p>
                                <a href="{{ route('students.show', $enquiry->convertedStudent) }}" 
                                   class="text-blue-600 hover:text-blue-800 font-medium">
                                    {{ $enquiry->convertedStudent->student_number }} - {{ $enquiry->convertedStudent->full_name }}
                                </a>
                            </div>
                        </div>
                    @endif

                    <!-- Audit Information -->
                    <div class="bg-white shadow-sm border border-gray-200 rounded-xl">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900">Audit Information</h3>
                        </div>
                        <div class="p-6 space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Created</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $enquiry->created_at->format('M j, Y g:i A') }}</p>
                                <p class="text-xs text-gray-500">by {{ $enquiry->createdBy->name }}</p>
                            </div>
                            
                            @if($enquiry->updated_at && $enquiry->updated_at != $enquiry->created_at)
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Last Updated</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ $enquiry->updated_at->format('M j, Y g:i A') }}</p>
                                    @if($enquiry->updatedBy)
                                        <p class="text-xs text-gray-500">by {{ $enquiry->updatedBy->name }}</p>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>