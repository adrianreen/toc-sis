{{-- Basic test view --}}
<x-wide-layout title="Students Test" subtitle="Testing student display">
    <div>
        <h1>Students: {{ $students->count() }}</h1>
        @foreach($students as $student)
            <div>{{ $student->full_name }} - {{ $student->email }}</div>
        @endforeach
        
        {{ $students->links() }}
    </div>
</x-wide-layout>