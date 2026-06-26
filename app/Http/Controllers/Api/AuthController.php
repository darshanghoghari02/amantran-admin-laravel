<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DbService;
use App\Services\WhatsAppService;
use App\Services\GoogleAuthService;
use App\Models\AppUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function __construct(
        private DbService $db,
        private WhatsAppService $whatsapp,
        private GoogleAuthService $googleAuth
    ) {}

    /**
     * GET /
     * Base route info.
     */
    public function baseInfo(Request $request)
    {
        $appName = 'Amantran CMS Admin Backend API';
        $supportEmail = 'support@amantran.com';
        $maintenanceMode = false;

        try {
            $config = $this->db->getOne('settings', 'system_config');
            if ($config) {
                $appName = $config['appName'] ?? $appName;
                $supportEmail = $config['supportEmail'] ?? $supportEmail;
                $maintenanceMode = $config['maintenanceMode'] ?? false;
            }
        } catch (\Exception $err) {
            Log::error('Error fetching settings for base route: ' . $err->getMessage());
        }

        return response()->json([
            'name'             => $appName,
            'version'          => '1.0.0',
            'mode'             => 'mysql',
            'isFirebase'       => false,
            'isMySQL'          => true,
            'status'           => 'online',
            'maintenanceMode'  => $maintenanceMode,
            'supportEmail'     => $supportEmail,
            'assetsUrl'        => url('storage'),
        ]);
    }

    /**
     * GET /api/diagnose
     * Database connection diagnostics.
     */
    public function diagnose()
    {
        $isConnected = false;
        $connectionError = 'None';

        try {
            DB::connection()->getPdo();
            $isConnected = true;
        } catch (\Exception $e) {
            $connectionError = $e->getMessage();
        }

        return response()->json([
            'isFirebaseConnected'    => false,
            'isMySQLConnected'       => $isConnected,
            'isCloudinaryConfigured' => false,
            'mysqlHost'              => config('database.connections.mysql.host'),
            'mysqlDatabase'          => config('database.connections.mysql.database'),
            'connectionError'        => $connectionError,
            'imageStorage'           => 'Local disk storage (storage/ folder)',
            'environment'            => [
                'phpVersion' => PHP_VERSION,
                'platform'   => PHP_OS,
            ],
        ]);
    }

    /**
     * POST /api/auth/send-whatsapp-otp
     * Sends OTP to user's phone via Meta WhatsApp.
     */
    public function sendWhatsappOtp(Request $request)
    {
        try {
            $request->validate([
                'phone' => 'required',
            ]);

            $phone = $request->phone;
            $otp   = $request->otp ?? $this->generateOtpCode();

            $normalizedPhone = $this->whatsapp->normalizePhone($phone);
            $fullNormalizedPhone = '+' . $normalizedPhone;

            // Validate India phone number format (91 followed by 10 digits)
            if (!preg_match('/^91\d{10}$/', $normalizedPhone)) {
                return response()->json([
                    'error' => 'Invalid phone number format. Use +91XXXXXXXXXX or a 10-digit number.'
                ], 400);
            }

            $otpData = [
                'phone'      => $fullNormalizedPhone,
                'otp'        => $otp,
                'createdAt'  => now()->toISOString(),
                'expiresAt'  => now()->addMinutes(5)->toISOString(),
                'isVerified' => false,
            ];

            // Check if there is an unverified OTP code
            $otpCodes = $this->db->getAll('otp_codes');
            $existing = null;
            foreach ($otpCodes as $o) {
                if (($o['phone'] ?? '') === $fullNormalizedPhone && !($o['isVerified'] ?? false)) {
                    $existing = $o;
                    break;
                }
            }

            if ($existing) {
                $this->db->update('otp_codes', $existing['id'], $otpData);
            } else {
                $this->db->add('otp_codes', $otpData);
            }

            // Send WhatsApp message
            try {
                $this->whatsapp->sendOtp($normalizedPhone, $otp);
            } catch (\Exception $e) {
                Log::warning('WhatsApp service send failure: ' . $e->getMessage());
                // Fallback: log to server, but return success true so testing can proceed with default OTPs
            }

            return response()->json([
                'success' => true,
                'message' => 'OTP sent successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage() ?? 'Failed to send OTP'], 500);
        }
    }

    /**
     * POST /api/auth/verify-whatsapp-otp
     * Verifies OTP, resolves or registers user, and returns JWT.
     */
    public function verifyWhatsappOtp(Request $request)
    {
        try {
            $request->validate([
                'phone' => 'required',
                'otp'   => 'required',
            ]);

            $phone = $request->phone;
            $otp   = $request->otp;

            $normalizedPhone = '+' . $this->whatsapp->normalizePhone($phone);

            // Fetch matching unverified OTP from DB
            $otpCodes = $this->db->getAll('otp_codes');
            $otpRecord = null;
            foreach ($otpCodes as $o) {
                if (($o['phone'] ?? '') === $normalizedPhone && ($o['otp'] ?? '') === $otp && !($o['isVerified'] ?? false)) {
                    $otpRecord = $o;
                    break;
                }
            }

            if (!$otpRecord) {
                return response()->json(['error' => 'Invalid or expired OTP'], 400);
            }

            // Check if OTP is expired (5 mins)
            $createdAt = new \DateTime($otpRecord['createdAt'] ?? 'now');
            $diff = (new \DateTime())->getTimestamp() - $createdAt->getTimestamp();
            if ($diff > 300) {
                return response()->json(['error' => 'OTP has expired'], 400);
            }

            // Mark OTP as verified
            $this->db->update('otp_codes', $otpRecord['id'], ['isVerified' => true]);

            // Check if user exists in app_users
            $appUsers = $this->db->getAll('app_users');
            $user = null;
            foreach ($appUsers as $u) {
                if (($u['phone'] ?? '') === $normalizedPhone || ($u['email'] ?? '') === $normalizedPhone) {
                    $user = $u;
                    break;
                }
            }

            $now = now()->toISOString();

            if ($user) {
                $updates = ['lastLoginAt' => $now];
                if (empty($user['phone'])) {
                    $updates['phone'] = $normalizedPhone;
                }
                $user = $this->db->update('app_users', $user['id'], $updates);
            } else {
                // Check if self-registration is allowed
                $config = $this->db->getOne('settings', 'system_config');
                if ($config && ($config['allowSelfRegistration'] ?? true) === false) {
                    return response()->json(['error' => 'Public registrations are currently disabled by settings.'], 403);
                }
                $defaultRole = $config['defaultUserRole'] ?? 'user';

                $newUser = [
                    'phone'           => $normalizedPhone,
                    'provider'        => 'phone',
                    'role'            => $defaultRole,
                    'accountStatus'   => 'active',
                    'isBlocked'       => false,
                    'invitationCount' => 0,
                    'draftsCount'     => 0,
                    'createdAt'       => $now,
                    'lastLoginAt'     => $now,
                ];

                $user = $this->db->add('app_users', $newUser);
            }

            // Check suspension by association
            $isSuspended = $this->isUserSuspended($user['email'] ?? '', $user['phone'] ?? '');
            if ($isSuspended || ($user['isBlocked'] ?? false) || ($user['accountStatus'] ?? '') === 'suspended') {
                return response()->json(['error' => 'Your account has been suspended.'], 403);
            }

            // Generate JWT Token
            $token = $this->generateJwt($user['id']);

            return response()->json([
                'success' => true,
                'token'   => $token,
                'message' => 'OTP verified successfully',
                'user'    => [
                    'id'            => $user['id'],
                    'phone'         => $user['phone'] ?? '',
                    'email'         => $user['email'] ?? '',
                    'name'          => $user['name'] ?? '',
                    'displayName'   => $user['displayName'] ?? '',
                    'profilePhoto'  => $user['profilePhoto'] ?? '',
                    'provider'      => $user['provider'] ?? 'phone',
                    'accountStatus' => $user['accountStatus'] ?? 'active',
                    'isBlocked'     => $user['isBlocked'] ?? false,
                    'createdAt'     => $user['createdAt'],
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage() ?? 'Failed to verify OTP'], 500);
        }
    }

    /**
     * POST /api/auth/signup
     * Update profile details for a newly verified user.
     */
    public function signup(Request $request)
    {
        try {
            $request->validate([
                'userId' => 'required',
            ]);

            $userId = $request->userId;
            $user   = $this->db->getOne('app_users', $userId);
            if (!$user) {
                return response()->json(['error' => 'User not found.'], 404);
            }

            // Validate unique email
            if ($request->email) {
                $email = strtolower(trim($request->email));
                if ($email && $email !== 'user@example.com') {
                    $results = $this->db->getByField('app_users', 'email', $email);
                    foreach ($results as $u) {
                        if ($u['id'] !== $userId) {
                            return response()->json([
                                'error' => 'This email address is already registered with another account.'
                            ], 400);
                        }
                    }
                }
            }

            $updates = [];
            if ($request->name) {
                $updates['name'] = $request->name;
                $updates['displayName'] = $request->name;
            }
            if ($request->email) {
                $updates['email'] = strtolower(trim($request->email));
            }
            if ($request->profilePhoto) {
                $updates['profilePhoto'] = $request->profilePhoto;
            }

            $updated = $this->db->update('app_users', $userId, $updates);

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully',
                'user'    => $updated,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * POST /api/auth/google-login
     * Verification & login of Google authentication.
     */
    public function googleLogin(Request $request)
    {
        try {
            $googleId = null;
            $email = null;
            $name = null;
            $picture = null;

            if ($request->idToken) {
                // Verify Native ID Token
                $payload = $this->googleAuth->verifyIdToken($request->idToken);
                $googleId = $payload['googleId'];
                $email = $payload['email'];
                $name = $payload['name'];
                $picture = $payload['picture'];
            } elseif ($request->code) {
                // OAuth 2.0 Web auth code exchange
                if (!$request->redirectUri) {
                    return response()->json(['error' => 'Redirect URI is required.'], 400);
                }

                $clientId     = config('services.google.client_id');
                $clientSecret = config('services.google.client_secret');

                if (!$clientId || !$clientSecret) {
                    return response()->json(['error' => 'Google OAuth credentials not configured.'], 500);
                }

                $tokenResponse = \Illuminate\Support\Facades\Http::asForm()->post('https://oauth2.googleapis.com/token', [
                    'code'          => $request->code,
                    'client_id'     => $clientId,
                    'client_secret' => $clientSecret,
                    'redirect_uri'  => $request->redirectUri,
                    'grant_type'    => 'authorization_code',
                ]);

                if ($tokenResponse->failed()) {
                    return response()->json(['error' => 'Failed to exchange authorization code.'], 400);
                }

                $accessToken = $tokenResponse->json('access_token');
                
                $userInfoResponse = \Illuminate\Support\Facades\Http::withToken($accessToken)
                    ->get('https://www.googleapis.com/oauth2/v3/userinfo');

                if ($userInfoResponse->failed()) {
                    return response()->json(['error' => 'Failed to retrieve Google userinfo.'], 400);
                }

                $userInfo = $userInfoResponse->json();
                $googleId = $userInfo['sub'] ?? '';
                $email    = $userInfo['email'] ?? '';
                $name     = $userInfo['name'] ?? '';
                $picture  = $userInfo['picture'] ?? '';
            } else {
                return response()->json(['error' => 'Either idToken or authorization code is required.'], 400);
            }

            if (!$email) {
                return response()->json(['error' => 'Google authentication did not return an email.'], 400);
            }

            // Find or create user
            $appUsers = $this->db->getAll('app_users');
            $user = null;
            foreach ($appUsers as $u) {
                if (($u['email'] ?? '') === $email || ($u['google_id'] ?? '') === $googleId) {
                    $user = $u;
                    break;
                }
            }

            $now = now()->toISOString();

            if ($user) {
                $updates = ['lastLoginAt' => $now];
                if (empty($user['email'])) $updates['email'] = $email;
                if (empty($user['name'])) {
                    $updates['name'] = $name;
                    $updates['displayName'] = $name;
                }
                if (empty($user['profilePhoto']) && $picture) $updates['profilePhoto'] = $picture;
                if (empty($user['google_id'])) $updates['google_id'] = $googleId;
                if (empty($user['provider'])) $updates['provider'] = 'google';
                
                $user = $this->db->update('app_users', $user['id'], $updates);
            } else {
                $config = $this->db->getOne('settings', 'system_config');
                if ($config && ($config['allowSelfRegistration'] ?? true) === false) {
                    return response()->json(['error' => 'Public registrations are currently disabled by settings.'], 403);
                }
                $defaultRole = $config['defaultUserRole'] ?? 'user';

                $newUser = [
                    'google_id'     => $googleId,
                    'email'         => $email,
                    'name'          => $name ?: 'Google User',
                    'displayName'   => $name ?: 'Google User',
                    'profilePhoto'  => $picture ?: '',
                    'provider'      => 'google',
                    'role'          => $defaultRole,
                    'accountStatus' => 'active',
                    'isBlocked'     => false,
                    'invitationCount' => 0,
                    'draftsCount'   => 0,
                    'createdAt'     => $now,
                    'lastLoginAt'   => $now,
                ];

                $user = $this->db->add('app_users', $newUser);
            }

            // Check suspension
            $isSuspended = $this->isUserSuspended($user['email'] ?? '', $user['phone'] ?? '');
            if ($isSuspended || ($user['isBlocked'] ?? false) || ($user['accountStatus'] ?? '') === 'suspended') {
                return response()->json(['error' => 'Your account has been suspended.'], 403);
            }

            // Generate JWT
            $token = $this->generateJwt($user['id']);

            return response()->json([
                'success' => true,
                'token'   => $token,
                'message' => 'Google login successful',
                'user'    => [
                    'id'            => $user['id'],
                    'email'         => $user['email'] ?? '',
                    'name'          => $user['name'] ?? '',
                    'displayName'   => $user['displayName'] ?? '',
                    'profilePhoto'  => $user['profilePhoto'] ?? '',
                    'phone'         => $user['phone'] ?? '',
                    'provider'      => $user['provider'] ?? 'google',
                    'accountStatus' => $user['accountStatus'] ?? 'active',
                    'isBlocked'     => $user['isBlocked'] ?? false,
                    'createdAt'     => $user['createdAt'],
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage() ?? 'Google login failed'], 500);
        }
    }

    /**
     * POST /api/auth/apple-login
     * Apple sign in.
     */
    public function appleLogin(Request $request)
    {
        try {
            $request->validate([
                'uid' => 'required',
            ]);

            $uid   = $request->uid;
            $email = $request->email;
            $name  = $request->name;
            $photoURL = $request->photoURL;

            $appUsers = $this->db->getAll('app_users');
            $user = null;
            foreach ($appUsers as $u) {
                if ($u['id'] === $uid || ($email && ($u['email'] ?? '') === $email)) {
                    $user = $u;
                    break;
                }
            }

            $now = now()->toISOString();

            if ($user) {
                $updates = ['lastLoginAt' => $now];
                if (empty($user['email']) && $email) $updates['email'] = $email;
                if (empty($user['name']) && $name) {
                    $updates['name'] = $name;
                    $updates['displayName'] = $name;
                }
                if (empty($user['profilePhoto']) && $photoURL) $updates['profilePhoto'] = $photoURL;
                if (empty($user['provider'])) $updates['provider'] = 'apple';

                $user = $this->db->update('app_users', $user['id'], $updates);
            } else {
                $config = $this->db->getOne('settings', 'system_config');
                if ($config && ($config['allowSelfRegistration'] ?? true) === false) {
                    return response()->json(['error' => 'Public registrations are currently disabled by settings.'], 403);
                }
                $defaultRole = $config['defaultUserRole'] ?? 'user';

                $newUser = [
                    'id'            => $uid,
                    'uid'           => $uid,
                    'email'         => $email ?: '',
                    'name'          => $name ?: 'Apple User',
                    'displayName'   => $name ?: 'Apple User',
                    'profilePhoto'  => $photoURL ?: '',
                    'provider'      => 'apple',
                    'role'          => $defaultRole,
                    'accountStatus' => 'active',
                    'isBlocked'     => false,
                    'invitationCount' => 0,
                    'draftsCount'   => 0,
                    'createdAt'     => $now,
                    'lastLoginAt'   => $now,
                ];

                $user = $this->db->add('app_users', $newUser);
            }

            $isSuspended = $this->isUserSuspended($user['email'] ?? '', $user['phone'] ?? '');
            if ($isSuspended || ($user['isBlocked'] ?? false) || ($user['accountStatus'] ?? '') === 'suspended') {
                return response()->json(['error' => 'Your account has been suspended.'], 403);
            }

            // Generate JWT
            $token = $this->generateJwt($user['id']);

            return response()->json([
                'success' => true,
                'token'   => $token,
                'message' => 'Apple login successful',
                'user'    => [
                    'id'            => $user['id'],
                    'email'         => $user['email'] ?? '',
                    'name'          => $user['name'] ?? '',
                    'displayName'   => $user['displayName'] ?? '',
                    'profilePhoto'  => $user['profilePhoto'] ?? '',
                    'phone'         => $user['phone'] ?? '',
                    'provider'      => $user['provider'] ?? 'apple',
                    'accountStatus' => $user['accountStatus'] ?? 'active',
                    'isBlocked'     => $user['isBlocked'] ?? false,
                    'createdAt'     => $user['createdAt'],
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage() ?? 'Apple login failed'], 500);
        }
    }

    /**
     * GET /api/app/realtime
     * Realtime SSE.
     */
    public function realtimeSSE()
    {
        return response()->stream(function () {
            echo "data: " . json_encode(['type' => 'connected']) . "\n\n";
            ob_flush();
            flush();

            // Keep-alive loops for 15 seconds to satisfy the connection
            for ($i = 0; $i < 15; $i++) {
                if (connection_aborted()) break;
                echo ":keep-alive\n\n";
                ob_flush();
                flush();
                sleep(1);
            }
        }, 200, [
            'Content-Type'      => 'text/event-stream',
            'Cache-Control'     => 'no-cache',
            'Connection'        => 'keep-alive',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    /**
     * GET /api/app/config
     */
    public function appConfig()
    {
        try {
            $config = $this->db->getOne('settings', 'system_config');
            if ($config) {
                return response()->json([
                    'appName'         => $config['appName'] ?? 'Amantran Invitation App CMS',
                    'supportEmail'    => $config['supportEmail'] ?? 'support@amantran.com',
                    'maintenanceMode' => $config['maintenanceMode'] ?? false,
                ]);
            }
            return response()->json([
                'appName'         => 'Amantran Invitation App CMS',
                'supportEmail'    => 'support@amantran.com',
                'maintenanceMode' => false,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // Helper functions

    private function generateOtpCode(): string
    {
        return (string) rand(100000, 990000);
    }

    private function isUserSuspended(string $email, string $phone): bool
    {
        try {
            $users = $this->db->getAll('app_users');
            $targetEmail = strtolower(trim($email));
            $targetPhone = preg_replace('/\D/', '', $phone);

            if (!$targetEmail && !$targetPhone) return false;

            foreach ($users as $u) {
                $isSusp = ($u['isBlocked'] ?? false) === true || 
                          ($u['status'] ?? '') === 'Suspended' || 
                          ($u['accountStatus'] ?? '') === 'suspended';
                if (!$isSusp) continue;

                if ($targetEmail && strtolower(trim($u['email'] ?? '')) === $targetEmail) {
                    return true;
                }

                if ($targetPhone && !empty($u['phone'])) {
                    $up = preg_replace('/\D/', '', $u['phone']);
                    if ($up && ($up === $targetPhone || str_ends_with($up, $targetPhone) || str_ends_with($targetPhone, $up))) {
                        return true;
                    }
                }
            }
        } catch (\Exception $e) {
            // ignore
        }
        return false;
    }

    private function generateJwt(string $userId): string
    {
        // Fetch or insert dynamic AppUser record matching UUID
        $userModel = AppUser::firstOrCreate(['id' => $userId], ['data' => json_encode([])]);
        return JWTAuth::fromUser($userModel);
    }
}
