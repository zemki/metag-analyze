<?php

namespace App\Http\Controllers;

use App\Cases;
use App\Helpers\Helper;
use App\Project;
use App\QrLoginToken;
use App\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QrLoginController extends Controller
{
    const INPUTS = 'inputs';
    const TOKEN = 'token';
    const CUSTOMINPUTS = 'custominputs';
    const NOTSTARTED = 'notstarted';
    const MEDIA = 'media';
    const EMAIL = 'email';
    const PASSWORD = 'password';

    /**
     * Generate a QR code for a specific case
     *
     * @param Request $request
     * @param Cases $case
     * @return JsonResponse
     */
    public function generateQrForCase(Request $request, Cases $case)
    {
        // 1. Validate permission (user owns project or is invited)
        if (auth()->user()->notOwnerNorInvited($case->project)) {
            abort(403, 'Unauthorized');
        }

        // 2. Check active QR limit (max 5 per case)
        $activeCount = QrLoginToken::where('case_id', $case->id)
            ->active()
            ->notExpired()
            ->count();

        if ($activeCount >= 5) {
            return response()->json(['error' => 'Maximum 5 active QR codes per case'], 422);
        }

        // 3. Validate expiration input
        $request->validate([
            'expires_in_days' => 'nullable|integer|in:7,14,30,60,90',
            'notify_on_use' => 'boolean',
        ]);

        // 4. Calculate expiration
        $expiresAt = $request->expires_in_days
            ? now()->addDays($request->expires_in_days)
            : null;

        // 5. Check if case has temp_password, if not generate one
        if (! $case->temp_password) {
            $tempPassword = Helper::random_str(30);
            $case->temp_password = Crypt::encryptString($tempPassword);
            $case->save();
        } else {
            $tempPassword = Crypt::decryptString($case->temp_password);
        }

        // 6. Create credential payload
        $credentials = [
            self::EMAIL => $case->user->email,
            self::PASSWORD => $tempPassword,
            'case_id' => $case->id,
            'expires_at' => $expiresAt ? $expiresAt->timestamp : null,
        ];

        // 7. Encrypt credentials
        $encryptedToken = Crypt::encryptString(json_encode($credentials));

        // 8. Create QR token record
        $qrToken = QrLoginToken::create([
            'case_id' => $case->id,
            'encrypted_credential' => $encryptedToken,
            'expires_at' => $expiresAt,
            'notify_on_use' => $request->notify_on_use ?? false,
            'created_by' => auth()->id(),
        ]);

        // 9. Generate deep link URL
        $deepLinkUrl = config('app.url').'/qr-login?token='.urlencode($encryptedToken);

        // 10. Generate QR code image (base64 PNG)
        $qrImage = base64_encode(QrCode::format('png')
            ->size(300)
            ->margin(2)
            ->generate($deepLinkUrl));

        // 11. Return response
        return response()->json([
            'success' => true,
            'qr_token_id' => $qrToken->id,
            'qr_url' => $deepLinkUrl,
            'qr_image' => 'data:image/png;base64,'.$qrImage,
            'expires_at' => $expiresAt?->toIso8601String(),
            'usage_count' => 0,
        ]);
    }

    /**
     * Handle QR redirect from browser (web route - browser fallback)
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function handleQrRedirect(Request $request)
    {
        $token = $request->query('token');

        if (! $token) {
            abort(400, 'Missing token');
        }

        return view('qr-redirect', [
            'token' => $token,
            'deep_link' => 'metagapp://login?token='.urlencode($token),
            'app_store_url' => 'https://apps.apple.com/app/metag', // Update with real URL
            'play_store_url' => 'https://play.google.com/store/apps/details?id=de.zemki.metagcompose',
        ]);
    }

    /**
     * Debug QR token structure (temporary - remove in production)
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function debugQrToken(Request $request)
    {
        $request->validate(['token' => 'required|string']);

        try {
            // Decrypt token
            $credentialsJson = Crypt::decryptString($request->token);
            $credentials = json_decode($credentialsJson, true);

            if (!$credentials) {
                return response()->json([
                    'error' => 'Invalid token format',
                    'token_received' => substr($request->token, 0, 50) . '...',
                    'decrypted' => null,
                ], 400);
            }

            // Find QR token record
            $qrToken = QrLoginToken::where('encrypted_credential', $request->token)
                ->with('case.user')
                ->first();

            return response()->json([
                'status' => 'Token valid',
                'credentials' => [
                    'email' => $credentials['email'] ?? null,
                    'has_password' => isset($credentials['password']),
                    'case_id' => $credentials['case_id'] ?? null,
                    'expires_at' => $credentials['expires_at'] ?? null,
                ],
                'qr_token' => $qrToken ? [
                    'id' => $qrToken->id,
                    'case_id' => $qrToken->case_id,
                    'case_name' => $qrToken->case->name ?? null,
                    'user_email' => $qrToken->case->user->email ?? null,
                    'is_active' => $qrToken->is_active,
                    'is_expired' => $qrToken->isExpired(),
                    'expires_at' => $qrToken->expires_at?->toIso8601String(),
                ] : null,
                'message' => 'Token is valid. Use POST /api/qr-login to authenticate.',
            ], 200);

        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            return response()->json([
                'error' => 'Decryption failed',
                'message' => 'Token is invalid or tampered',
                'token_received' => substr($request->token, 0, 50) . '...',
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Unexpected error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Login with QR token (API endpoint)
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function loginWithQr(Request $request)
    {
        // 1. Validate request
        $request->validate([self::TOKEN => 'required|string']);

        try {
            // 2. Decrypt token
            $credentialsJson = Crypt::decryptString($request->token);
            $credentials = json_decode($credentialsJson, true);

            if (! $credentials) {
                return response()->json(['error' => 'Invalid token format'], 401);
            }

            // 3. Find QR token record
            $qrToken = QrLoginToken::where('encrypted_credential', $request->token)
                ->where('is_active', true)
                ->first();

            if (! $qrToken) {
                return response()->json(['error' => 'QR code not found or revoked'], 401);
            }

            // 4. Check expiration
            if ($qrToken->isExpired()) {
                return response()->json(['error' => 'QR code expired'], 401);
            }

            // 5. Get case and verify temp_password
            $case = Cases::with('user')->find($qrToken->case_id);

            if (!$case) {
                return response()->json(['error' => 'Case not found'], 401);
            }

            // Decrypt the case's temp_password
            $caseTempPassword = Crypt::decryptString($case->temp_password);

            // Verify the password from the QR token matches the case's temp_password
            if ($caseTempPassword !== $credentials[self::PASSWORD]) {
                return response()->json(['error' => 'Authentication failed'], 401);
            }

            // Manually authenticate the user (bypass password check)
            $user = $case->user;
            auth()->login($user);

            // 6. Reset failed login attempts
            $user->forceFill([
                'failed_login_attempts' => 0,
                'lockout_until' => null,
            ])->save();

            // 7. Generate API token (same as regular login)
            $apiToken = Helper::random_str(60, '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ');
            $user->forceFill([
                'api_token' => hash('sha256', $apiToken),
                'token_expires_at' => now()->addDays(config('auth.token_expiration_days', 30)),
            ])->save();

            // 8. Get user's case
            $userHasACase = $user->latestCase;

            if (! $userHasACase || $userHasACase->isBackend()) {
                return response()->json(['case' => 'No cases'], 499);
            }

            // 9. Ensure user has profile
            if (! $user->profile()->exists()) {
                $user->addProfile($user);
            }

            // 10. Update QR token usage
            $qrToken->incrementUsage();

            // 11. Send notification if enabled
            if ($qrToken->notify_on_use && $qrToken->creator) {
                // TODO: Send notification to researcher
                // $qrToken->creator->notify(new QrCodeUsedNotification($qrToken, $user));
            }

            // 12. Save device ID if provided
            User::saveDeviceId($request);

            // 13. Calculate duration (same as regular login)
            $lastDayPos = strpos($userHasACase->duration, 'lastDay:');
            $startDay = Helper::get_string_between($userHasACase->duration, 'startDay:', '|');

            $duration = $lastDayPos
                ? substr($userHasACase->duration, $lastDayPos + strlen('lastDay:'), strlen($userHasACase->duration) - 1)
                : Cases::calculateDuration($request->datetime ?? time(), $userHasACase->duration);

            $userHasACase->duration .= $lastDayPos ? '' : '|lastDay:'.$duration;
            $userHasACase->save();

            // 14. Format response (same as regular login)
            $inputs = $this->formatLoginResponse($userHasACase);
            $notStarted = (strtotime(date('d.m.Y')) < strtotime($startDay));

            return response()->json([
                self::INPUTS => $inputs[self::INPUTS],
                'case' => $userHasACase->makeHidden('file_token'),
                self::TOKEN => $apiToken,
                'file_token' => $userHasACase->file_token ? Crypt::decryptString($userHasACase->file_token) : null,
                'duration' => $duration,
                self::CUSTOMINPUTS => $inputs[self::INPUTS][self::CUSTOMINPUTS],
                self::NOTSTARTED => $notStarted,
            ], 200);
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            return response()->json(['error' => 'Invalid or tampered token'], 401);
        }
    }

    /**
     * List QR tokens for a case
     *
     * @param Cases $case
     * @return JsonResponse
     */
    public function listQrTokens(Cases $case)
    {
        // Validate permission
        if (auth()->user()->notOwnerNorInvited($case->project)) {
            abort(403, 'Unauthorized');
        }

        $tokens = QrLoginToken::where('case_id', $case->id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($token) {
                return [
                    'id' => $token->id,
                    'created_at' => $token->created_at->toIso8601String(),
                    'expires_at' => $token->expires_at?->toIso8601String(),
                    'last_used_at' => $token->last_used_at?->toIso8601String(),
                    'usage_count' => $token->usage_count,
                    'is_active' => $token->is_active,
                    'is_expired' => $token->isExpired(),
                    'notify_on_use' => $token->notify_on_use,
                    'qr_url' => config('app.url').'/qr-login?token='.urlencode($token->encrypted_credential),
                ];
            });

        return response()->json(['qr_tokens' => $tokens]);
    }

    /**
     * Revoke a QR token
     *
     * @param QrLoginToken $token
     * @return JsonResponse
     */
    public function revokeToken(QrLoginToken $token)
    {
        // Validate permission
        if (auth()->user()->notOwnerNorInvited($token->case->project)) {
            abort(403, 'Unauthorized');
        }

        $token->revoke();

        return response()->json(['success' => true, 'message' => 'QR code revoked']);
    }

    /**
     * List all QR tokens for all cases in a project
     *
     * @param Project $project
     * @return JsonResponse
     */
    public function listProjectQrTokens(Project $project)
    {
        // Validate permission
        if (auth()->user()->notOwnerNorInvited($project)) {
            abort(403, 'Unauthorized');
        }

        // Get all case IDs for this project
        $caseIds = $project->cases()->pluck('id');

        // Get all QR tokens for these cases with case and user info
        $tokens = QrLoginToken::whereIn('case_id', $caseIds)
            ->with(['case.user'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($token) {
                $qrImage = base64_encode(QrCode::format('png')
                    ->size(300)
                    ->margin(2)
                    ->generate(config('app.url').'/qr-login?token='.urlencode($token->encrypted_credential)));

                return [
                    'id' => $token->id,
                    'case_id' => $token->case_id,
                    'case_name' => $token->case->name,
                    'user_email' => $token->case->user->email,
                    'created_at' => $token->created_at->toIso8601String(),
                    'expires_at' => $token->expires_at?->toIso8601String(),
                    'last_used_at' => $token->last_used_at?->toIso8601String(),
                    'usage_count' => $token->usage_count,
                    'is_active' => $token->is_active,
                    'is_expired' => $token->isExpired(),
                    'notify_on_use' => $token->notify_on_use,
                    'qr_url' => config('app.url').'/qr-login?token='.urlencode($token->encrypted_credential),
                    'qr_image' => 'data:image/png;base64,'.$qrImage,
                ];
            });

        return response()->json([
            'qr_tokens' => $tokens,
            'total_count' => $tokens->count(),
            'active_count' => $tokens->where('is_active', true)->where('is_expired', false)->count(),
        ]);
    }

    /**
     * Format login response (same as ApiController)
     *
     * @param Cases $response
     * @return array
     */
    protected function formatLoginResponse($response)
    {
        $data[self::INPUTS][self::MEDIA] = $response->project->media;
        $nullItem = (object) ['id' => 0, 'name' => ''];
        $data[self::INPUTS][self::MEDIA]->prepend($nullItem);
        $data[self::INPUTS][self::CUSTOMINPUTS] = $response->project->inputs;

        return $data;
    }
}
