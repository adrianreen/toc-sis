<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class AzureController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('azure')->stateless()->redirect();
    }

    public function callback(Request $request)
    {
        try {
            $token = Socialite::driver('azure')->stateless()->user();

            Log::info('Azure user retrieved', ['email' => $token->email]);
            Log::debug('Raw ID token claims', $token->user);

            // Extract groups from ID token claims
            $claims = $token->user;
            $groups = $claims['groups'] ?? [];

            Log::info('Groups from ID token', ['groups' => $groups]);

            $user = User::updateOrCreate(
                ['azure_id' => $token->id],
                [
                    'name' => $token->name,
                    'email' => $token->email,
                    'azure_groups' => $groups,
                    'last_login_at' => now(),
                    'password' => bcrypt(Str::random(32)),
                ]
            );

            $user->role = $this->determineRole($groups);
            $user->save();

            Log::debug('Matching env to groups', [
                'env_value' => trim(env('AZURE_GROUP_MANAGERS')),
                'in_array' => in_array(trim(env('AZURE_GROUP_MANAGERS')), $groups),
                'all_groups' => $groups,
            ]);

            Auth::login($user);

            Log::info('User logged in and redirected', [
                'email' => $user->email,
                'role' => $user->role
            ]);

            return redirect()->route('dashboard');

        } catch (\Exception $e) {
            Log::error('Azure authentication failed', [
                'error' => $e->getMessage()
            ]);
            return redirect('/')->with('error', 'Authentication failed: ' . $e->getMessage());
        }
    }

    private function determineRole(array $groups)
    {
        $roleMapping = [
            'manager' => trim(env('AZURE_GROUP_MANAGERS')),
            'student_services' => trim(env('AZURE_GROUP_STUDENT_SERVICES')),
            'teacher' => trim(env('AZURE_GROUP_TEACHERS')),
        ];

        foreach ($roleMapping as $role => $groupId) {
            if ($groupId && in_array($groupId, $groups)) {
                Log::info('Role matched', ['role' => $role, 'group' => $groupId]);
                return $role;
            }
        }

        Log::info('No group match found, defaulting to student');
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
