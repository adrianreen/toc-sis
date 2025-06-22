<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $data['subject'] }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #2563eb;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }
        .content {
            background-color: #f8fafc;
            padding: 30px;
            border: 1px solid #e2e8f0;
        }
        .footer {
            background-color: #64748b;
            color: white;
            padding: 15px;
            text-align: center;
            border-radius: 0 0 8px 8px;
            font-size: 12px;
        }
        .system-info {
            background-color: #e0f2fe;
            padding: 15px;
            border-radius: 4px;
            margin-top: 20px;
        }
        .system-info h3 {
            margin-top: 0;
            color: #0369a1;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
        }
        .info-table td {
            padding: 5px;
            border-bottom: 1px solid #cbd5e1;
        }
        .info-table td:first-child {
            font-weight: bold;
            width: 40%;
        }
        .success {
            color: #059669;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>TOC Student Information System</h1>
        <p>Email System Test</p>
    </div>
    
    <div class="content">
        <h2>{{ $data['subject'] }}</h2>
        
        <p>{{ $data['message'] }}</p>
        
        <p class="success">✅ If you are reading this email, the TOC-SIS email system is working correctly!</p>
        
        <p><strong>Test Details:</strong></p>
        <ul>
            <li><strong>Test Time:</strong> {{ $data['test_time'] }}</li>
            <li><strong>Email Driver:</strong> {{ config('mail.default') }}</li>
            <li><strong>Queue Driver:</strong> {{ config('queue.default') }}</li>
        </ul>
        
        @if(isset($data['system_info']))
        <div class="system-info">
            <h3>System Information</h3>
            <table class="info-table">
                @foreach($data['system_info'] as $key => $value)
                <tr>
                    <td>{{ $key }}</td>
                    <td>{{ $value }}</td>
                </tr>
                @endforeach
            </table>
        </div>
        @endif
        
        <h3>Next Steps for Production:</h3>
        <ol>
            <li>Verify your email service provider configuration</li>
            <li>Configure DNS records (SPF, DKIM, DMARC)</li>
            <li>Test with different email providers to ensure deliverability</li>
            <li>Monitor email delivery logs and bounce rates</li>
            <li>Set up queue workers for background email processing</li>
        </ol>
    </div>
    
    <div class="footer">
        <p>The Open College - Student Information System</p>
        <p>This is an automated test email from the TOC-SIS system.</p>
    </div>
</body>
</html>