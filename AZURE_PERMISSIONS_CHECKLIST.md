# Azure AD Permissions Update Checklist

## Overview
To enable Microsoft Graph API email integration for the Outlook dashboard widget, you need to update your Azure AD app registration with additional permissions.

## ✅ Checklist: Azure Portal Changes Required

### Step 1: Access Azure Portal
- [ ] Log into [portal.azure.com](https://portal.azure.com)
- [ ] Navigate to **Azure Active Directory**
- [ ] Go to **App registrations**
- [ ] Find and select your **TOC-SIS application**

### Step 2: Add API Permissions
Current permissions (already configured):
- [x] Microsoft Graph → User.Read (Delegated)
- [x] Microsoft Graph → Group.Read.All (Delegated)
- [x] Microsoft Graph → Directory.Read.All (Delegated)

**NEW permissions to add:**
- [ ] **Microsoft Graph → Mail.Read (Delegated)**
  - Description: "Read user mail"
  - Required for: Reading student emails for dashboard widget
  
- [ ] **Microsoft Graph → offline_access (Delegated)**
  - Description: "Maintain access to data you have given it access to"
  - Required for: Refresh tokens to maintain email access

### Step 3: Grant Admin Consent
- [ ] After adding permissions, click **"Grant admin consent for [Your Organization]"**
- [ ] Confirm the consent dialog
- [ ] Verify all permissions show "Granted" status

### Step 4: Verify Configuration
Your final permissions list should include:
- [x] Microsoft Graph → User.Read (Delegated) ✓ Granted
- [x] Microsoft Graph → Group.Read.All (Delegated) ✓ Granted  
- [x] Microsoft Graph → Directory.Read.All (Delegated) ✓ Granted
- [ ] **Microsoft Graph → Mail.Read (Delegated) ✓ Granted** ← NEW
- [ ] **Microsoft Graph → offline_access (Delegated) ✓ Granted** ← NEW

## 🔧 Code Changes (Already Applied)

The following Laravel configuration files have been updated:

### ✅ config/services.php
```php
'scopes' => [
    'openid', 
    'profile', 
    'email', 
    'https://graph.microsoft.com/Mail.Read',        // NEW
    'https://graph.microsoft.com/offline_access'    // NEW
],
```

### ✅ app/Http/Controllers/Auth/AzureController.php
```php
->scopes([
    'User.Read', 
    'Group.Read.All', 
    'Directory.Read.All',
    'https://graph.microsoft.com/Mail.Read',        // NEW
    'https://graph.microsoft.com/offline_access'    // NEW
])
```

## 🚨 Important Notes

### User Consent Flow
- **First Login After Changes**: Users will see a new consent screen asking for email permissions
- **Existing Users**: Will need to re-authenticate to grant new permissions
- **New Users**: Will see all permissions in their initial consent flow

### Permission Scope Explanation
- **Mail.Read**: Read-only access to user emails (no send/delete capabilities)
- **offline_access**: Allows refresh tokens for background email fetching
- **Minimal Scope**: These are the minimum permissions needed for dashboard widget

### Testing After Changes
1. **Clear browser cache** for testing users
2. **Test login flow** with a student account
3. **Verify consent screen** shows email permissions
4. **Check access tokens** include Graph API scopes

## 🔒 Security Considerations

### Data Minimization
- Only requesting **Mail.Read** (not Mail.ReadWrite or Mail.Send)
- Dashboard widget will cache minimal email metadata only
- No email content storage, only subject lines and metadata

### Compliance
- **GDPR**: Email access requires explicit user consent (handled by Azure consent flow)
- **Educational Privacy**: Permissions are read-only and user-controlled
- **Audit Trail**: All email access will be logged in TOC-SIS activity logs

## 📱 User Experience Impact

### What Users Will See
1. **Enhanced Consent Screen**: Additional permission for "Read your email"
2. **Dashboard Widget**: New email summary widget showing:
   - Unread email count
   - Recent email subjects
   - Quick link to full Outlook
3. **Privacy Control**: Users can revoke access anytime through Azure AD

### What Users WON'T See
- **No Full Email Client**: Widget shows summary only
- **No Email Content**: Subject lines and metadata only
- **No Send Capability**: Read-only integration
- **No Email Storage**: Real-time API calls, minimal caching

## 🚀 Next Steps After Azure Changes

Once you've completed the Azure portal changes:

1. **Test Authentication**
   ```bash
   # Clear application cache
   php artisan cache:clear
   php artisan config:clear
   
   # Test login with student account
   # Check for new consent screen
   ```

2. **Install Graph API Package**
   ```bash
   composer require microsoft/microsoft-graph
   ```

3. **Implement Email Service**
   - OutlookService class for Graph API calls
   - Dashboard widget component
   - Token storage and encryption

4. **User Communication**
   - Notify students about new email widget
   - Explain privacy and benefits
   - Provide support for consent process

## ❓ Troubleshooting

### Common Issues
- **"Invalid Scope" Error**: Ensure permissions are granted admin consent
- **"Unauthorized" Response**: Check token includes Graph API scopes
- **Users Don't See Widget**: Verify they've completed new consent flow

### Support Contacts
- **Azure AD Issues**: IT administrator with Azure AD access
- **Development Issues**: Development team
- **User Support**: Student services for consent/privacy questions

---

**Status**: ⏳ Awaiting Azure AD permission updates
**Next Action**: Complete Azure portal changes above
**Timeline**: 30 minutes for Azure changes + testing