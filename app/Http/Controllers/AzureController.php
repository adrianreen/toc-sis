<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;

class AzureController extends Controller
{
    /**
     * Redirect to Azure AD for authentication
     */
    public function redirect()
    {
        return Socialite::driver('azure')
            ->scopes(['openid', 'profile', 'email', 'User.Read', 'Directory.Read.All'])
            ->with(['prompt' => 'select_account'])
            ->redirect();
    }

    /**
     * Handle the Azure AD callback
     */
    public function callback(Request $request)
    {
        try {
            // Get the user data from Azure AD
            $azureUser = Socialite::driver('azure')->user();
            
            Log::info('Azure AD callback received', [
                'email' => $azureUser->getEmail(),
                'name' => $azureUser->getName(),
                'azure_id' => $azureUser->getId(),
            ]);

            // Get user groups from Microsoft Graph API
            $groups = $this->getUserGroups($azureUser->token);
            
            Log::info('User groups retrieved', [
                'email' => $azureUser->getEmail(),
                'groups' => $groups,
            ]);

            // Determine the user's role based on group membership
            $role = $this->determineUserRole($groups);
            
            Log::info('Role determined', [
                'email' => $azureUser->getEmail(),
                'role' => $role,
                'groups' => $groups,
            ]);

            // Create or update the user
            $user = User::updateOrCreate(
                ['azure_id' => $azureUser->getId()],
                [
                    'name' => $azureUser->getName(),
                    'email' => $azureUser->getEmail(),
                    'role' => $role,
                    'azure_groups' => $groups,
                    'last_login_at' => now(),
                ]
            );

            // Log the user in
            Auth::login($user, true);

            Log::info('User logged in successfully', [
                'user_id' => $user->id,
                'email' => $user->email,
                'role' => $user->role,
            ]);

            return redirect()->route('dashboard')->with('success', 'Welcome to TOC SIS!');

        } catch (\Exception $e) {
            Log::error('Azure AD authentication failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('home')->with('error', 'Authentication failed. Please try again.');
        }
    }

    /**
     * Get user groups from Microsoft Graph API
     */
    protected function getUserGroups(string $accessToken): array
    {
        try {
            $response = Http::withToken($accessToken)
                ->get('https://graph.microsoft.com/v1.0/me/memberOf', [
                    '$select' => 'id,displayName,mailNickname',
                    '$filter' => "securityEnabled eq true"
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $groups = [];

                foreach ($data['value'] as $group) {
                    $groups[] = [
                        'id' => $group['id'],
                        'displayName' => $group['displayName'] ?? null,
                        'mailNickname' => $group['mailNickname'] ?? null,
                    ];
                }

                Log::info('Groups retrieved from Graph API', [
                    'total_groups' => count($groups),
                    'groups' => $groups,
                ]);

                return $groups;
            } else {
                Log::warning('Failed to retrieve groups from Graph API', [
                    'status' => $response->status(),
                    'response' => $response->body(),
                ]);
                return [];
            }
        } catch (\Exception $e) {
            Log::error('Error retrieving user groups', [
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }

    /**
     * Determine user role based on group membership
     */
    protected function determineUserRole(array $groups): string
    {
        // Extract group IDs from the groups array
        $groupIds = array_column($groups, 'id');
        
        Log::info('Determining role for groups', [
            'group_ids' => $groupIds,
            'manager_group' => config('services.azure.group_managers', env('AZURE_GROUP_MANAGERS')),
            'student_services_group' => config('services.azure.group_student_services', env('AZURE_GROUP_STUDENT_SERVICES')),
            'teachers_group' => config('services.azure.group_teachers', env('AZURE_GROUP_TEACHERS')),
        ]);

        // Check for manager role (highest priority)
        if (in_array(env('AZURE_GROUP_MANAGERS'), $groupIds)) {
            return 'manager';
        }

        // Check for student services role
        if (in_array(env('AZURE_GROUP_STUDENT_SERVICES'), $groupIds)) {
            return 'student_services';
        }

        // Check for teacher role
        if (in_array(env('AZURE_GROUP_TEACHERS'), $groupIds)) {
            return 'teacher';
        }

        // Default role
        return 'student';
    }

    /**
     * Logout the user
     */
    public function logout(Request $request)
    {
        $user = Auth::user();
        
        Log::info('User logging out', [
            'user_id' => $user?->id,
            'email' => $user?->email,
        ]);

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'You have been logged out successfully.');
    }
}