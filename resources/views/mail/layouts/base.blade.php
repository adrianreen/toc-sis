<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject ?? 'The Open College' }}</title>
    <style>
        /* Reset and base styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            line-height: 1.6;
            color: #374151;
            background-color: #f9fafb;
        }
        
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        
        /* Header */
        .email-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 32px 24px;
            text-align: center;
        }
        
        .email-header img {
            max-height: 60px;
            max-width: 200px;
            margin-bottom: 16px;
        }
        
        .email-header h1 {
            color: #ffffff;
            font-size: 24px;
            font-weight: 600;
            margin: 0;
        }
        
        /* Content */
        .email-content {
            padding: 32px 24px;
        }
        
        .email-content h2 {
            color: #1f2937;
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 16px;
        }
        
        .email-content h3 {
            color: #374151;
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 12px;
            margin-top: 24px;
        }
        
        .email-content p {
            margin-bottom: 16px;
            color: #4b5563;
        }
        
        .email-content ul, .email-content ol {
            margin-bottom: 16px;
            padding-left: 24px;
        }
        
        .email-content li {
            margin-bottom: 8px;
            color: #4b5563;
        }
        
        /* Buttons */
        .btn {
            display: inline-block;
            padding: 12px 24px;
            margin: 16px 8px 16px 0;
            background-color: #4f46e5;
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: background-color 0.3s ease;
        }
        
        .btn:hover {
            background-color: #4338ca;
        }
        
        .btn-secondary {
            background-color: #6b7280;
        }
        
        .btn-secondary:hover {
            background-color: #4b5563;
        }
        
        .btn-success {
            background-color: #059669;
        }
        
        .btn-success:hover {
            background-color: #047857;
        }
        
        /* Info boxes */
        .info-box {
            background-color: #f0f9ff;
            border-left: 4px solid #0ea5e9;
            padding: 16px;
            margin: 24px 0;
            border-radius: 0 8px 8px 0;
        }
        
        .info-box p {
            margin: 0;
            color: #0c4a6e;
        }
        
        .warning-box {
            background-color: #fefce8;
            border-left: 4px solid #eab308;
            padding: 16px;
            margin: 24px 0;
            border-radius: 0 8px 8px 0;
        }
        
        .warning-box p {
            margin: 0;
            color: #713f12;
        }
        
        /* Student details table */
        .student-details {
            background-color: #f8fafc;
            border-radius: 8px;
            padding: 20px;
            margin: 24px 0;
        }
        
        .student-details h3 {
            color: #1e293b;
            margin-top: 0;
            margin-bottom: 16px;
        }
        
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .detail-row:last-child {
            border-bottom: none;
        }
        
        .detail-label {
            font-weight: 600;
            color: #475569;
            width: 40%;
        }
        
        .detail-value {
            color: #1e293b;
            width: 60%;
            text-align: right;
        }
        
        /* Footer */
        .email-footer {
            background-color: #f8fafc;
            padding: 24px;
            text-align: center;
            border-top: 1px solid #e5e7eb;
        }
        
        .email-footer p {
            color: #6b7280;
            font-size: 14px;
            margin-bottom: 8px;
        }
        
        .email-footer a {
            color: #4f46e5;
            text-decoration: none;
        }
        
        .email-footer a:hover {
            text-decoration: underline;
        }
        
        /* Responsive */
        @media only screen and (max-width: 600px) {
            .email-container {
                margin: 0;
                border-radius: 0;
            }
            
            .email-header, .email-content, .email-footer {
                padding-left: 16px;
                padding-right: 16px;
            }
            
            .btn {
                display: block;
                text-align: center;
                margin: 16px 0;
            }
            
            .detail-row {
                flex-direction: column;
                gap: 4px;
            }
            
            .detail-label, .detail-value {
                width: 100%;
                text-align: left;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="email-header">
            @if(isset($logo_url))
                <img src="{{ $logo_url }}" alt="{{ config('app.name') }}">
            @endif
            <h1>{{ config('app.name', 'The Open College') }}</h1>
        </div>
        
        <!-- Content -->
        <div class="email-content">
            @yield('content')
        </div>
        
        <!-- Footer -->
        <div class="email-footer">
            <p><strong>{{ config('app.name', 'The Open College') }}</strong></p>
            <p>
                Email: <a href="mailto:{{ config('mail.from.address') }}">{{ config('mail.from.address') }}</a> |
                Phone: +353 1 234 5678
            </p>
            <p>
                <a href="{{ url('/') }}">Student Portal</a> |
                <a href="{{ url('/') }}">Visit Our Website</a>
            </p>
            <p style="font-size: 12px; color: #9ca3af; margin-top: 16px;">
                This email was sent to {{ $recipient_email ?? 'you' }}. 
                If you have any questions, please contact us using the details above.
            </p>
        </div>
    </div>
</body>
</html>