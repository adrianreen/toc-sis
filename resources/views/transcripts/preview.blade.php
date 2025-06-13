{{-- resources/views/transcripts/preview.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    📄 Transcript Preview
                </h2>
                <p class="mt-1 text-sm text-gray-600">
                    Preview of {{ $student->full_name }}'s academic transcript
                </p>
            </div>
            <div class="space-x-2">
                <a href="{{ route('transcripts.download', $student) }}" 
                   class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    📥 Download PDF
                </a>
                <a href="{{ route('students.show', $student) }}" 
                   class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    ← Back to Student
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Transcript Preview Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border-2 border-gray-200">
                <div class="p-8">
                    
                    <!-- Header -->
                    <div class="text-center border-b-4 border-gray-800 pb-6 mb-8">
                        <h1 class="text-3xl font-bold text-gray-800 mb-2">{{ $institution['name'] }}</h1>
                        <div class="text-sm text-gray-600 mb-6">
                            {{ $institution['address'] }} | {{ $institution['website'] }} | {{ $institution['phone'] }}
                        </div>
                        <div class="inline-block text-xl font-bold text-gray-800 border-2 border-gray-800 px-6 py-3">
                            OFFICIAL ACADEMIC TRANSCRIPT
                        </div>
                    </div>

                    <!-- Student Information -->
                    <div class="bg-gray-50 p-6 rounded-lg mb-8">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <span class="font-bold text-gray-700">Student Name:</span>
                                <span class="ml-2">{{ $student->full_name }}</span>
                            </div>
                            <div>
                                <span class="font-bold text-gray-700">Student Number:</span>
                                <span class="ml-2">{{ $student->student_number }}</span>
                            </div>
                            <div>
                                <span class="font-bold text-gray-700">Date of Birth:</span>
                                <span class="ml-2">{{ $student->date_of_birth ? $student->date_of_birth->format('d F Y') : 'Not recorded' }}</span>
                            </div>
                            <div>
                                <span class="font-bold text-gray-700">Student Status:</span>
                                <span class="ml-2">{{ ucfirst($student->status) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Academic Records by Programme -->
                    @foreach($programmeModules as $programmeData)
                        <div class="mb-10">
                            <div class="bg-gray-800 text-white p-4 font-bold text-lg mb-4">
                                {{ $programmeData['programme']->name }} ({{ $programmeData['programme']->code }})
                            </div>
                            
                            @if(count($programmeData['modules']) > 0)
                                <div class="overflow-x-auto">
                                    <table class="min-w-full border border-gray-300">
                                        <thead>
                                            <tr class="bg-gray-100">
                                                <th class="border border-gray-300 px-4 py-3 text-left text-sm font-bold">Module Code</th>
                                                <th class="border border-gray-300 px-4 py-3 text-left text-sm font-bold">Module Title</th>
                                                <th class="border border-gray-300 px-4 py-3 text-center text-sm font-bold">Credits</th>
                                                <th class="border border-gray-300 px-4 py-3 text-center text-sm font-bold">Grade</th>
                                                <th class="border border-gray-300 px-4 py-3 text-center text-sm font-bold">Status</th>
                                                <th class="border border-gray-300 px-4 py-3 text-center text-sm font-bold">Completed</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($programmeData['modules'] as $moduleData)
                                                <tr class="hover:bg-gray-50">
                                                    <td class="border border-gray-300 px-4 py-3 text-sm">{{ $moduleData['module']->code }}</td>
                                                    <td class="border border-gray-300 px-4 py-3 text-sm">{{ $moduleData['module']->name }}</td>
                                                    <td class="border border-gray-300 px-4 py-3 text-sm text-center">{{ $moduleData['credits'] }}</td>
                                                    <td class="border border-gray-300 px-4 py-3 text-sm text-center font-bold">
                                                        {{ $moduleData['grade'] ?? '-' }}
                                                    </td>
                                                    <td class="border border-gray-300 px-4 py-3 text-sm text-center">
                                                        <span class="@if($moduleData['status'] === 'Completed') text-green-600 font-bold @elseif($moduleData['status'] === 'Failed') text-red-600 font-bold @else text-yellow-600 italic @endif">
                                                            {{ $moduleData['status'] }}
                                                        </span>
                                                    </td>
                                                    <td class="border border-gray-300 px-4 py-3 text-sm text-center">
                                                        {{ $moduleData['completion_date'] ? $moduleData['completion_date']->format('M Y') : '-' }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                
                                <div class="bg-gray-100 p-4 border-l-4 border-gray-800 mt-4">
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <span class="font-bold">Total Credits:</span>
                                            <span class="ml-2">{{ $programmeData['total_credits'] }}</span>
                                        </div>
                                        <div>
                                            <span class="font-bold">Programme GPA:</span>
                                            <span class="ml-2">{{ $programmeData['gpa'] > 0 ? number_format($programmeData['gpa'], 2) : 'N/A' }}</span>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="text-center text-gray-500 italic py-8">
                                    No completed modules for this programme
                                </div>
                            @endif
                        </div>
                    @endforeach

                    <!-- Overall Summary -->
                    @if($totalCredits > 0)
                        <div class="bg-gray-800 text-white p-6 text-center">
                            <div class="text-lg font-bold mb-4">ACADEMIC SUMMARY</div>
                            <div class="grid grid-cols-2 gap-8">
                                <div>
                                    <div class="text-sm">Total Credits Earned</div>
                                    <div class="text-2xl font-bold">{{ $totalCredits }}</div>
                                </div>
                                <div>
                                    <div class="text-sm">Overall GPA</div>
                                    <div class="text-3xl font-bold">{{ number_format($overallGPA, 2) }}</div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Certification -->
                    <div class="mt-8 pt-6 border-t-2 border-gray-800 text-sm text-gray-600">
                        <p class="mb-4">
                            <strong>CERTIFICATION:</strong> This is to certify that the above is a true and accurate record of the academic 
                            achievements of {{ $student->full_name }} (Student Number: {{ $student->student_number }}) at 
                            {{ $institution['name'] }}. This transcript includes all modules where results have been officially released.
                        </p>
                        <p class="mb-4">
                            <strong>GRADING SCALE:</strong> A (80-100%), B (70-79%), C (60-69%), D (50-59%), E (40-49%), F (0-39%). 
                            Minimum passing grade is E (40%).
                        </p>
                        <p class="mb-4">
                            <strong>GPA SCALE:</strong> A=4.0, B=3.0, C=2.0, D=1.5, E=1.0, F=0.0
                        </p>
                    </div>

                    <!-- Footer -->
                    <div class="mt-6 pt-4 text-center text-xs text-gray-500">
                        <p>
                            This transcript was generated on {{ $generatedDate->format('d F Y \a\t H:i') }} and contains only officially released results.<br>
                            For verification purposes, please contact {{ $institution['name'] }} directly.<br>
                            Document ID: TOC-{{ $student->student_number }}-{{ $generatedDate->format('Y-m-d-His') }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="mt-6 flex justify-center space-x-4">
                <a href="{{ route('transcripts.download', $student) }}" 
                   class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg">
                    📥 Download Official PDF
                </a>
                <a href="{{ route('students.show', $student) }}" 
                   class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-3 px-6 rounded-lg">
                    ← Back to Student
                </a>
            </div>
        </div>
    </div>
</x-app-layout>