{{-- Development Role Switcher Component --}}
{{-- WARNING: Always renders if included in layout and user is authenticated. REMOVE BEFORE DEPLOYMENT. --}}
@if(Auth::check())
<div style="position: fixed; bottom: 20px; right: 20px; background-color: #ffeeba; border: 1px solid #ffdf7e; padding: 15px; border-radius: 5px; z-index: 1000; box-shadow: 0 0 10px rgba(0,0,0,0.1);">
    <h4 style="margin-top: 0; margin-bottom: 10px; color: #856404; font-size: 1em;">⚠️ DEV ROLE SWITCHER (LIVE) ⚠️</h4>
    <p style="font-size: 0.85em; margin-bottom: 5px;">Current Role: <strong style="color: #155724;">{{ ucfirst(str_replace('_', ' ', Auth::user()->role ?? 'N/A')) }}</strong></p>

    @if(session('dev_status'))
        <p style="color: green; font-size: 0.85em; margin-bottom: 10px;">{{ session('dev_status') }}</p>
    @endif
    @if(session('dev_error'))
        <p style="color: red; font-size: 0.85em; margin-bottom: 10px;">{{ session('dev_error') }}</p>
    @endif

    <form method="POST" action="{{ route('dev.switch-role') }}" style="margin-bottom: 0;">
        @csrf
        <select name="role" onchange="this.form.submit()" style="padding: 8px; border-radius: 3px; border: 1px solid #ced4da; min-width: 150px;">
            <option value="">-- Select Role --</option>
            {{-- Add your roles here. Ensure values match validation in controller --}}
            <option value="student" {{ Auth::user()->role == 'student' ? 'selected' : '' }}>Student</option>
            <option value="teacher" {{ Auth::user()->role == 'teacher' ? 'selected' : '' }}>Teacher</option>
            <option value="student_services" {{ Auth::user()->role == 'student_services' ? 'selected' : '' }}>Student Services</option>
            <option value="manager" {{ Auth::user()->role == 'manager' ? 'selected' : '' }}>Manager</option>
            <option value="admin" {{ Auth::user()->role == 'admin' ? 'selected' : '' }}>Admin</option>
        </select>
    </form>
    <p style="font-size: 0.75em; color: #856404; font-weight: bold; margin-top: 10px;">
        CRITICAL: REMOVE THIS COMPONENT AND ROUTE FROM CODE BEFORE ANY DEPLOYMENT!
    </p>
</div>
@endif