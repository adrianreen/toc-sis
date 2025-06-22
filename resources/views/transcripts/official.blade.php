{{-- resources/views/transcripts/official.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Official Academic Transcript</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 20px;
            background: white;
            position: relative;
        }
        
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 120px;
            color: rgba(31, 41, 55, 0.03);
            font-weight: bold;
            z-index: -1;
            user-select: none;
            pointer-events: none;
            letter-spacing: 15px;
            font-family: Arial, sans-serif;
            width: 150%;
            text-align: center;
        }
        
        .header {
            text-align: center;
            border-bottom: 3px solid #b8860b;
            padding-bottom: 20px;
            margin-bottom: 30px;
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px 8px 0 0;
        }
        
        .institution-name {
            font-size: 32px;
            font-weight: bold;
            color: #b8860b;
            margin-bottom: 5px;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
            letter-spacing: 1px;
        }
        
        .institution-details {
            font-size: 12px;
            color: #666;
            margin-bottom: 20px;
        }
        
        .document-title {
            font-size: 22px;
            font-weight: bold;
            color: white;
            border: 3px solid #b8860b;
            background: #b8860b;
            padding: 12px 20px;
            display: inline-block;
            border-radius: 5px;
            letter-spacing: 2px;
        }
        
        .student-info {
            background: #f8fafc;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        
        .student-info-grid {
            display: table;
            width: 100%;
        }
        
        .student-info-row {
            display: table-row;
        }
        
        .student-info-label {
            display: table-cell;
            font-weight: bold;
            padding: 5px 20px 5px 0;
            width: 150px;
        }
        
        .student-info-value {
            display: table-cell;
            padding: 5px 0;
        }
        
        .programme-section {
            margin-bottom: 40px;
            page-break-inside: avoid;
        }
        
        .programme-header {
            background: #1f2937;
            color: white;
            padding: 15px 20px;
            font-weight: bold;
            font-size: 18px;
            margin-bottom: 15px;
            border-radius: 5px;
            border-left: 5px solid #b8860b;
        }
        
        .module-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .module-table th {
            background: #e5e7eb;
            color: #1f2937;
            padding: 10px;
            font-weight: bold;
            font-size: 12px;
            border: 1px solid #d1d5db;
            text-align: left;
        }
        
        .module-table td {
            padding: 8px 10px;
            border: 1px solid #d1d5db;
            font-size: 11px;
            color: #1f2937;
        }
        
        .module-table tr:nth-child(even) {
            background: #f9fafb;
        }
        
        .grade-cell {
            text-align: center;
            font-weight: bold;
        }
        
        .status-completed {
            color: #059669;
            font-weight: bold;
        }
        
        .status-failed {
            color: #dc2626;
            font-weight: bold;
        }
        
        .status-progress {
            color: #d97706;
            font-style: italic;
        }
        
        .programme-summary {
            background: #f3f4f6;
            padding: 12px 15px;
            border-left: 4px solid #1f2937;
            margin-top: 10px;
        }
        
        .summary-row {
            display: table;
            width: 100%;
            margin-bottom: 5px;
        }
        
        .summary-label {
            display: table-cell;
            font-weight: bold;
            width: 150px;
        }
        
        .summary-value {
            display: table-cell;
        }
        
        .overall-summary {
            background: #1f2937;
            color: white;
            padding: 25px;
            margin-top: 30px;
            text-align: center;
            border-radius: 8px;
            border: 3px solid #b8860b;
        }
        
        .overall-gpa {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .certification {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #1f2937;
            font-size: 11px;
            color: #666;
        }
        
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 11px;
            color: #666;
            page-break-inside: avoid;
            border-top: 2px solid #b8860b;
            padding-top: 20px;
            background: #f8f9fa;
            padding: 20px;
            border-radius: 0 0 8px 8px;
        }
        
        .security-features {
            margin-top: 20px;
            font-size: 10px;
            color: #999;
            text-align: center;
            font-style: italic;
        }
        
        @media print {
            body {
                padding: 15px;
            }
            
            .page-break {
                page-break-before: always;
            }
        }
    </style>
</head>
<body>
    <!-- Watermark -->
    <div class="watermark">THE OPEN COLLEGE OFFICIAL</div>
    
    <!-- Header -->
    <div class="header">
        <div style="display: table; width: 100%; margin-bottom: 20px;">
            <div style="display: table-cell; width: 120px; vertical-align: middle;">
                @php
                    $logoPath = public_path('images/logo.png');
                    $logoSrc = '';
                    if (file_exists($logoPath)) {
                        $imageData = file_get_contents($logoPath);
                        $logoSrc = 'data:image/png;base64,' . base64_encode($imageData);
                    }
                @endphp
                
                @if($logoSrc)
                    <img src="{{ $logoSrc }}" style="width: 100px; height: auto;" alt="The Open College Logo">
                @else
                    <div style="width: 100px; height: 60px; background: #b8860b; border: 3px solid #daa520; text-align: center; line-height: 54px; color: white; font-weight: bold; font-size: 20px;">TOC</div>
                @endif
            </div>
            <div style="display: table-cell; vertical-align: middle; text-align: center;">
                <div class="institution-name">{{ $institution['name'] }}</div>
                <div class="institution-details">
                    {{ $institution['address'] }} | {{ $institution['website'] }} | {{ $institution['phone'] }}
                </div>
            </div>
            <div style="display: table-cell; width: 120px; vertical-align: middle; text-align: right;">
                <div style="font-size: 12px; color: #666;">
                    <strong>Document ID:</strong><br>
                    TOC-{{ $student->student_number }}-{{ $generatedDate->format('Y-m-d') }}
                </div>
            </div>
        </div>
        <div class="document-title">OFFICIAL ACADEMIC TRANSCRIPT</div>
    </div>

    <!-- Student Information -->
    <div class="student-info">
        <div class="student-info-grid">
            <div class="student-info-row">
                <div class="student-info-label">Student Name:</div>
                <div class="student-info-value">{{ $student->full_name }}</div>
            </div>
            <div class="student-info-row">
                <div class="student-info-label">Student Number:</div>
                <div class="student-info-value">{{ $student->student_number }}</div>
            </div>
            <div class="student-info-row">
                <div class="student-info-label">Date of Birth:</div>
                <div class="student-info-value">{{ $student->date_of_birth ? $student->date_of_birth->format('d F Y') : 'Not recorded' }}</div>
            </div>
            <div class="student-info-row">
                <div class="student-info-label">Student Status:</div>
                <div class="student-info-value">{{ ucfirst($student->status) }}</div>
            </div>
        </div>
    </div>

    <!-- Grading System and Legend -->
    <div style="background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 15px; margin: 20px 0; font-size: 11px;">
        <div style="text-align: center; font-weight: bold; font-size: 13px; margin-bottom: 12px; color: #1f2937;">
            IRISH NATIONAL FRAMEWORK OF QUALIFICATIONS (NFQ) - GRADING SYSTEM
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; margin-bottom: 15px;">
            <!-- Grade Scale -->
            <div>
                <div style="font-weight: bold; margin-bottom: 8px; color: #374151;">QQI Grade Scale:</div>
                <div style="display: grid; grid-template-columns: 30px 1fr 50px; gap: 8px; font-size: 10px;">
                    <div style="font-weight: bold;">D</div><div>Distinction</div><div>80-100%</div>
                    <div style="font-weight: bold;">M</div><div>Merit</div><div>65-79%</div>
                    <div style="font-weight: bold;">P</div><div>Pass</div><div>50-64%</div>
                    <div style="font-weight: bold;">F</div><div>Fail</div><div>40-49%</div>
                    <div style="font-weight: bold;">U</div><div>Unsuccessful</div><div>0-39%</div>
                </div>
            </div>
            
            <!-- Component Indicators -->
            <div>
                <div style="font-weight: bold; margin-bottom: 8px; color: #374151;">Component Indicators:</div>
                <div style="font-size: 10px; line-height: 1.4;">
                    <div><span style="color: #dc2626; font-weight: bold;">[MUST PASS]</span> - Required for module completion</div>
                    <div><span style="background: #059669; color: white; padding: 1px 4px; border-radius: 6px; font-size: 8px;">PASS</span> - Component passed</div>
                    <div><span style="background: #dc2626; color: white; padding: 1px 4px; border-radius: 6px; font-size: 8px;">FAIL</span> - Component failed</div>
                    <div style="margin-top: 4px; color: #6b7280;">Grading dates show assessment completion</div>
                </div>
            </div>
            
            <!-- Credit System -->
            <div>
                <div style="font-weight: bold; margin-bottom: 8px; color: #374151;">Credit System:</div>
                <div style="font-size: 10px; line-height: 1.4;">
                    <div>• 1 Credit = 10 hours learning time</div>
                    <div>• Module weightings shown as percentages</div>
                    <div>• Overall grades calculated from weighted components</div>
                    <div>• Must-pass components override overall calculation</div>
                </div>
            </div>
        </div>
        
        <div style="border-top: 1px solid #e5e7eb; padding-top: 10px; font-size: 9px; color: #6b7280; text-align: center;">
            <strong>Document Authenticity:</strong> All grades are verified and released according to institutional policy. 
            Component breakdowns show detailed assessment performance and grading dates for verification.
        </div>
    </div>

    <!-- Academic Records by Programme -->
    @foreach($programmeModules as $programmeData)
        <div class="programme-section">
            <div class="programme-header">
                {{ $programmeData['programme']->title }} ({{ $programmeData['programme']->programme_code }})
            </div>
            
            @if(count($programmeData['modules']) > 0)
                <table class="module-table">
                    <thead>
                        <tr>
                            <th style="width: 15%;">Module Code</th>
                            <th style="width: 40%;">Module Title</th>
                            <th style="width: 10%;">Credits</th>
                            <th style="width: 10%;">Grade</th>
                            <th style="width: 15%;">Status</th>
                            <th style="width: 10%;">Completed</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($programmeData['modules'] as $moduleData)
                            <tr>
                                <td>{{ $moduleData['module']->module_code }}</td>
                                <td>{{ $moduleData['module']->title }}</td>
                                <td style="text-align: center;">{{ $moduleData['credits'] }}</td>
                                <td class="grade-cell">
                                    @if($moduleData['grade'])
                                        {{ $moduleData['grade'] }}
                                        @if($moduleData['percentage'])
                                            <br><small style="color: #666;">({{ $moduleData['percentage'] }}%)</small>
                                        @endif
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="@if($moduleData['status'] === 'Completed') status-completed @elseif($moduleData['status'] === 'Failed') status-failed @else status-progress @endif">
                                    {{ $moduleData['status'] }}
                                </td>
                                <td style="text-align: center;">
                                    {{ $moduleData['completion_date'] ? $moduleData['completion_date']->format('M Y') : '-' }}
                                </td>
                            </tr>
                            
                            {{-- Assessment Components Breakdown --}}
                            @if(!empty($moduleData['components']))
                                <tr>
                                    <td colspan="6" style="padding: 0; border: none;">
                                        <div style="background: #f8f9fa; margin: 3px 0; padding: 12px; border-left: 4px solid #3b82f6; font-size: 10px; border-radius: 0 4px 4px 0;">
                                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                                                <strong style="color: #1f2937; font-size: 11px;">Assessment Component Breakdown</strong>
                                                <div style="font-size: 9px; color: #6b7280;">
                                                    Module Pass Mark: {{ $moduleData['module']->pass_mark ?? 40 }}% • 
                                                    Overall Result: <strong style="color: {{ $moduleData['status'] === 'Completed' ? '#059669' : ($moduleData['status'] === 'Failed' ? '#dc2626' : '#f59e0b') }};">{{ $moduleData['status'] }}</strong>
                                                </div>
                                            </div>
                                            
                                            <table style="width: 100%; border-collapse: collapse; font-size: 10px;">
                                                <thead>
                                                    <tr style="background: #e5e7eb;">
                                                        <th style="padding: 6px 8px; text-align: left; font-weight: 600; color: #374151; border: 1px solid #d1d5db;">Assessment Component</th>
                                                        <th style="padding: 6px 8px; text-align: center; font-weight: 600; color: #374151; border: 1px solid #d1d5db; width: 60px;">Weight</th>
                                                        <th style="padding: 6px 8px; text-align: center; font-weight: 600; color: #374151; border: 1px solid #d1d5db; width: 80px;">Score</th>
                                                        <th style="padding: 6px 8px; text-align: center; font-weight: 600; color: #374151; border: 1px solid #d1d5db; width: 60px;">Mark %</th>
                                                        <th style="padding: 6px 8px; text-align: center; font-weight: 600; color: #374151; border: 1px solid #d1d5db; width: 60px;">Result</th>
                                                        <th style="padding: 6px 8px; text-align: center; font-weight: 600; color: #374151; border: 1px solid #d1d5db; width: 80px;">Graded</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($moduleData['components'] as $component)
                                                        <tr style="background: white;">
                                                            <td style="padding: 6px 8px; border: 1px solid #d1d5db; color: #1f2937;">
                                                                {{ $component['name'] }}
                                                                @if($component['is_must_pass'])
                                                                    <span style="color: #dc2626; font-weight: bold; font-size: 9px;"> [MUST PASS]</span>
                                                                @endif
                                                            </td>
                                                            <td style="padding: 6px 8px; text-align: center; border: 1px solid #d1d5db; color: #374151;">
                                                                {{ $component['weighting'] }}%
                                                            </td>
                                                            <td style="padding: 6px 8px; text-align: center; border: 1px solid #d1d5db; color: #374151;">
                                                                {{ $component['grade'] }}/{{ $component['max_grade'] }}
                                                            </td>
                                                            <td style="padding: 6px 8px; text-align: center; border: 1px solid #d1d5db;">
                                                                <strong style="color: {{ $component['percentage'] >= ($component['component_pass_mark'] ?? 40) ? '#059669' : '#dc2626' }};">
                                                                    {{ $component['percentage'] }}%
                                                                </strong>
                                                            </td>
                                                            <td style="padding: 6px 8px; text-align: center; border: 1px solid #d1d5db;">
                                                                <span style="
                                                                    padding: 2px 6px; 
                                                                    border-radius: 10px; 
                                                                    font-size: 8px; 
                                                                    font-weight: bold; 
                                                                    color: white;
                                                                    background: {{ $component['passed'] ? '#059669' : '#dc2626' }};
                                                                ">
                                                                    {{ $component['passed'] ? 'PASS' : 'FAIL' }}
                                                                </span>
                                                            </td>
                                                            <td style="padding: 6px 8px; text-align: center; border: 1px solid #d1d5db; color: #6b7280; font-size: 9px;">
                                                                {{ $component['graded_date'] ? $component['graded_date']->format('d/m/Y') : 'Pending' }}
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                            
                                            <div style="margin-top: 8px; font-size: 9px; color: #6b7280; display: flex; justify-content: space-between;">
                                                <div>
                                                    @if(collect($moduleData['components'])->where('is_must_pass', true)->count() > 0)
                                                        <span style="color: #dc2626;">⚠</span> Must-pass components are required for module completion
                                                    @endif
                                                </div>
                                                <div>
                                                    Components weighted average: <strong>{{ $moduleData['percentage'] }}%</strong>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
                
                <div class="programme-summary">
                    <div class="summary-row">
                        <div class="summary-label">Total Credits:</div>
                        <div class="summary-value">{{ $programmeData['total_credits'] }}</div>
                    </div>
                    <div class="summary-row">
                        <div class="summary-label">Programme Result:</div>
                        <div class="summary-value">
                            @php
                                $avgPoints = $programmeData['total_credits'] > 0 ? $programmeData['gpa'] : 0;
                                $programmeGrade = 'N/A';
                                if ($avgPoints >= 2.5) $programmeGrade = 'Distinction';
                                elseif ($avgPoints >= 1.5) $programmeGrade = 'Merit';
                                elseif ($avgPoints >= 1.0) $programmeGrade = 'Pass';
                                elseif ($avgPoints > 0) $programmeGrade = 'Unsuccessful';
                                
                                // Calculate overall programme percentage from module percentages
                                $totalMark = 0;
                                $totalWeight = 0;
                                foreach($programmeData['modules'] as $moduleData) {
                                    if($moduleData['percentage'] && $moduleData['status'] === 'Completed') {
                                        $credits = $moduleData['credits'];
                                        $totalMark += ($moduleData['percentage'] * $credits);
                                        $totalWeight += $credits;
                                    }
                                }
                                $programmePercentage = $totalWeight > 0 ? round($totalMark / $totalWeight, 1) : 0;
                            @endphp
                            {{ $programmeGrade }}
                            @if($programmePercentage > 0)
                                <br><small style="color: #ccc;">({{ $programmePercentage }}%)</small>
                            @endif
                        </div>
                    </div>
                </div>
            @else
                <p style="text-align: center; color: #666; font-style: italic; padding: 20px;">
                    No completed modules for this programme
                </p>
            @endif
        </div>
    @endforeach

    <!-- Standalone Modules -->
    @if(count($standaloneModules) > 0)
        <div class="programme-section">
            <div class="programme-header">
                Standalone Modules
            </div>
            
            <table class="module-table">
                <thead>
                    <tr>
                        <th style="width: 15%;">Module Code</th>
                        <th style="width: 40%;">Module Title</th>
                        <th style="width: 10%;">Credits</th>
                        <th style="width: 10%;">Grade</th>
                        <th style="width: 15%;">Status</th>
                        <th style="width: 10%;">Completed</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($standaloneModules as $moduleData)
                        <tr>
                            <td>{{ $moduleData['module']->module_code }}</td>
                            <td>{{ $moduleData['module']->title }}</td>
                            <td style="text-align: center;">{{ $moduleData['credits'] }}</td>
                            <td class="grade-cell">
                                @if($moduleData['grade'])
                                    {{ $moduleData['grade'] }}
                                    @if($moduleData['percentage'])
                                        <br><small style="color: #666;">({{ $moduleData['percentage'] }}%)</small>
                                    @endif
                                @else
                                    -
                                @endif
                            </td>
                            <td class="@if($moduleData['status'] === 'Completed') status-completed @elseif($moduleData['status'] === 'Failed') status-failed @else status-progress @endif">
                                {{ $moduleData['status'] }}
                            </td>
                            <td style="text-align: center;">
                                {{ $moduleData['completion_date'] ? $moduleData['completion_date']->format('M Y') : '-' }}
                            </td>
                        </tr>
                        
                        {{-- Assessment Components Breakdown --}}
                        @if(!empty($moduleData['components']))
                            <tr>
                                <td colspan="6" style="padding: 0; border: none;">
                                    <div style="background: #f8f9fa; margin: 3px 0; padding: 12px; border-left: 4px solid #3b82f6; font-size: 10px; border-radius: 0 4px 4px 0;">
                                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                                            <strong style="color: #1f2937; font-size: 11px;">Assessment Component Breakdown</strong>
                                            <div style="font-size: 9px; color: #6b7280;">
                                                Module Pass Mark: {{ $moduleData['module']->pass_mark ?? 40 }}% • 
                                                Overall Result: <strong style="color: {{ $moduleData['status'] === 'Completed' ? '#059669' : ($moduleData['status'] === 'Failed' ? '#dc2626' : '#f59e0b') }};">{{ $moduleData['status'] }}</strong>
                                            </div>
                                        </div>
                                        
                                        <table style="width: 100%; border-collapse: collapse; font-size: 10px;">
                                            <thead>
                                                <tr style="background: #e5e7eb;">
                                                    <th style="padding: 6px 8px; text-align: left; font-weight: 600; color: #374151; border: 1px solid #d1d5db;">Assessment Component</th>
                                                    <th style="padding: 6px 8px; text-align: center; font-weight: 600; color: #374151; border: 1px solid #d1d5db; width: 60px;">Weight</th>
                                                    <th style="padding: 6px 8px; text-align: center; font-weight: 600; color: #374151; border: 1px solid #d1d5db; width: 80px;">Score</th>
                                                    <th style="padding: 6px 8px; text-align: center; font-weight: 600; color: #374151; border: 1px solid #d1d5db; width: 60px;">Mark %</th>
                                                    <th style="padding: 6px 8px; text-align: center; font-weight: 600; color: #374151; border: 1px solid #d1d5db; width: 60px;">Result</th>
                                                    <th style="padding: 6px 8px; text-align: center; font-weight: 600; color: #374151; border: 1px solid #d1d5db; width: 80px;">Graded</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($moduleData['components'] as $component)
                                                    <tr style="background: white;">
                                                        <td style="padding: 6px 8px; border: 1px solid #d1d5db; color: #1f2937;">
                                                            {{ $component['name'] }}
                                                            @if($component['is_must_pass'])
                                                                <span style="color: #dc2626; font-weight: bold; font-size: 9px;"> [MUST PASS]</span>
                                                            @endif
                                                        </td>
                                                        <td style="padding: 6px 8px; text-align: center; border: 1px solid #d1d5db; color: #374151;">
                                                            {{ $component['weighting'] }}%
                                                        </td>
                                                        <td style="padding: 6px 8px; text-align: center; border: 1px solid #d1d5db; color: #374151;">
                                                            {{ $component['grade'] }}/{{ $component['max_grade'] }}
                                                        </td>
                                                        <td style="padding: 6px 8px; text-align: center; border: 1px solid #d1d5db;">
                                                            <strong style="color: {{ $component['percentage'] >= ($component['component_pass_mark'] ?? 40) ? '#059669' : '#dc2626' }};">
                                                                {{ $component['percentage'] }}%
                                                            </strong>
                                                        </td>
                                                        <td style="padding: 6px 8px; text-align: center; border: 1px solid #d1d5db;">
                                                            <span style="
                                                                padding: 2px 6px; 
                                                                border-radius: 10px; 
                                                                font-size: 8px; 
                                                                font-weight: bold; 
                                                                color: white;
                                                                background: {{ $component['passed'] ? '#059669' : '#dc2626' }};
                                                            ">
                                                                {{ $component['passed'] ? 'PASS' : 'FAIL' }}
                                                            </span>
                                                        </td>
                                                        <td style="padding: 6px 8px; text-align: center; border: 1px solid #d1d5db; color: #6b7280; font-size: 9px;">
                                                            {{ $component['graded_date'] ? $component['graded_date']->format('d/m/Y') : 'Pending' }}
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                        
                                        <div style="margin-top: 8px; font-size: 9px; color: #6b7280; display: flex; justify-content: space-between;">
                                            <div>
                                                @if(collect($moduleData['components'])->where('is_must_pass', true)->count() > 0)
                                                    <span style="color: #dc2626;">⚠</span> Must-pass components are required for module completion
                                                @endif
                                            </div>
                                            <div>
                                                Components weighted average: <strong>{{ $moduleData['percentage'] }}%</strong>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <!-- Overall Summary -->
    @if($totalCredits > 0)
        <div style="background: #f9fafb; border: 2px solid #3b82f6; border-radius: 10px; padding: 20px; margin: 25px 0; page-break-inside: avoid;">
            <div style="text-align: center; font-size: 18px; font-weight: bold; margin-bottom: 20px; color: #1f2937; text-transform: uppercase;">
                🎓 ACADEMIC ACHIEVEMENT SUMMARY
            </div>
            
            @php
                $overallGrade = 'N/A';
                if ($overallGPA >= 2.5) $overallGrade = 'Distinction';
                elseif ($overallGPA >= 1.5) $overallGrade = 'Merit';
                elseif ($overallGPA >= 1.0) $overallGrade = 'Pass';
                elseif ($overallGPA > 0) $overallGrade = 'Unsuccessful';
                
                // Calculate detailed statistics
                $overallTotalMark = 0;
                $overallTotalWeight = 0;
                $completedModules = 0;
                $inProgressModules = 0;
                $failedModules = 0;
                $distinctionCount = 0;
                $meritCount = 0;
                $passCount = 0;
                
                // Programme modules
                foreach($programmeModules as $progData) {
                    foreach($progData['modules'] as $moduleData) {
                        if($moduleData['status'] === 'Completed') {
                            $completedModules++;
                            $credits = $moduleData['credits'];
                            $overallTotalMark += ($moduleData['percentage'] * $credits);
                            $overallTotalWeight += $credits;
                            
                            // Count grade distribution
                            if($moduleData['grade'] === 'D') $distinctionCount++;
                            elseif($moduleData['grade'] === 'M') $meritCount++;
                            elseif($moduleData['grade'] === 'P') $passCount++;
                        } elseif($moduleData['status'] === 'Failed') {
                            $failedModules++;
                        } else {
                            $inProgressModules++;
                        }
                    }
                }
                
                // Standalone modules
                foreach($standaloneModules as $moduleData) {
                    if($moduleData['status'] === 'Completed') {
                        $completedModules++;
                        $credits = $moduleData['credits'];
                        $overallTotalMark += ($moduleData['percentage'] * $credits);
                        $overallTotalWeight += $credits;
                        
                        // Count grade distribution
                        if($moduleData['grade'] === 'D') $distinctionCount++;
                        elseif($moduleData['grade'] === 'M') $meritCount++;
                        elseif($moduleData['grade'] === 'P') $passCount++;
                    } elseif($moduleData['status'] === 'Failed') {
                        $failedModules++;
                    } else {
                        $inProgressModules++;
                    }
                }
                
                $overallPercentage = $overallTotalWeight > 0 ? round($overallTotalMark / $overallTotalWeight, 1) : 0;
                $totalModules = $completedModules + $inProgressModules + $failedModules;
            @endphp
            
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap: 15px; margin-bottom: 20px;">
                <!-- Total Credits -->
                <div style="text-align: center; background: white; border-radius: 8px; padding: 15px; border: 1px solid #e5e7eb;">
                    <div style="font-size: 12px; color: #6b7280; margin-bottom: 5px;">TOTAL CREDITS</div>
                    <div style="font-size: 24px; font-weight: bold; color: #3b82f6;">{{ $totalCredits }}</div>
                    <div style="font-size: 10px; color: #6b7280;">{{ $totalCredits * 10 }} learning hours</div>
                </div>
                
                <!-- Overall Result -->
                <div style="text-align: center; background: white; border-radius: 8px; padding: 15px; border: 1px solid #e5e7eb;">
                    <div style="font-size: 12px; color: #6b7280; margin-bottom: 5px;">OVERALL RESULT</div>
                    <div style="font-size: 20px; font-weight: bold; color: {{ $overallGrade === 'Distinction' ? '#059669' : ($overallGrade === 'Merit' ? '#3b82f6' : ($overallGrade === 'Pass' ? '#10b981' : '#dc2626')) }};">
                        {{ $overallGrade }}
                    </div>
                    @if($overallPercentage > 0)
                        <div style="font-size: 12px; color: #6b7280;">({{ $overallPercentage }}%)</div>
                    @endif
                </div>
                
                <!-- Modules Completed -->
                <div style="text-align: center; background: white; border-radius: 8px; padding: 15px; border: 1px solid #e5e7eb;">
                    <div style="font-size: 12px; color: #6b7280; margin-bottom: 5px;">MODULES COMPLETED</div>
                    <div style="font-size: 24px; font-weight: bold; color: #059669;">{{ $completedModules }}</div>
                    <div style="font-size: 10px; color: #6b7280;">of {{ $totalModules }} total</div>
                </div>
                
                <!-- Success Rate -->
                <div style="text-align: center; background: white; border-radius: 8px; padding: 15px; border: 1px solid #e5e7eb;">
                    <div style="font-size: 12px; color: #6b7280; margin-bottom: 5px;">SUCCESS RATE</div>
                    <div style="font-size: 24px; font-weight: bold; color: #10b981;">
                        {{ $totalModules > 0 ? round(($completedModules / $totalModules) * 100, 1) : 0 }}%
                    </div>
                    <div style="font-size: 10px; color: #6b7280;">completion rate</div>
                </div>
            </div>
            
            <!-- Grade Distribution -->
            @if($completedModules > 0)
                <div style="background: white; border-radius: 8px; padding: 15px; border: 1px solid #e5e7eb;">
                    <div style="font-size: 14px; font-weight: bold; margin-bottom: 10px; color: #374151; text-align: center;">
                        GRADE DISTRIBUTION BREAKDOWN
                    </div>
                    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; text-align: center; font-size: 11px;">
                        <div style="background: #f0fdf4; padding: 8px; border-radius: 6px; border: 1px solid #22c55e;">
                            <div style="font-weight: bold; color: #15803d;">DISTINCTION (D)</div>
                            <div style="font-size: 18px; font-weight: bold; color: #15803d;">{{ $distinctionCount }}</div>
                            <div style="color: #15803d;">{{ $completedModules > 0 ? round(($distinctionCount / $completedModules) * 100, 1) : 0 }}%</div>
                        </div>
                        <div style="background: #eff6ff; padding: 8px; border-radius: 6px; border: 1px solid #3b82f6;">
                            <div style="font-weight: bold; color: #1d4ed8;">MERIT (M)</div>
                            <div style="font-size: 18px; font-weight: bold; color: #1d4ed8;">{{ $meritCount }}</div>
                            <div style="color: #1d4ed8;">{{ $completedModules > 0 ? round(($meritCount / $completedModules) * 100, 1) : 0 }}%</div>
                        </div>
                        <div style="background: #f0f9ff; padding: 8px; border-radius: 6px; border: 1px solid #0ea5e9;">
                            <div style="font-weight: bold; color: #0369a1;">PASS (P)</div>
                            <div style="font-size: 18px; font-weight: bold; color: #0369a1;">{{ $passCount }}</div>
                            <div style="color: #0369a1;">{{ $completedModules > 0 ? round(($passCount / $completedModules) * 100, 1) : 0 }}%</div>
                        </div>
                        <div style="background: #fef2f2; padding: 8px; border-radius: 6px; border: 1px solid #ef4444;">
                            <div style="font-weight: bold; color: #dc2626;">FAILED (F)</div>
                            <div style="font-size: 18px; font-weight: bold; color: #dc2626;">{{ $failedModules }}</div>
                            <div style="color: #dc2626;">{{ $totalModules > 0 ? round(($failedModules / $totalModules) * 100, 1) : 0 }}%</div>
                        </div>
                    </div>
                </div>
            @endif
            
            <!-- Summary Statement -->
            <div style="text-align: center; margin-top: 15px; padding: 10px; background: white; border-radius: 6px; border: 1px solid #e5e7eb; font-size: 11px; color: #374151;">
                <strong>Academic Standing:</strong> 
                @if($overallGrade === 'Distinction')
                    Exceptional performance demonstrating comprehensive understanding and superior achievement across all assessed areas.
                @elseif($overallGrade === 'Merit') 
                    Very good performance demonstrating clear understanding and proficient achievement in assessed areas.
                @elseif($overallGrade === 'Pass')
                    Satisfactory performance demonstrating adequate understanding and competent achievement meeting minimum requirements.
                @else
                    Performance below minimum academic standards required for successful completion.
                @endif
            </div>
        </div>
    @endif

    <!-- Certification -->
    <div class="certification">
        <p>
            <strong>CERTIFICATION:</strong> This is to certify that the above is a true and accurate record of the academic 
            achievements of {{ $student->full_name }} (Student Number: {{ $student->student_number }}) at 
            {{ $institution['name'] }}. This transcript includes all modules where results have been officially released.
        </p>
        <p>
            <strong>QQI GRADING SCALE:</strong> Distinction (D) 80-100%, Merit (M) 65-79%, Pass (P) 50-64%, Unsuccessful (U) 0-49%. 
            This follows the Quality and Qualifications Ireland (QQI) Level 5 grading bands.
        </p>
        <p>
            <strong>OVERALL RESULTS:</strong> Distinction = mostly distinctions achieved, Merit = mostly merits/distinctions achieved, 
            Pass = minimum standard achieved, Unsuccessful = below minimum standard.
        </p>
    </div>

    <!-- Footer -->
    <div class="footer">
        <div style="display: table; width: 100%; margin-bottom: 15px;">
            <div style="display: table-cell; width: 100px; vertical-align: middle;">
                @php
                    $footerLogoPath = public_path('images/logo.png');
                    $footerLogoSrc = '';
                    if (file_exists($footerLogoPath)) {
                        $footerImageData = file_get_contents($footerLogoPath);
                        $footerLogoSrc = 'data:image/png;base64,' . base64_encode($footerImageData);
                    }
                @endphp
                
                @if($footerLogoSrc)
                    <img src="{{ $footerLogoSrc }}" style="width: 80px; height: auto; opacity: 0.7;" alt="The Open College Logo">
                @else
                    <div style="width: 80px; height: 48px; background: #b8860b; border: 2px solid #daa520; text-align: center; line-height: 44px; color: white; font-weight: bold; font-size: 16px; opacity: 0.8;">TOC</div>
                @endif
            </div>
            <div style="display: table-cell; vertical-align: middle; text-align: center;">
                <p style="margin: 0;">
                    <strong>{{ $institution['name'] }}</strong><br>
                    This transcript was generated on {{ $generatedDate->format('d F Y \a\t H:i') }} and contains only officially released results.<br>
                    For verification purposes, please contact {{ $institution['name'] }} directly.
                </p>
            </div>
            <div style="display: table-cell; width: 100px; vertical-align: middle; text-align: right; font-size: 10px;">
                <strong>Verification Code:</strong><br>
                {{ strtoupper(substr(md5($student->student_number . $generatedDate->format('Y-m-d')), 0, 8)) }}
            </div>
        </div>
        
        <div class="security-features">
            🔒 This document contains security features including digital watermarks and unique verification codes<br>
            📧 {{ $institution['website'] }} | ☎ {{ $institution['phone'] }} | 📍 {{ $institution['address'] }}
        </div>
    </div>
</body>
</html>