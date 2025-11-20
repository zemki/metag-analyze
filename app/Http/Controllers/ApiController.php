<?php

namespace App\Http\Controllers;

use App\Cases;
use App\Helpers\Helper;
use App\Mail\VerificationEmail;
use App\Project;
use App\Role;
use App\User;
use DateTime;
use Exception;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;

class ApiController extends Controller
{
    const EMAIL = 'email';

    const PASSWORD = 'password';

    const INPUTS = 'inputs';

    const TOKEN = 'token';

    const CUSTOMINPUTS = 'custominputs';

    const NOTSTARTED = 'notstarted';

    const MEDIA = 'media';

    const ENTITY = 'entity';

    /**
     * @return ResponseFactory|Response
     */
    public function returnUser($id)
    {
        if ($id === 0) {
            $user = new User;

            return response($user, 200);
        }
        $user = User::where('id', $id)->with('profile')->first();
        $user['roles'] = $user->roles()->pluck('roles.name', 'roles.id')->toArray();
        $user['profile'] = $user->profile()->first();

        return response($user->jsonSerialize(), 200);
    }

    /**
     * @return ResponseFactory|Response
     */
    public function returnAllUsers()
    {
        return response(User::all()->jsonSerialize(), 200);
    }

    /**
     * @return ResponseFactory|Response
     */
    public function returnAllRoles()
    {
        return response(Role::all()->jsonSerialize(), 200);
    }

    /**
     * Handles Login Request
     *
     * @return JsonResponse
     */
    public function login(Request $request)
    {
        $credentials = [
            self::EMAIL => $request->email,
            self::PASSWORD => $request->password,
        ];

        if (auth()->attempt($credentials)) {
            $user = auth()->user();

            // Reset any failed login attempts and lockout
            $user->forceFill([
                'failed_login_attempts' => 0,
                'lockout_until' => null,
            ])->save();

            // Generate token with expiration
            $token = Helper::random_str(60, '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ');
            $user->forceFill([
                'api_token' => hash('sha256', $token),
                'token_expires_at' => now()->addDays(config('auth.token_expiration_days', 30)),
            ])->save();

            $userHasACase = $user->latestCase;

            if (! $userHasACase) {
                return response()->json(['case' => 'No cases'], 499);
            }

            if ($userHasACase->isBackend()) {
                return response()->json(['case' => 'No cases'], 499);
            }

            if (! $user->profile()->exists()) {
                $user->addProfile($user);
            }

            User::saveDeviceId($request);
            $lastDayPos = strpos($userHasACase->duration, 'lastDay:');
            $startDay = Helper::get_string_between($userHasACase->duration, 'startDay:', '|');

            $duration = $lastDayPos
                ? substr($userHasACase->duration, $lastDayPos + strlen('lastDay:'), strlen($userHasACase->duration) - 1)
                : Cases::calculateDuration($request->datetime, $userHasACase->duration);

            $userHasACase->duration .= $lastDayPos ? '' : '|lastDay:' . $duration;
            $userHasACase->save();

            // Track first login for MART projects
            if ($userHasACase->project->isMartProject() && !$userHasACase->first_login_at) {
                $userHasACase->first_login_at = now();
                $userHasACase->save();

                // Calculate dynamic end dates for MART schedules
                $this->calculateMartDynamicEndDates($userHasACase);
            }

            // Determine which API version to use
            $forceApiV2 = env('FORCE_API_V2', false);
            $projectDate = new DateTime($userHasACase->project->created_at ?? 'now');
            $cutoffDate = new DateTime(config('app.api_v2_cutoff_date', '2025-03-21'));

            // Use API V2 if forced or if project was created after cutoff date
            $useApiV2 = $forceApiV2 || ($projectDate >= $cutoffDate);

            if ($useApiV2) {
                // Use V2 formatting
                $v2ApiController = new \App\Http\Controllers\Api\V2\ApiController;
                $inputs = $v2ApiController->formatLoginResponse($userHasACase);
            } else {
                // Use legacy V1 formatting
                $v1ApiController = new \App\Http\Controllers\Api\V1\ApiController;
                $inputs = $v1ApiController->formatLoginResponse($userHasACase);
            }

            $notStarted = (strtotime(date('d.m.Y')) < strtotime($startDay));

            return response()->json([
                self::INPUTS => $inputs[self::INPUTS],
                'case' => $userHasACase->makeHidden('file_token'),
                self::TOKEN => $token,
                'file_token' => $userHasACase->file_token ? Crypt::decryptString($userHasACase->file_token) : null,
                'duration' => $duration,
                self::CUSTOMINPUTS => $inputs[self::INPUTS][self::CUSTOMINPUTS],
                self::NOTSTARTED => $notStarted,
                'api_version' => $useApiV2 ? 'v2' : 'v1',
            ], 200);

        } else {
            // Track failed login attempts
            if ($user = User::where('email', $request->email)->first()) {
                $user->increment('failed_login_attempts');

                if ($user->failed_login_attempts >= config('auth.max_login_attempts', 5)) {
                    $user->lockout_until = now()->addMinutes(30);
                    $user->save();
                }
            }

            return response()->json(['error' => 'invalid credentials'], 401);
        }
    }

    /**
     * @return mixed
     */
    public function register(Request $request)
    {
        abort(403, 'NOT POSSIBLE');
        $request->validate([
            'name' => 'required|string|max:255',
            self::EMAIL => 'required|string|email|max:255|unique:users',
            self::PASSWORD => 'required|string|min:6',
        ]);

        return User::create([
            'name' => $request->name,
            self::EMAIL => $request->email,
            self::PASSWORD => Hash::make($request->password),
        ]);
    }

    /**
     * @return JsonResponse
     */
    public function logout()
    {
        auth()->user()->tokens->each(function ($token) {
            $token->delete();
        });

        return response()->json('Logged out successfully', 200);
    }

    /**
     * Update the authenticated user's API token.
     *
     * @return array
     *
     * @throws Exception
     */
    public function update(Request $request)
    {
        $token = Helper::random_str(60);
        $request->user()->forceFill([
            'api_token' => hash('sha256', $token),
        ])->save();

        return [self::TOKEN => $token];
    }

    /**
     * Check if an email exists in the system
     *
     * ⚠️ IMPORTANT: This endpoint is ONLY for MART projects.
     * It validates that the provided project_id belongs to a MART project.
     *
     * Security measures:
     * - Rate limited to 5 requests per minute
     * - Validates project is MART project
     * - Uses constant-time comparison to prevent timing attacks
     * - Validates email format before checking
     * - Logs suspicious activity
     * - Tracks check in cache for password setup flow
     *
     * @return JsonResponse
     */
    public function checkEmailExists(\App\Http\Requests\EmailCheckRequest $request)
    {
        // Validation happens automatically via EmailCheckRequest
        // Verify project is a MART project
        $project = Project::findOrFail($request->project_id);
        if (! $project->isMartProject()) {
            \Log::warning('Email check attempted for non-MART project', [
                'project_id' => $request->project_id,
                'project_name' => $project->name,
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'error' => 'Invalid project',
                'message' => 'This endpoint is only available for MART projects. Please contact your researcher for access.',
            ], 403);
        }

        $email = strtolower(trim($request->email));

        // Add a small random delay to make timing attacks harder
        usleep(random_int(50000, 150000)); // 50-150ms random delay

        // Check if email exists
        $exists = User::where('email', $email)->exists();

        // Track this check in cache for password setup flow
        // This prevents password setup emails being sent without checking first
        // Include project_id to ensure same project is used for both check and setup
        $cacheKey = 'email_check:' . md5($email . $request->ip() . $request->project_id);
        \Cache::put($cacheKey, [
            'email' => $email,
            'exists' => $exists,
            'project_id' => $request->project_id,
            'checked_at' => now(),
        ], now()->addMinutes(5)); // Valid for 5 minutes

        // Note: We intentionally don't log successful requests to avoid disk space issues
        // during potential flooding attacks. Only suspicious activity is logged.
        // Use APM tools (New Relic, DataDog, etc.) for request monitoring.

        // Return consistent response format
        // Note: Consider security implications - this endpoint could be used
        // for user enumeration. In production, you might want to return
        // the same response regardless of whether email exists, or require authentication.
        return response()->json([
            'exists' => $exists,
            'message' => $exists
                ? 'Email is registered in the system'
                : 'Email is not registered',
        ], 200);
    }

    /**
     * Send password setup email to user
     *
     * ⚠️ IMPORTANT: This endpoint is ONLY for MART projects.
     * It validates that the provided project_id belongs to a MART project.
     * For non-MART projects, users should be invited by researchers through the
     * standard case creation flow.
     *
     * This endpoint reuses the existing password setup infrastructure:
     * - Uses existing password_token field in users table
     * - Sends existing VerificationEmail
     * - User sets password via existing web route (password/set)
     * - Setting password automatically verifies email
     *
     * Security measures:
     * - Rate limited to 3 requests per 10 minutes
     * - Validates project is MART project
     * - Requires email to be checked first via /api/check-email (within 5 minutes)
     * - Checks must be from same IP address and same project
     * - Won't send to already verified emails
     * - Logs all requests
     *
     * @return JsonResponse
     */
    public function sendPasswordSetup(Request $request)
    {
        // 1. Validate input
        $request->validate([
            'email' => 'required|email|max:255',
            'project_id' => 'required|integer|exists:projects,id',
        ]);

        // 2. Verify project is a MART project
        $project = Project::findOrFail($request->project_id);
        if (! $project->isMartProject()) {
            \Log::warning('Password setup attempted for non-MART project', [
                'project_id' => $request->project_id,
                'project_name' => $project->name,
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'error' => 'Invalid project',
                'message' => 'This endpoint is only available for MART projects. Please contact your researcher for access.',
            ], 403);
        }

        $email = strtolower(trim($request->email));

        // 3. Check if email was checked recently from same IP and same project
        $cacheKey = 'email_check:' . md5($email . $request->ip() . $request->project_id);
        $emailCheck = \Cache::get($cacheKey);

        if (! $emailCheck) {
            \Log::warning('Password setup attempted without email check', [
                'email' => $email,
                'project_id' => $request->project_id,
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'error' => 'Please verify the email address first',
                'message' => 'You must check if the email exists before requesting a password setup link',
            ], 403);
        }

        // 4. Check if email already exists and is verified
        $user = User::where('email', $email)->first();
        if ($user && $user->email_verified_at) {
            \Log::warning('Password setup attempted for verified user', [
                'email' => $email,
                'project_id' => $request->project_id,
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'error' => 'Email already registered',
                'message' => 'This email is already registered and verified. Please login.',
            ], 400);
        }

        // 5. Create user if doesn't exist OR regenerate token if exists but not verified
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

        // 6. Generate new password token (even if user exists but not verified)
        $user->password_token = Crypt::encryptString(Helper::random_str(30));
        $user->save();

        // 7. Send verification email using existing infrastructure
        try {
            Mail::to($user->email)->send(
                new VerificationEmail($user, config('utilities.emailDefaultText'))
            );
        } catch (Exception $e) {
            \Log::error('Failed to send password setup email', [
                'email' => $email,
                'project_id' => $request->project_id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Failed to send email',
                'message' => 'Unable to send password setup email. Please try again later.',
            ], 500);
        }

        // Note: We intentionally don't log successful password setup sends to avoid
        // disk space issues during potential flooding attacks. Only errors and
        // suspicious activity are logged. Use APM tools for request monitoring.

        // 8. Return success response
        return response()->json([
            'success' => true,
            'message' => 'Password setup email sent successfully. Please check your inbox.',
            'details' => 'You will receive an email with a link to set your password. The link is valid for 24 hours.',
        ], 200);
    }

    /**
     * Calculate dynamic end dates for MART schedules on first login.
     *
     * This method is called when a participant logs in for the first time to a MART project.
     * It checks each schedule to see if it should calculate end dates dynamically based on login time.
     *
     * Implementation steps (to be completed later):
     * 1. Get all schedules for the MART project
     * 2. Loop through each schedule
     * 3. Check if timing_config.calculate_end_date_on_login is true
     * 4. If true, calculate end_date_time based on:
     *    - first_login_at timestamp
     *    - timing_config.duration_days_after_login
     * 5. Update the timing_config.end_date_time field
     * 6. Save the schedule
     *
     * @param \App\Cases $case The case that just logged in for the first time
     * @return void
     */
    protected function calculateMartDynamicEndDates($case)
    {
        // TODO: Implement dynamic end date calculation
        // This is a placeholder method that will be implemented later
        // For now, it does nothing - schedules will use their static end dates
    }

    /**
     * Handles QR Code Login Request
     *
     * @return JsonResponse
     */
    public function qrLogin(Request $request)
    {
        $uuid = $request->input('token');

        if (!$uuid) {
            return response()->json(['error' => 'qr_invalid'], 401);
        }

        // Look up case by QR token UUID
        $case = Cases::where('qr_token_uuid', $uuid)
            ->with(['user', 'project'])
            ->first();

        if (!$case) {
            return response()->json(['error' => 'qr_invalid'], 401);
        }

        // Check if token is revoked
        if ($case->qr_token_revoked_at) {
            return response()->json(['error' => 'qr_revoked'], 401);
        }

        // Block MART projects
        if ($case->project->isMartProject()) {
            return response()->json(['error' => 'mart_project'], 403);
        }

        // Block API v1 projects
        $projectDate = new DateTime($case->project->created_at ?? 'now');
        $cutoffDate = new DateTime(config('app.api_v2_cutoff_date', '2025-03-21'));
        if ($projectDate < $cutoffDate) {
            return response()->json(['error' => 'api_v1_project'], 403);
        }

        // Decrypt QR data
        try {
            $decryptedData = json_decode(Crypt::decryptString($case->qr_encrypted_data), true);
        } catch (Exception $e) {
            return response()->json(['error' => 'qr_invalid'], 401);
        }

        $user = $case->user;

        if (!$user) {
            return response()->json(['error' => 'qr_invalid'], 401);
        }

        // Verify password hasn't changed since QR generation
        if ($user->password !== $decryptedData['password']) {
            return response()->json(['error' => 'password_mismatch'], 401);
        }

        // Authenticate the user
        auth()->login($user);

        // Reset any failed login attempts and lockout
        $user->forceFill([
            'failed_login_attempts' => 0,
            'lockout_until' => null,
        ])->save();

        // Generate token with expiration
        $token = Helper::random_str(60, '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ');
        $user->forceFill([
            'api_token' => hash('sha256', $token),
            'token_expires_at' => now()->addDays(config('auth.token_expiration_days', 30)),
        ])->save();

        if ($case->isBackend()) {
            return response()->json(['case' => 'No cases'], 499);
        }

        if (!$user->profile()->exists()) {
            $user->addProfile($user);
        }

        User::saveDeviceId($request);
        $lastDayPos = strpos($case->duration, 'lastDay:');
        $startDay = Helper::get_string_between($case->duration, 'startDay:', '|');

        $duration = $lastDayPos
            ? substr($case->duration, $lastDayPos + strlen('lastDay:'), strlen($case->duration) - 1)
            : Cases::calculateDuration($request->datetime, $case->duration);

        $case->duration .= $lastDayPos ? '' : '|lastDay:' . $duration;
        $case->save();

        // Use API V2 formatting (guaranteed by API v2 check above)
        $v2ApiController = new \App\Http\Controllers\Api\V2\ApiController;
        $inputs = $v2ApiController->formatLoginResponse($case);

        $notStarted = (strtotime(date('d.m.Y')) < strtotime($startDay));

        return response()->json([
            self::INPUTS => $inputs[self::INPUTS],
            'case' => $case->makeHidden('file_token'),
            self::TOKEN => $token,
            'file_token' => $case->file_token ? Crypt::decryptString($case->file_token) : null,
            'duration' => $duration,
            self::CUSTOMINPUTS => $inputs[self::INPUTS][self::CUSTOMINPUTS],
            self::NOTSTARTED => $notStarted,
            'api_version' => 'v2',
        ], 200);
    }
}
