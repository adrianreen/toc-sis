<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct(
        private NotificationService $notificationService
    ) {}

    /**
     * Display user's notifications
     */
    public function index()
    {
        $notifications = auth()->user()->notifications()
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('notifications.index', compact('notifications'));
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(Notification $notification)
    {
        if ($notification->user_id !== auth()->id()) {
            abort(403);
        }

        $notification->markAsRead();

        if ($notification->action_url) {
            return redirect($notification->action_url);
        }

        return redirect()->back();
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        $updatedCount = auth()->user()->unreadNotifications()->count();

        auth()->user()->unreadNotifications()->update([
            'is_read' => true,
            'read_at' => now(),
        ]);

        // Clear the cached unread count
        \Cache::forget('unread_notifications_'.auth()->id());

        // Return JSON for AJAX requests
        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Marked {$updatedCount} notifications as read.",
                'updated_count' => $updatedCount,
            ]);
        }

        return redirect()->back()->with('success', 'All notifications marked as read.');
    }

    /**
     * Admin: Create announcement
     */
    public function createAnnouncement(Request $request)
    {
        if (! auth()->user()->isManager()) {
            abort(403);
        }

        if ($request->isMethod('post')) {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'message' => 'required|string|max:1000',
                'target_audience' => 'required|in:all,students,staff',
                'action_url' => 'nullable|url',
            ]);

            // Get target users
            $userIds = $this->getTargetUsers($validated['target_audience']);

            $this->notificationService->createAnnouncement(
                $userIds,
                $validated['title'],
                $validated['message'],
                $validated['action_url'] ?? null
            );

            return redirect()->back()->with('success', 'Announcement sent to '.count($userIds).' users.');
        }

        return view('notifications.create-announcement');
    }

    /**
     * Admin: Notification management dashboard
     */
    public function adminDashboard()
    {
        if (! auth()->user()->isManager()) {
            abort(403);
        }

        $stats = [
            'total_notifications' => Notification::count(),
            'unread_notifications' => Notification::unread()->count(),
            'recent_notifications' => Notification::where('created_at', '>=', now()->subDays(7))->count(),
            'email_notifications_sent' => Notification::where('email_sent', true)->count(),
        ];

        $recentNotifications = Notification::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('notifications.admin-dashboard', compact('stats', 'recentNotifications'));
    }

    private function getTargetUsers(string $audience): array
    {
        switch ($audience) {
            case 'students':
                return User::where('role', 'student')->pluck('id')->toArray();
            case 'staff':
                return User::whereIn('role', ['manager', 'teacher', 'student_services'])->pluck('id')->toArray();
            case 'all':
            default:
                return User::pluck('id')->toArray();
        }
    }
}
