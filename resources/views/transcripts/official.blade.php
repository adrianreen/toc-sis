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

    <!-- Academic Records by Programme -->
    @foreach($programmeModules as $programmeData)
        <div class="programme-section">
            <div class="programme-header">
                {{ $programmeData['programme']->title }} ({{ $programmeData['programme']->code }})
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
                                <td>{{ $moduleData['module']->code }}</td>
                                <td>{{ $moduleData['module']->title }}</td>
                                <td style="text-align: center;">{{ $moduleData['credits'] }}</td>
                                <td class="grade-cell">
                                    @if($moduleData['grade'])
                                        {{ $moduleData['grade'] }}
                                        @php
                                            // Calculate overall percentage for this module using new architecture
                                            $totalMark = 0;
                                            $totalWeight = 0;
                                            $gradeRecords = \App\Models\StudentGradeRecord::where('student_id', $moduleData['enrolment']->student_id)
                                                ->where('module_instance_id', $moduleData['enrolment']->module_instance_id)
                                                ->where('is_visible_to_student', true)
                                                ->whereNotNull('grade')
                                                ->get();
                                            
                                            foreach($gradeRecords as $gradeRecord) {
                                                // Find weight from module assessment strategy
                                                $assessmentStrategy = $moduleData['module']->assessment_strategy ?? [];
                                                $weight = 100; // Default weight
                                                foreach($assessmentStrategy as $component) {
                                                    if($component['component_name'] === $gradeRecord->assessment_component_name) {
                                                        $weight = $component['weighting'] ?? 100;
                                                        break;
                                                    }
                                                }
                                                $totalMark += ($gradeRecord->grade * $weight / 100);
                                                $totalWeight += $weight;
                                            }
                                            $percentage = $totalWeight > 0 ? round($totalMark, 1) : 0;
                                        @endphp
                                        <br><small style="color: #666;">({{ $percentage }}%)</small>
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
                                
                                // Calculate overall programme percentage
                                $totalMark = 0;
                                $totalWeight = 0;
                                foreach($programmeData['modules'] as $moduleData) {
                                    if($moduleData['grade'] && $moduleData['status'] === 'Completed') {
                                        $credits = $moduleData['credits'];
                                        $modulePercentage = 0;
                                        $moduleWeightTotal = 0;
                                        $gradeRecords = \App\Models\StudentGradeRecord::where('student_id', $moduleData['enrolment']->student_id)
                                            ->where('module_instance_id', $moduleData['enrolment']->module_instance_id)
                                            ->where('is_visible_to_student', true)
                                            ->whereNotNull('grade')
                                            ->get();
                                        
                                        foreach($gradeRecords as $gradeRecord) {
                                            $assessmentStrategy = $moduleData['module']->assessment_strategy ?? [];
                                            $weight = 100;
                                            foreach($assessmentStrategy as $component) {
                                                if($component['component_name'] === $gradeRecord->assessment_component_name) {
                                                    $weight = $component['weighting'] ?? 100;
                                                    break;
                                                }
                                            }
                                            $modulePercentage += ($gradeRecord->grade * $weight / 100);
                                            $moduleWeightTotal += $weight;
                                        }
                                        if($moduleWeightTotal > 0) {
                                            $finalModulePercentage = $modulePercentage;
                                            $totalMark += ($finalModulePercentage * $credits);
                                            $totalWeight += $credits;
                                        }
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

    <!-- Overall Summary -->
    @if($totalCredits > 0)
        <div class="overall-summary">
            <div style="font-size: 18px; font-weight: bold; margin-bottom: 15px;">
                ACADEMIC SUMMARY
            </div>
            <div style="display: table; width: 100%; margin: 0 auto;">
                <div style="display: table-row;">
                    <div style="display: table-cell; padding: 10px; text-align: center;">
                        <div style="font-size: 14px;">Total Credits Earned</div>
                        <div style="font-size: 20px; font-weight: bold;">{{ $totalCredits }}</div>
                    </div>
                    <div style="display: table-cell; padding: 10px; text-align: center;">
                        <div style="font-size: 14px;">Overall Result</div>
                        <div class="overall-gpa">
                            @php
                                $overallGrade = 'N/A';
                                if ($overallGPA >= 2.5) $overallGrade = 'Distinction';
                                elseif ($overallGPA >= 1.5) $overallGrade = 'Merit';
                                elseif ($overallGPA >= 1.0) $overallGrade = 'Pass';
                                elseif ($overallGPA > 0) $overallGrade = 'Unsuccessful';
                                
                                // Calculate overall percentage across all programmes
                                $overallTotalMark = 0;
                                $overallTotalWeight = 0;
                                foreach($programmeModules as $progData) {
                                    foreach($progData['modules'] as $moduleData) {
                                        if($moduleData['grade'] && $moduleData['status'] === 'Completed') {
                                            $credits = $moduleData['credits'];
                                            $modulePercentage = 0;
                                            $moduleWeightTotal = 0;
                                            $gradeRecords = \App\Models\StudentGradeRecord::where('student_id', $moduleData['enrolment']->student_id)
                                                ->where('module_instance_id', $moduleData['enrolment']->module_instance_id)
                                                ->where('is_visible_to_student', true)
                                                ->whereNotNull('grade')
                                                ->get();
                                            
                                            foreach($gradeRecords as $gradeRecord) {
                                                $assessmentStrategy = $moduleData['module']->assessment_strategy ?? [];
                                                $weight = 100;
                                                foreach($assessmentStrategy as $component) {
                                                    if($component['component_name'] === $gradeRecord->assessment_component_name) {
                                                        $weight = $component['weighting'] ?? 100;
                                                        break;
                                                    }
                                                }
                                                $modulePercentage += ($gradeRecord->grade * $weight / 100);
                                                $moduleWeightTotal += $weight;
                                            }
                                            if($moduleWeightTotal > 0) {
                                                $finalModulePercentage = $modulePercentage;
                                                $overallTotalMark += ($finalModulePercentage * $credits);
                                                $overallTotalWeight += $credits;
                                            }
                                        }
                                    }
                                }
                                $overallPercentage = $overallTotalWeight > 0 ? round($overallTotalMark / $overallTotalWeight, 1) : 0;
                            @endphp
                            {{ $overallGrade }}
                            @if($overallPercentage > 0)
                                <br><small style="color: #ddd; font-size: 16px;">({{ $overallPercentage }}%)</small>
                            @endif
                        </div>
                    </div>
                </div>
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