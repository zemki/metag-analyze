<?php

namespace App\Http\Controllers;

use App\Cases;
use App\Helpers\Helper;
use App\Mail\VerificationEmail;
use App\Project;
use App\Role;
use App\Setting;
use App\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;

/**
 * MART Authentication Controller
 *
 * Handles 3-screen authentication flow for MART mobile apps:
 * - Screen 1: Email Check (+ optional password setup request)
 * - Screen 2: Password Check (returns tokens)
 * - Screen 3: Project Access Check (auto-creates case)
 *
 * Flow validation uses cache to ensure screens are completed in order.
 */
class MartAuthController extends Controller
{
    /**
     * Screen 1: Check if email exists
     *
     * @return JsonResponse
     */
    public function checkEmail(\App\Http\Requests\MartEmailCheckRequest $request)
    {
        // Validation happens automatically via MartEmailCheckRequest
        $email = strtolower(trim($request->email));

        // Add random delay to prevent timing attacks
        usleep(random_int(50000, 150000)); // 50-150ms

        // Check if email exists
        $exists = User::where('email', $email)->exists();

        // Store in cache for 1 minute (used by Screen 2)
        $cacheKey = 'email_check:'.md5($email.$request->ip());
        Cache::put($cacheKey, [
            'email' => $email,
            'checked_at' => now(),
        ], now()->addMinutes(1));

        // Return response matching frontend TypeScript type
        return response()->json([
            'email' => $email,
            'emailExists' => $exists,
        ], 200);
    }

    /**
     * Screen 1: Send password setup email (for new users)
     *
     * This is called when user clicks "Register" button after seeing emailExists: false
     *
     * @return JsonResponse
     */
    public function sendPasswordSetup(Request $request)
    {
        // 1. Validate input
        $request->validate([
            'email' => 'required|email|max:255',
        ]);

        $email = strtolower(trim($request->email));

        // 2. Check if email was checked recently (within 1 minute)
        $cacheKey = 'email_check:'.md5($email.$request->ip());
        $emailCheck = Cache::get($cacheKey);

        if (! $emailCheck) {
            \Log::warning('Password setup attempted without email check', [
                'email' => $email,
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'error' => 'Flow validation failed',
                'message' => 'Please check your email first',
                'step' => 'email_check_required',
            ], 403);
        }

        // 3. Check if email already exists and is verified
        $user = User::where('email', $email)->first();
        if ($user && $user->email_verified_at) {
            \Log::warning('Password setup attempted for verified user', [
                'email' => $email,
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'error' => 'Email already registered',
                'message' => 'This email is already registered and verified. Please login.',
            ], 400);
        }

        // 4. Create user if doesn't exist OR regenerate token if exists but not verified
        if (! $user) {
            $user = new User;
            $user->email = $email;
            $user->password = Crypt::encryptString(Helper::random_str(30));
            $user->save();

            // Assign user role
            $role = Role::where('name', '=', 'user')->first();
            $user->roles()->sync($role);

            $userStatus = 'new';
        } else {
            $userStatus = 'existing_unverified';
        }

        // 5. Generate new password token
        $user->password_token = Crypt::encryptString(Helper::random_str(30));
        $user->save();

        // 6. Send verification email
        try {
            Mail::to($user->email)->send(
                new VerificationEmail($user, config('utilities.emailDefaultText'))
            );
        } catch (Exception $e) {
            \Log::error('Failed to send password setup email', [
                'email' => $email,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Failed to send email',
                'message' => 'Unable to send password setup email. Please try again later.',
            ], 500);
        }

        // 7. Return success response
        return response()->json([
            'success' => true,
            'message' => 'Password setup email sent successfully. Please check your inbox.',
        ], 200);
    }

    /**
     * Screen 2: Check password and return authentication tokens
     *
     * Validates email was checked in Screen 1, then authenticates user.
     * Returns bearerToken and refreshToken on success.
     *
     * @return JsonResponse
     */
    public function checkPassword(Request $request)
    {
        // 1. Validate input
        $request->validate([
            'email' => 'required|email|max:255',
            'password' => 'required|string',
        ]);

        $email = strtolower(trim($request->email));

        // 2. Check if email was checked recently (within 1 minute)
        $emailCheckKey = 'email_check:'.md5($email.$request->ip());
        $emailCheck = Cache::get($emailCheckKey);

        if (! $emailCheck) {
            \Log::warning('Password check attempted without email check', [
                'email' => $email,
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'error' => 'Flow validation failed',
                'message' => 'Please check your email first',
                'step' => 'email_check_required',
            ], 403);
        }

        // 3. Attempt authentication
        $credentials = [
            'email' => $email,
            'password' => $request->password,
        ];

        if (! auth()->attempt($credentials)) {
            // Track failed login attempts
            if ($user = User::where('email', $email)->first()) {
                $user->increment('failed_login_attempts');

                $maxAttempts = Setting::get('mart_max_login_attempts', config('auth.max_login_attempts', 10));
                if ($user->failed_login_attempts >= $maxAttempts) {
                    $lockoutDuration = Setting::get('mart_lockout_duration', config('auth.lockout_duration', 30));
                    $user->lockout_until = now()->addMinutes($lockoutDuration);
                    $user->save();
                }
            }

            return response()->json([
                'error' => 'Authentication failed',
                'message' => 'Invalid email or password',
            ], 401);
        }

        // 4. Authentication successful - get user
        $user = auth()->user();

        // Reset failed login attempts
        $user->forceFill([
            'failed_login_attempts' => 0,
            'lockout_until' => null,
        ])->save();

        // 5. Generate access token (bearer token)
        $accessToken = Helper::random_str(60, '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ');
        $user->forceFill([
            'api_token' => hash('sha256', $accessToken),
            'token_expires_at' => now()->addDays(config('auth.token_expiration_days', 30)),
        ])->save();

        // 6. Generate refresh token
        $refreshToken = Helper::random_str(60, '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ');
        $user->forceFill([
            'refresh_token' => hash('sha256', $refreshToken),
            'refresh_token_expires_at' => now()->addDays(7), // 7 days for refresh token
        ])->save();

        // 7. Store password check in cache for 5 minutes (used by Screen 3)
        $passwordCheckKey = 'password_check:'.md5($email.$request->ip());
        Cache::put($passwordCheckKey, [
            'email' => $email,
            'user_id' => $user->id,
            'authenticated_at' => now(),
        ], now()->addMinutes(5));

        // 8. Return tokens matching frontend TypeScript type
        return response()->json([
            'email' => $email,
            'bearerToken' => $accessToken,
            'refreshToken' => $refreshToken,
        ], 200);
    }

    /**
     * Screen 3: Check project access and auto-create case
     *
     * Validates password was checked in Screen 2, verifies project is MART,
     * and auto-creates a case for the user in this project if needed.
     *
     * @return JsonResponse
     */
    public function checkAccess(Request $request)
    {
        // 1. Validate input
        $request->validate([
            'email' => 'required|email|max:255',
            'projectId' => 'required|integer|exists:projects,id',
        ]);

        $email = strtolower(trim($request->email));
        $projectId = $request->projectId;

        // 2. Check if password was checked recently (within 5 minutes)
        $passwordCheckKey = 'password_check:'.md5($email.$request->ip());
        $passwordCheck = Cache::get($passwordCheckKey);

        if (! $passwordCheck) {
            \Log::warning('Project access check attempted without password check', [
                'email' => $email,
                'project_id' => $projectId,
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'error' => 'Flow validation failed',
                'message' => 'Please login first',
                'step' => 'password_check_required',
            ], 403);
        }

        // 3. Get user
        $user = User::where('email', $email)->first();
        if (! $user) {
            return response()->json([
                'error' => 'User not found',
                'message' => 'User does not exist',
            ], 404);
        }

        // 4. Get project and verify it's a MART project
        $project = Project::findOrFail($projectId);
        if (! $project->isMartProject()) {
            \Log::warning('Project access check for non-MART project', [
                'email' => $email,
                'project_id' => $projectId,
                'project_name' => $project->name,
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'error' => 'Invalid project',
                'message' => 'This project ID is not a MART project',
            ], 403);
        }

        // 5. Check if user has a case in this project
        $case = Cases::where('user_id', $user->id)
            ->where('project_id', $projectId)
            ->first();

        // 6. Auto-create case if doesn't exist
        if (! $case) {
            $case = Cases::create([
                'name' => 'MART_'.uniqid(), // Participant ID
                'user_id' => $user->id,
                'project_id' => $projectId,
                'duration' => 'startDay:'.now()->format('d.m.Y').'|',
                'created_at' => now(),
            ]);

            \Log::info('Auto-created MART case', [
                'user_id' => $user->id,
                'project_id' => $projectId,
                'case_id' => $case->id,
                'participant_id' => $case->name,
            ]);
        }

        // 7. Set first_login_at and calculate dynamic dates if not already set
        if (! $case->first_login_at) {
            $case->first_login_at = now();
            $case->save();

            // Calculate dynamic start/end dates for questionnaires with "start on login"
            $this->calculateMartDynamicEndDates($case);
        }

        // 8. Return success response with participant info
        return response()->json([
            'projectId' => $projectId,
            'participantIsAllowed' => true,
            'participantId' => $case->name,
            'caseId' => $case->id,
        ], 200);
    }

    /**
     * Refresh access token using refresh token
     *
     * Implements token rotation: issues new access token AND new refresh token,
     * invalidating the old refresh token.
     *
     * @return JsonResponse
     */
    public function refreshToken(Request $request)
    {
        // 1. Validate input
        $request->validate([
            'refreshToken' => 'required|string',
        ]);

        $refreshToken = $request->refreshToken;
        $hashedRefreshToken = hash('sha256', $refreshToken);

        // 2. Find user with this refresh token
        $user = User::where('refresh_token', $hashedRefreshToken)->first();

        if (! $user) {
            return response()->json([
                'error' => 'Invalid refresh token',
                'message' => 'Refresh token not found or already used',
            ], 401);
        }

        // 3. Check if refresh token is expired
        if ($user->refresh_token_expires_at && $user->refresh_token_expires_at < now()) {
            // Clear expired token
            $user->forceFill([
                'refresh_token' => null,
                'refresh_token_expires_at' => null,
            ])->save();

            return response()->json([
                'error' => 'Refresh token expired',
                'message' => 'Please login again',
            ], 401);
        }

        // 4. Generate new access token
        $newAccessToken = Helper::random_str(60, '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ');
        $user->forceFill([
            'api_token' => hash('sha256', $newAccessToken),
            'token_expires_at' => now()->addDays(config('auth.token_expiration_days', 30)),
        ])->save();

        // 5. Generate new refresh token (token rotation)
        $newRefreshToken = Helper::random_str(60, '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ');
        $user->forceFill([
            'refresh_token' => hash('sha256', $newRefreshToken),
            'refresh_token_expires_at' => now()->addDays(7),
        ])->save();

        // 6. Return new tokens
        return response()->json([
            'bearerToken' => $newAccessToken,
            'refreshToken' => $newRefreshToken,
        ], 200);
    }

    /**
     * Calculate dynamic start/end dates for questionnaires with "start on login"
     *
     * Creates per-case schedule overrides in mart_case_schedules table.
     *
     * @param  \App\Cases  $case
     */
    protected function calculateMartDynamicEndDates($case)
    {
        $project = $case->project;
        $martProject = $project->martProject();

        if (! $martProject) {
            return;
        }

        $schedules = \App\Mart\MartSchedule::where('mart_project_id', $martProject->id)->get();

        foreach ($schedules as $schedule) {
            $timing = $schedule->timing_config ?? [];
            $overrides = [];

            // Calculate start date if start_on_first_login is true
            if ($timing['start_on_first_login'] ?? false) {
                $overrides['start_date_time'] = [
                    'date' => $case->first_login_at->format('Y-m-d'),
                    'time' => $timing['daily_start_time'] ?? '09:00',
                ];
            }

            // Calculate end date if use_dynamic_end_date is true
            if ($timing['use_dynamic_end_date'] ?? false) {
                // Use the override start date if set, otherwise use schedule's static start date
                $startDate = $overrides['start_date_time']['date']
                    ?? ($timing['start_date_time']['date'] ?? null);

                if ($startDate) {
                    $maxTotalSubmits = $timing['max_total_submits'] ?? 30;
                    $maxDailySubmits = $timing['max_daily_submits'] ?? 6;

                    $durationDays = (int) ceil($maxTotalSubmits / $maxDailySubmits);
                    $endDate = \Carbon\Carbon::parse($startDate)->addDays($durationDays);

                    $overrides['end_date_time'] = [
                        'date' => $endDate->format('Y-m-d'),
                        'time' => $timing['daily_end_time'] ?? '21:00',
                    ];
                }
            }

            // Store in MART database if there are any overrides
            if (! empty($overrides)) {
                \App\Mart\MartCaseSchedule::setForCase($case->id, $schedule->id, $overrides);
            }
        }
    }
}
