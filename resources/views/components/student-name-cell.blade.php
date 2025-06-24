{{--
Enhanced Student Name Cell Component
Provides clear clickability and visual prominence for student identification
--}}

@props([
    'student',
    'moduleInstance' => null
])

<td class="student-name-cell sticky left-0 bg-white border-r border-gray-200 z-10">
    <div class="student-info-container">
        <!-- Avatar -->
        <div class="student-avatar">
            <div class="avatar-circle">
                {{ substr($student->first_name, 0, 1) }}{{ substr($student->last_name, 0, 1) }}
            </div>
            @if($student->profile_photo_url ?? false)
                <img src="{{ $student->profile_photo_url }}" 
                     alt="{{ $student->full_name }}" 
                     class="avatar-image">
            @endif
        </div>
        
        <!-- Name and Details -->
        <div class="student-details">
            <button class="student-name-btn" 
                    onclick="showStudentDetails({{ $student->id }})"
                    title="Click to view {{ $student->full_name }} details">
                <div class="student-name">{{ $student->full_name }}</div>
                <div class="student-number">{{ $student->student_number }}</div>
                @if($student->email)
                    <div class="student-email">{{ Str::limit($student->email, 25) }}</div>
                @endif
            </button>
        </div>
        
        <!-- Quick Student Actions -->
        <div class="student-actions">
            <div class="student-actions-dropdown">
                <button class="student-action-btn dropdown-trigger" 
                        onclick="toggleStudentActions({{ $student->id }})"
                        title="Student Actions">
                    <svg fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"/>
                    </svg>
                </button>
                
                <div class="student-actions-menu hidden" id="actions-{{ $student->id }}">
                    <button class="action-menu-item" 
                            onclick="copyGradesFromStudent({{ $student->id }})"
                            title="Copy grades to other students">
                        <svg fill="currentColor" viewBox="0 0 20 20">
                            <path d="M8 2a1 1 0 000 2h2a1 1 0 100-2H8z"/>
                            <path d="M3 5a2 2 0 012-2 3 3 0 003 3h6a3 3 0 003-3 2 2 0 012 2v6h-4.586l1.293-1.293a1 1 0 00-1.414-1.414l-3 3a1 1 0 000 1.414l3 3a1 1 0 001.414-1.414L14.414 13H19v3a2 2 0 01-2 2H5a2 2 0 01-2-2V5z"/>
                        </svg>
                        <span>Copy Grades</span>
                    </button>
                    
                    @if($moduleInstance)
                        <button class="action-menu-item" 
                                onclick="showStudentProgress({{ $student->id }}, {{ $moduleInstance->id }})"
                                title="View progress for this module">
                            <svg fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M3 3a1 1 0 000 2v8a2 2 0 002 2h2.586l-1.293 1.293a1 1 0 101.414 1.414L10 15.414l2.293 2.293a1 1 0 001.414-1.414L12.414 15H15a2 2 0 002-2V5a1 1 0 100-2H3zm11.707 4.707a1 1 0 00-1.414-1.414L10 9.586 8.707 8.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span>View Progress</span>
                        </button>
                    @endif
                    
                    <button class="action-menu-item" 
                            onclick="exportStudentGrades({{ $student->id }})"
                            title="Export grades for this student">
                        <svg fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"/>
                        </svg>
                        <span>Export Grades</span>
                    </button>
                    
                    <button class="action-menu-item" 
                            onclick="sendEmailToStudent({{ $student->id }})"
                            title="Send email to student">
                        <svg fill="currentColor" viewBox="0 0 20 20">
                            <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/>
                            <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/>
                        </svg>
                        <span>Send Email</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</td>

<style>
/* Student Name Cell Styles */
.student-name-cell {
    min-width: 200px;
    padding: 12px;
    transition: background-color 0.2s ease;
}

.student-name-cell:hover {
    background-color: #f0f9ff;
}

.student-info-container {
    display: flex;
    align-items: center;
    gap: 12px;
    position: relative;
}

/* Student Avatar */
.student-avatar {
    position: relative;
    flex-shrink: 0;
}

.avatar-circle {
    width: 42px;
    height: 42px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--grade-primary, #3b82f6), #0284c7);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 14px;
    border: 2px solid white;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
}

.student-info-container:hover .avatar-circle {
    transform: scale(1.05);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.avatar-image {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid white;
}

/* Student Details */
.student-details {
    flex: 1;
    min-width: 0;
}

.student-name-btn {
    background: none;
    border: none;
    cursor: pointer;
    text-align: left;
    width: 100%;
    padding: 6px 8px;
    border-radius: 8px;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.student-name-btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(59, 130, 246, 0.1), transparent);
    transition: left 0.5s ease;
}

.student-name-btn:hover::before {
    left: 100%;
}

.student-name-btn:hover {
    background: rgba(59, 130, 246, 0.05);
    transform: translateX(2px);
    box-shadow: 0 2px 8px rgba(59, 130, 246, 0.1);
}

.student-name-btn:focus {
    outline: 2px solid var(--grade-primary, #3b82f6);
    outline-offset: 2px;
}

.student-name {
    font-size: 14px;
    font-weight: 600;
    color: #1f2937;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    transition: color 0.3s ease;
}

.student-name-btn:hover .student-name {
    color: var(--grade-primary, #3b82f6);
}

.student-number {
    font-size: 12px;
    color: var(--grade-neutral, #6b7280);
    margin-top: 2px;
    font-weight: 500;
}

.student-email {
    font-size: 11px;
    color: var(--grade-neutral, #6b7280);
    margin-top: 1px;
    opacity: 0.8;
}

/* Student Actions */
.student-actions {
    flex-shrink: 0;
    position: relative;
}

.student-actions-dropdown {
    position: relative;
}

.student-action-btn {
    width: 32px;
    height: 32px;
    border: none;
    border-radius: 8px;
    background: rgba(59, 130, 246, 0.1);
    color: var(--grade-primary, #3b82f6);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    opacity: 0;
    transform: scale(0.8);
}

.student-info-container:hover .student-action-btn {
    opacity: 1;
    transform: scale(1);
}

.student-action-btn:hover {
    background: var(--grade-primary, #3b82f6);
    color: white;
    transform: scale(1.1);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
}

.student-action-btn svg {
    width: 16px;
    height: 16px;
}

/* Actions Dropdown Menu */
.student-actions-menu {
    position: absolute;
    top: 100%;
    right: 0;
    margin-top: 4px;
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1), 0 4px 6px rgba(0, 0, 0, 0.05);
    z-index: 50;
    min-width: 180px;
    overflow: hidden;
    opacity: 0;
    transform: translateY(-10px);
    transition: all 0.3s ease;
    pointer-events: none;
}

.student-actions-menu:not(.hidden) {
    opacity: 1;
    transform: translateY(0);
    pointer-events: all;
}

.action-menu-item {
    display: flex;
    align-items: center;
    gap: 8px;
    width: 100%;
    padding: 12px 16px;
    border: none;
    background: none;
    cursor: pointer;
    transition: all 0.2s ease;
    font-size: 14px;
    color: #374151;
    text-align: left;
}

.action-menu-item:hover {
    background: #f3f4f6;
    color: var(--grade-primary, #3b82f6);
}

.action-menu-item:active {
    background: #e5e7eb;
}

.action-menu-item svg {
    width: 16px;
    height: 16px;
    flex-shrink: 0;
}

.action-menu-item span {
    font-weight: 500;
}

/* Status Indicators for Student */
.student-status-indicators {
    position: absolute;
    top: -2px;
    right: -2px;
    display: flex;
    gap: 2px;
}

.student-status-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    border: 2px solid white;
}

.status-active {
    background: var(--grade-success, #10b981);
}

.status-warning {
    background: var(--grade-warning, #f59e0b);
}

.status-danger {
    background: var(--grade-danger, #ef4444);
}

/* Responsive Design */
@media (max-width: 1024px) {
    .student-name-cell {
        min-width: 180px;
        padding: 10px;
    }
    
    .avatar-circle {
        width: 36px;
        height: 36px;
        font-size: 12px;
    }
    
    .student-info-container {
        gap: 10px;
    }
}

@media (max-width: 768px) {
    .student-name-cell {
        min-width: 160px;
        padding: 8px;
    }
    
    .avatar-circle {
        width: 32px;
        height: 32px;
        font-size: 11px;
    }
    
    .student-info-container {
        gap: 8px;
    }
    
    .student-name {
        font-size: 13px;
    }
    
    .student-number {
        font-size: 11px;
    }
    
    .student-email {
        display: none; /* Hide email on mobile to save space */
    }
    
    .student-action-btn {
        width: 28px;
        height: 28px;
    }
    
    .student-actions-menu {
        min-width: 160px;
    }
}

/* Dark Mode Support */
@media (prefers-color-scheme: dark) {
    .student-actions-menu {
        background: #1f2937;
        border-color: #374151;
    }
    
    .action-menu-item {
        color: #d1d5db;
    }
    
    .action-menu-item:hover {
        background: #374151;
        color: #60a5fa;
    }
}

/* High Contrast Mode */
@media (prefers-contrast: high) {
    .avatar-circle {
        border-width: 3px;
    }
    
    .student-name-btn:focus {
        outline-width: 3px;
    }
    
    .student-actions-menu {
        border-width: 2px;
    }
}

/* Reduced Motion */
@media (prefers-reduced-motion: reduce) {
    .student-name-btn,
    .student-action-btn,
    .avatar-circle,
    .student-actions-menu {
        transition: none;
    }
    
    .student-name-btn::before {
        display: none;
    }
    
    .student-name-btn:hover {
        transform: none;
    }
    
    .student-action-btn:hover {
        transform: none;
    }
}
</style>

<script>
// Student actions dropdown functionality
function toggleStudentActions(studentId) {
    const menu = document.getElementById(`actions-${studentId}`);
    const allMenus = document.querySelectorAll('.student-actions-menu');
    
    // Close all other menus
    allMenus.forEach(m => {
        if (m !== menu) {
            m.classList.add('hidden');
        }
    });
    
    // Toggle current menu
    menu.classList.toggle('hidden');
    
    // Close menu when clicking outside
    if (!menu.classList.contains('hidden')) {
        document.addEventListener('click', function closeMenu(e) {
            if (!menu.contains(e.target) && !e.target.closest('.dropdown-trigger')) {
                menu.classList.add('hidden');
                document.removeEventListener('click', closeMenu);
            }
        });
    }
}

// Student action functions (to be implemented)
function showStudentDetails(studentId) {
    // Implementation for showing student details modal/page
    console.log('Show details for student:', studentId);
}

function copyGradesFromStudent(studentId) {
    // Implementation for copying grades
    console.log('Copy grades from student:', studentId);
}

function showStudentProgress(studentId, moduleInstanceId) {
    // Implementation for showing student progress
    console.log('Show progress for student:', studentId, 'in module:', moduleInstanceId);
}

function exportStudentGrades(studentId) {
    // Implementation for exporting student grades
    console.log('Export grades for student:', studentId);
}

function sendEmailToStudent(studentId) {
    // Implementation for sending email
    console.log('Send email to student:', studentId);
}
</script>