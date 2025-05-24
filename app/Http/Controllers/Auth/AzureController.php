<?php
// app/Http/Controllers/Auth/AzureController.php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class AzureController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('azure')->redirect();
    }

    public function callback()
    {
        try {
            $azureUser = Socialite::driver('azure')->user();
            
            // Get user's groups from Azure AD
            $groups = $azureUser->user['groups'] ?? [];
            
            $user = User::updateOrCreate(
                ['azure_id' => $azureUser->id],
                [
                    'name' => $azureUser->name,
                    'email' => $azureUser->email,
                    'azure_groups' => $groups,
                    'last_login_at' => now(),
                ]
            );
            
            // Determine role based on Azure AD groups
            $user->role = $this->determineRole($groups);
            $user->save();
            
            Auth::login($user);
            
            return redirect()->route('dashboard');
            
        } catch (\Exception $e) {
            return redirect('/')->with('error', 'Authentication failed');
        }
    }
    
    private function determineRole($groups)
    {
        // You'll need to update this with your actual Azure AD group IDs
        $roleMapping = [
            'manager' => env('AZURE_GROUP_MANAGERS'),
            'student_services' => env('AZURE_GROUP_STUDENT_SERVICES'),
            'teacher' => env('AZURE_GROUP_TEACHERS'),
        ];
        
        foreach ($roleMapping as $role => $groupId) {
            if (in_array($groupId, $groups)) {
                return $role;
            }
        }
        
        return 'student';
    }
    
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect('/');
    }
}