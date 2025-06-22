# Production Email System Configuration Guide

## Overview

The TOC-SIS system includes a comprehensive email system that requires proper configuration for production deployment. This guide provides step-by-step instructions for setting up email delivery.

## Current Status

- ✅ **Email Templates**: System includes professional, responsive email templates
- ✅ **Email Queue System**: Background processing configured with Laravel Queues
- ✅ **Audit Logging**: Complete delivery tracking via EmailLog model
- ✅ **User Interface**: Student email management in admin sidebar
- ✅ **Variable System**: 20+ dynamic variables for email personalization
- ⚠️ **Email Driver**: Currently configured for development (log driver)

## Critical: Development vs Production Configuration

### Current Development Configuration
```bash
MAIL_MAILER=log  # Emails written to log files, not delivered
MAIL_FROM_ADDRESS=noreply@theopencollege.com
MAIL_FROM_NAME="The Open College"
```

### Required Production Configuration

Choose ONE of the following professional email services:

## Option 1: Mailgun (Recommended for Small-Medium Volume)

**Benefits**: Free tier (5,000 emails/month), excellent deliverability, easy setup

```bash
# Add to .env file
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailgun.org
MAIL_PORT=587
MAIL_USERNAME=postmaster@mg.yourdomain.com
MAIL_PASSWORD=your-mailgun-smtp-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@theopencollege.com
MAIL_FROM_NAME="The Open College"

# Mailgun-specific settings
MAILGUN_DOMAIN=mg.yourdomain.com
MAILGUN_SECRET=your-mailgun-api-key
```

**Setup Steps**:
1. Create account at https://mailgun.com
2. Add and verify your domain
3. Get SMTP credentials from Mailgun dashboard
4. Configure DNS records (SPF, DKIM, CNAME)
5. Update .env with credentials above

## Option 2: SendGrid (Recommended for High Volume)

**Benefits**: Excellent analytics, high deliverability, scalable pricing

```bash
# Add to .env file
MAIL_MAILER=smtp
MAIL_HOST=smtp.sendgrid.net
MAIL_PORT=587
MAIL_USERNAME=apikey
MAIL_PASSWORD=your-sendgrid-api-key
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@theopencollege.com
MAIL_FROM_NAME="The Open College"
```

**Setup Steps**:
1. Create account at https://sendgrid.com
2. Create an API key with Mail Send permissions
3. Configure domain authentication (SPF, DKIM)
4. Update .env with credentials above

## Option 3: Amazon SES (Recommended for AWS Infrastructure)

**Benefits**: Very cost-effective for high volume, integrates with AWS

```bash
# Add to .env file
MAIL_MAILER=ses
AWS_ACCESS_KEY_ID=your-aws-access-key
AWS_SECRET_ACCESS_KEY=your-aws-secret-key
AWS_DEFAULT_REGION=eu-west-1
MAIL_FROM_ADDRESS=noreply@theopencollege.com
MAIL_FROM_NAME="The Open College"
```

**Setup Steps**:
1. Configure AWS SES in your AWS account
2. Verify your domain in SES
3. Request production access (removes sending limits)
4. Create IAM user with SES permissions
5. Update .env with AWS credentials

## Option 4: Microsoft 365 / Office 365 SMTP

**Benefits**: If you already use Microsoft 365 for email

```bash
# Add to .env file
MAIL_MAILER=smtp
MAIL_HOST=smtp.office365.com
MAIL_PORT=587
MAIL_USERNAME=your-office365-email@yourdomain.com
MAIL_PASSWORD=your-office365-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@theopencollege.com
MAIL_FROM_NAME="The Open College"
```

**Note**: May require app-specific password if 2FA is enabled.

## DNS Configuration (Critical for Deliverability)

Regardless of provider, configure these DNS records:

### SPF Record
```
TXT record for yourdomain.com:
v=spf1 include:mailgun.org include:sendgrid.net include:amazonses.com ~all
```

### DKIM Records
Configure DKIM records as provided by your email service provider.

### DMARC Record
```
TXT record for _dmarc.yourdomain.com:
v=DMARC1; p=quarantine; rua=mailto:dmarc@yourdomain.com
```

## Queue Configuration for Production

Email sending uses Laravel queues for performance. Configure queue workers:

### Database Queue (Simple Setup)
```bash
# .env
QUEUE_CONNECTION=database

# Run queue worker (systemd service recommended)
php artisan queue:work --timeout=60
```

### Redis Queue (Recommended for Production)
```bash
# .env
QUEUE_CONNECTION=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Run queue worker
php artisan queue:work redis --timeout=60
```

### Systemd Service for Queue Worker
Create `/etc/systemd/system/toc-sis-worker.service`:

```ini
[Unit]
Description=TOC-SIS Queue Worker
After=network.target

[Service]
Type=simple
User=www-data
WorkingDirectory=/var/www/toc-sis
ExecStart=/usr/bin/php artisan queue:work --sleep=3 --tries=3 --timeout=60
Restart=always
RestartSec=5

[Install]
WantedBy=multi-user.target
```

Enable and start:
```bash
sudo systemctl enable toc-sis-worker
sudo systemctl start toc-sis-worker
```

## Testing Email Configuration

Use the built-in email test command:

```bash
# Test email sending
php artisan email:test your-email@example.com

# Test with specific template
php artisan email:test your-email@example.com --template=grade-notification

# Test queue processing
php artisan email:test your-email@example.com --queue
```

## Email Templates Available

The system includes these professional email templates:

1. **Welcome Email** - New student onboarding
2. **Grade Notification** - Assessment results with transcripts
3. **Assessment Reminder** - Upcoming deadline notifications
4. **Extension Approved** - Academic extension confirmations
5. **Deferral Approved** - Programme deferral confirmations
6. **Administrative Announcement** - System-wide messages

## Monitoring and Maintenance

### Email Delivery Monitoring
- Check EmailLog model for delivery tracking
- Monitor bounce and complaint rates
- Set up alerts for failed deliveries

### Queue Monitoring
```bash
# Check queue status
php artisan queue:work --once

# Monitor failed jobs
php artisan queue:failed

# Retry failed jobs
php artisan queue:retry all
```

### Performance Optimization
- Use queue batching for bulk emails
- Configure Redis for queue performance
- Monitor email service quotas and limits

## Security Considerations

1. **API Keys**: Store email service API keys securely
2. **Rate Limiting**: Respect email service rate limits
3. **Spam Prevention**: Configure SPF, DKIM, and DMARC properly
4. **Content Filtering**: Ensure academic emails aren't flagged as spam
5. **Data Protection**: Email logs contain student data - secure appropriately

## Post-Deployment Verification Checklist

- [ ] Email service account created and verified
- [ ] DNS records configured (SPF, DKIM, DMARC)
- [ ] .env file updated with production credentials
- [ ] Queue worker running and monitored
- [ ] Test emails delivered successfully
- [ ] Email templates rendering correctly
- [ ] Transcript attachments working
- [ ] Delivery tracking functional
- [ ] Bounce handling configured

## Troubleshooting Common Issues

### Emails Not Sending
1. Check queue worker is running: `systemctl status toc-sis-worker`
2. Verify .env configuration
3. Test SMTP connection: `php artisan tinker` then test connection
4. Check Laravel logs: `storage/logs/laravel.log`

### Poor Deliverability
1. Verify SPF/DKIM/DMARC records
2. Check sender reputation
3. Review email content for spam triggers
4. Monitor bounce/complaint rates

### Queue Processing Issues
1. Check failed jobs: `php artisan queue:failed`
2. Verify database/Redis connection
3. Monitor queue worker memory usage
4. Restart queue workers if needed

## Contact and Support

For email service-specific support:
- **Mailgun**: https://documentation.mailgun.com/
- **SendGrid**: https://docs.sendgrid.com/
- **Amazon SES**: https://docs.aws.amazon.com/ses/
- **Office 365**: https://docs.microsoft.com/en-us/exchange/

For TOC-SIS email system support, check:
- Application logs in `storage/logs/`
- EmailLog model records
- Queue job status and failures

---

**⚠️ CRITICAL**: The system will not send emails until production email configuration is completed. This affects student notifications, result delivery, and all email-based workflows.