<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\User; // ADD THIS LINE
use App\Services\GraphTokenService;
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
            ->scopes([
                'User.Read', 
                'Group.Read.All', 
                'Directory.Read.All',
                'https://graph.microsoft.com/Mail.Read',
                'https://graph.microsoft.com/offline_access'
            ])
            ->redirect();
    }

    /**
     * Handle Azure AD callback and authenticate user
     * ENHANCED: Now includes student matching functionality
     */
    public function callback()
    {
        try {
            $azureUser = Socialite::driver('azure')->user();

            Log::info('Azure authentication callback received', [
                'user_id' => $azureUser->getId(),
                'email' => $azureUser->getEmail(),
                'name' => $azureUser->getName(),
            ]);

            // Get user's Azure AD groups for role assignment
            $groups = $this->getUserGroups($azureUser->getId(), $azureUser->token);

            // Determine user role based on group membership
            $role = $this->determineUserRole($groups);

            // ENHANCED: If role is 'student', try to find matching student record
            $studentRecord = null;
            if ($role === 'student') {
                $studentRecord = $this->findMatchingStudent($azureUser->getEmail());

                if ($studentRecord) {
                    Log::info('Found matching student record', [
                        'student_id' => $studentRecord->id,
                        'student_number' => $studentRecord->student_number,
                        'azure_email' => $azureUser->getEmail(),
                        'student_email' => $studentRecord->email,
                    ]);
                } else {
                    Log::warning('No matching student record found', [
                        'azure_email' => $azureUser->getEmail(),
                        'will_create_unlinked_account' => true,
                    ]);
                }
            }

            Log::info('User role determined', [
                'role' => $role,
                'groups_found' => $groups,
                'email' => $azureUser->getEmail(),
                'linked_to_student' => $studentRecord ? $studentRecord->student_number : null,
            ]);

            // Create or update user record
            $user = User::updateOrCreate(
                ['azure_id' => $azureUser->getId()],
                [
                    'name' => $azureUser->getName(),
                    'email' => $azureUser->getEmail(),
                    'role' => $role,
                    'azure_groups' => $groups,
                    'last_login_at' => now(),
                    'student_id' => $studentRecord ? $studentRecord->id : null, // ENHANCED: Store student relationship
                ]
            );

            // ENHANCED: Log successful student linking
            if ($studentRecord && $role === 'student') {
                Log::info('Student account successfully linked', [
                    'user_id' => $user->id,
                    'student_id' => $studentRecord->id,
                    'student_number' => $studentRecord->student_number,
                    'relationship_stored' => true,
                ]);
            }

            // Store Graph API tokens if available
            $this->storeGraphTokens($user, $azureUser);

            // Log the user in
            Auth::login($user);

            Log::info('User authenticated successfully', [
                'user_id' => $user->id,
                'email' => $user->email,
                'role' => $user->role,
                'linked_student' => $user->student_id,
                'has_graph_token' => $user->hasValidGraphToken(),
            ]);

            return redirect()->intended('/dashboard');

        } catch (\Exception $e) {
            Log::error('Azure authentication failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect('/')->with('error', 'Authentication failed. Please try again.');
        }
    }

    /**
     * ENHANCED: NEW METHOD - Find matching student record by email
     * Handles domain switching between @theopencollege.com and @student.theopencollege.com
     */
    private function findMatchingStudent($azureEmail)
    {
        try {
            Log::info('Searching for matching student record', ['azure_email' => $azureEmail]);

            // First, try direct email match
            $student = Student::where('email', $azureEmail)->first();

            if ($student) {
                Log::info('Found direct email match', [
                    'student_id' => $student->id,
                    'student_number' => $student->student_number,
                ]);

                return $student;
            }

            // Handle domain switching - try alternate domain
            $alternateEmail = null;

            if (str_ends_with($azureEmail, '@student.theopencollege.com')) {
                // Azure email uses new domain, try old domain in student record
                $alternateEmail = str_replace('@student.theopencollege.com', '@theopencollege.com', $azureEmail);
            } elseif (str_ends_with($azureEmail, '@theopencollege.com')) {
                // Azure email uses old domain, try new domain in student record
                $alternateEmail = str_replace('@theopencollege.com', '@student.theopencollege.com', $azureEmail);
            }

            if ($alternateEmail) {
                Log::info('Trying alternate domain', [
                    'original_email' => $azureEmail,
                    'alternate_email' => $alternateEmail,
                ]);

                $student = Student::where('email', $alternateEmail)->first();

                if ($student) {
                    Log::info('Found student with alternate domain', [
                        'student_id' => $student->id,
                        'student_number' => $student->student_number,
                        'student_email' => $student->email,
                        'azure_email' => $azureEmail,
                    ]);

                    // OPTIONAL: Update student email to match Azure email
                    // Uncomment next line if you want to sync the emails:
                    // $student->update(['email' => $azureEmail]);

                    return $student;
                }
            }

            Log::info('No matching student record found', [
                'azure_email' => $azureEmail,
                'alternate_tried' => $alternateEmail,
            ]);

            return null;

        } catch (\Exception $e) {
            Log::error('Error finding matching student', [
                'azure_email' => $azureEmail,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Get user's Azure AD groups efficiently (1-2 API calls)
     * Only retrieves groups that matter for role assignment
     */
    private function getUserGroups($userId, $accessToken)
    {
        try {
            // Define groups we care about for role assignment
            $targetGroups = [
                'TOC-SIS Managers',
                'TOC-SIS Student Services',
                'TOC-SIS Teachers',
                'TOC-SIS Students',
                'Global Administrator',  // Fallback admin group
                'Leadership Team',        // Fallback admin group
            ];

            Log::info('Retrieving user groups for role assignment');

            // Get user's groups with optimized query (1 API call)
            $response = Http::withToken($accessToken)
                ->timeout(10)
                ->retry(2, 1000) // Retry twice with 1 second delay
                ->get('https://graph.microsoft.com/v1.0/me/memberOf', [
                    '$select' => 'displayName',  // Only get the name field
                    '$top' => 200,                // Get more groups in first call
                ]);

            if (! $response->successful()) {
                Log::warning('Failed to retrieve user groups', [
                    'status' => $response->status(),
                    'error' => $response->body(),
                ]);

                return [];
            }

            $data = $response->json();
            $userGroups = [];

            // Extract relevant groups from response
            if (isset($data['value'])) {
                foreach ($data['value'] as $group) {
                    if (isset($group['displayName'])) {
                        $groupName = $group['displayName'];

                        // Only keep groups that matter for role assignment
                        if (in_array($groupName, $targetGroups)) {
                            $userGroups[] = $groupName;
                            Log::info('Found target group', ['group' => $groupName]);
                        }
                    }
                }
            }

            // If pagination exists and we haven't found any target groups yet, check next page
            if (isset($data['@odata.nextLink']) && empty($userGroups)) {
                Log::info('Checking additional pages for target groups');

                $nextResponse = Http::withToken($accessToken)
                    ->timeout(10)
                    ->get($data['@odata.nextLink']);

                if ($nextResponse->successful()) {
                    $nextData = $nextResponse->json();
                    if (isset($nextData['value'])) {
                        foreach ($nextData['value'] as $group) {
                            if (isset($group['displayName']) && in_array($group['displayName'], $targetGroups)) {
                                $userGroups[] = $group['displayName'];
                                Log::info('Found target group on additional page', ['group' => $group['displayName']]);
                            }
                        }
                    }
                }
            }

            Log::info('User groups retrieved successfully', [
                'target_groups_found' => $userGroups,
                'count' => count($userGroups),
            ]);

            return $userGroups;

        } catch (\Exception $e) {
            Log::error('Exception while retrieving user groups', [
                'error' => $e->getMessage(),
                'user_id' => $userId,
            ]);

            return [];
        }
    }

    /**
     * Determine user role based on Azure AD group membership
     * Defaults to 'student' for security if no groups match
     */
    private function determineUserRole($groups)
    {
        // Define role mapping - groups to application roles
        $roleMapping = [
            // Primary TOC-SIS groups
            'TOC-SIS Managers' => 'manager',
            'TOC-SIS Student Services' => 'student_services',
            'TOC-SIS Teachers' => 'teacher',
            'TOC-SIS Students' => 'student',

            // Fallback groups for admin access
            'Global Administrator' => 'manager',
            'Leadership Team' => 'manager',
        ];

        // Role priority (highest to lowest privilege)
        $rolePriority = ['manager', 'student_services', 'teacher', 'student'];

        // Check for highest priority role first
        foreach ($rolePriority as $role) {
            // Find all groups that map to this role
            $groupsForRole = array_keys($roleMapping, $role);

            foreach ($groupsForRole as $groupName) {
                if (in_array($groupName, $groups)) {
                    Log::info('Role assigned based on group membership', [
                        'assigned_role' => $role,
                        'matching_group' => $groupName,
                    ]);

                    return $role;
                }
            }
        }

        // Security default: if no groups match, assign student role
        Log::warning('No matching groups found - defaulting to student role', [
            'user_groups' => $groups,
            'checked_groups' => array_keys($roleMapping),
        ]);

        return 'student';
    }

    /**
     * Log user out and invalidate session
     */
    public function logout(Request $request)
    {
        $user = Auth::user();

        if ($user) {
            Log::info('User logging out', [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('success', 'You have been logged out successfully.');
    }

    /**
     * Store Graph API tokens from Azure authentication
     */
    private function storeGraphTokens(User $user, $azureUser): void
    {
        try {
            // Check if we have the necessary token data
            if (!$azureUser->token || !property_exists($azureUser, 'refreshToken')) {
                Log::info('Azure user missing Graph API tokens', [
                    'user_id' => $user->id,
                    'has_token' => !empty($azureUser->token),
                    'has_refresh_token' => property_exists($azureUser, 'refreshToken'),
                ]);
                return;
            }

            $tokenService = app(GraphTokenService::class);

            // Prepare token data for storage
            $tokenData = [
                'access_token' => $azureUser->token,
                'refresh_token' => $azureUser->refreshToken ?? null,
                'expires_in' => $azureUser->expiresIn ?? 3600,
                'scope' => 'https://graph.microsoft.com/Mail.Read https://graph.microsoft.com/offline_access',
            ];

            $graphToken = $tokenService->storeTokens($user, $tokenData);

            Log::info('Graph API tokens stored successfully', [
                'user_id' => $user->id,
                'has_refresh_token' => !empty($graphToken->refresh_token),
                'expires_at' => $graphToken->expires_at,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to store Graph API tokens', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}
