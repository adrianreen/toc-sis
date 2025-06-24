{{-- Email Dashboard Widget Component --}}
@props(['user'])

<div class="bg-white rounded-xl border border-slate-200 shadow-sm" 
     x-data="emailWidget" 
     x-init="loadEmailData()">
    
    <div class="p-6 border-b border-slate-200">
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold text-slate-900">College Email</h3>
            <div class="flex items-center space-x-2">
                <span x-show="emailData.unread_count > 0" 
                      class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-sm font-medium"
                      x-text="`${emailData.unread_count} unread`">
                </span>
                <span x-show="emailData.unread_count === 0 && !emailData.error" 
                      class="bg-green-100 text-green-800 px-2 py-1 rounded-full text-sm font-medium">
                    All caught up
                </span>
                <button @click="refreshEmails()" 
                        :disabled="loading"
                        class="p-1 text-slate-400 hover:text-slate-600 transition-colors cursor-pointer"
                        :class="{ 'animate-spin': loading }">
                    <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                </button>
            </div>
        </div>
    </div>
    
    <div class="p-6">
        {{-- Loading State --}}
        <div x-show="loading && !emailData.recent_emails" class="text-center py-8">
            <div class="animate-spin w-8 h-8 border-4 border-blue-500 border-t-transparent rounded-full mx-auto mb-4"></div>
            <p class="text-slate-500 text-sm">Loading your emails...</p>
        </div>

        {{-- Error State --}}
        <div x-show="emailData.error && !loading" class="text-center py-8">
            <div x-show="emailData.error === 'no_permissions'" class="text-amber-600">
                <i data-lucide="lock" class="w-12 h-12 mx-auto mb-4"></i>
                <h4 class="font-medium mb-2">Email Access Not Authorized</h4>
                <p class="text-sm text-slate-600 mb-4">Please re-authenticate to enable email access</p>
                <a href="{{ route('login') }}" 
                   class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors cursor-pointer">
                    Re-authenticate
                </a>
            </div>
            <div x-show="emailData.error === 'api_error'" class="text-red-600">
                <i data-lucide="wifi-off" class="w-12 h-12 mx-auto mb-4"></i>
                <h4 class="font-medium mb-2">Unable to Load Emails</h4>
                <p class="text-sm text-slate-600 mb-4" x-text="emailData.message"></p>
                <button @click="refreshEmails()" 
                        class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors cursor-pointer">
                    Try Again
                </button>
            </div>
        </div>

        {{-- Email List --}}
        <div x-show="!loading && !emailData.error && emailData.recent_emails">
            <div x-show="emailData.recent_emails && emailData.recent_emails.length > 0" class="space-y-3">
                <p class="text-xs text-slate-500 mb-3">💡 Click any email to search for it in Outlook</p>
                <template x-for="email in emailData.recent_emails" :key="email.id">
                    <div class="flex items-start justify-between p-3 rounded-lg hover:bg-slate-50 transition-colors border border-slate-100 cursor-pointer"
                         @click="openEmail(email.id, email.subject)">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center space-x-2 mb-1">
                                <p class="text-sm font-medium text-slate-900 truncate" x-text="email.subject"></p>
                                <span x-show="!email.is_read" class="w-2 h-2 bg-blue-500 rounded-full flex-shrink-0"></span>
                                <span x-show="email.is_important" class="text-orange-500 flex-shrink-0">
                                    <i data-lucide="star" class="w-3 h-3"></i>
                                </span>
                            </div>
                            <p class="text-xs text-slate-600 truncate" x-text="email.sender"></p>
                            <p class="text-xs text-slate-500" x-text="email.received_time_human"></p>
                        </div>
                    </div>
                </template>
            </div>
            
            <div x-show="emailData.recent_emails && emailData.recent_emails.length === 0" class="text-center py-8">
                <i data-lucide="mail" class="w-12 h-12 text-slate-400 mx-auto mb-4"></i>
                <p class="text-slate-500">No recent emails</p>
            </div>
        </div>

        {{-- Actions --}}
        <div x-show="!loading && !emailData.error" class="mt-6 pt-4 border-t border-slate-200 flex space-x-3">
            <a href="https://outlook.office365.com/mail/inbox" 
               target="_blank"
               class="flex-1 bg-blue-600 text-white text-center py-2 px-4 rounded-lg hover:bg-blue-700 transition-colors cursor-pointer">
                <div class="flex items-center justify-center space-x-2">
                    <span>Open Inbox</span>
                    <i data-lucide="external-link" class="w-4 h-4"></i>
                </div>
            </a>
            <button @click="refreshEmails()" 
                    :disabled="loading"
                    class="px-4 py-2 border border-slate-300 text-slate-700 rounded-lg hover:bg-slate-50 transition-colors cursor-pointer">
                <i data-lucide="refresh-cw" class="w-4 h-4" :class="{ 'animate-spin': loading }"></i>
            </button>
        </div>

        {{-- Last Updated --}}
        <div x-show="emailData.last_updated && !loading" class="mt-3 text-center">
            <p class="text-xs text-slate-400">
                Last updated: <span x-text="formatLastUpdated(emailData.last_updated)"></span>
            </p>
        </div>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('emailWidget', () => ({
        emailData: {
            unread_count: 0,
            recent_emails: [],
            error: null,
            message: '',
            last_updated: null
        },
        loading: false,

        async loadEmailData() {
            if (this.loading) return;
            
            this.loading = true;
            
            try {
                const response = await fetch('/api/email-summary', {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                if (response.ok) {
                    this.emailData = await response.json();
                } else {
                    this.emailData = {
                        error: 'api_error',
                        message: 'Failed to load emails. Please try again.',
                        unread_count: 0,
                        recent_emails: []
                    };
                }
            } catch (error) {
                console.error('Email widget error:', error);
                this.emailData = {
                    error: 'api_error',
                    message: 'Network error. Please check your connection.',
                    unread_count: 0,
                    recent_emails: []
                };
            } finally {
                this.loading = false;
            }
        },

        async refreshEmails() {
            if (this.loading) return;
            
            this.loading = true;
            
            try {
                const response = await fetch('/api/email-summary?refresh=1', {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                if (response.ok) {
                    this.emailData = await response.json();
                } else {
                    // Keep existing data but show error
                    this.emailData.error = 'api_error';
                    this.emailData.message = 'Failed to refresh emails.';
                }
            } catch (error) {
                console.error('Email refresh error:', error);
                this.emailData.error = 'api_error';
                this.emailData.message = 'Network error during refresh.';
            } finally {
                this.loading = false;
            }
        },

        formatLastUpdated(timestamp) {
            if (!timestamp) return '';
            
            try {
                const date = new Date(timestamp);
                const now = new Date();
                const diffInMinutes = Math.floor((now - date) / (1000 * 60));

                if (diffInMinutes < 1) return 'Just now';
                if (diffInMinutes < 60) return `${diffInMinutes}m ago`;
                if (diffInMinutes < 1440) return `${Math.floor(diffInMinutes / 60)}h ago`;
                return date.toLocaleDateString();
            } catch (error) {
                return '';
            }
        },

        openEmail(emailId, subject) {
            // Since direct email ID links don't work reliably, let's try a search approach
            // This opens Outlook with a search for the email subject, which should help users find it quickly
            
            if (subject && subject.trim()) {
                // Create a search URL that searches for the email subject
                // This should help users quickly locate the specific email
                const searchQuery = encodeURIComponent(subject.trim());
                const outlookUrl = `https://outlook.office365.com/mail/search/query/${searchQuery}`;
                
                window.open(outlookUrl, '_blank');
            } else {
                // Fallback to inbox if no subject available
                window.open('https://outlook.office365.com/mail/inbox', '_blank');
            }
            
            // Optional: Track email opens for analytics
            this.trackEmailOpen(emailId);
        },

        trackEmailOpen(emailId) {
            // Optional analytics tracking
            try {
                fetch('/api/email/track-open', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ email_id: emailId })
                }).catch(error => {
                    // Silently fail analytics tracking
                    console.debug('Email tracking failed:', error);
                });
            } catch (error) {
                // Silently fail analytics tracking
                console.debug('Email tracking error:', error);
            }
        }
    }))
});
</script>