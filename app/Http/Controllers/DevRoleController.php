<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User; // Make sure you have your User model imported

class DevRoleController extends Controller
{
    /**
     * Switch user role.
     * WARNING: This is always active if the route is enabled.
     * Ensure 'role' attribute is fillable in your User model.
     */
    public function switchRole(Request $request)
    {
        $validated = $request->validate([
            // Add all your roles here
            'role' => 'required|string|in:student,teacher,student_services,manager,admin',
        ]);

        $user = Auth::user();
        if ($user) {
            $user->role = $validated['role'];
            $user->save(); // Persist the change

            return redirect()->back()->with('dev_status', 'Role switched to: ' . ucfirst(str_replace('_', ' ', $validated['role'])));
        }

        return redirect()->back()->with('dev_error', 'Could not switch role. User not authenticated.');
    }
}