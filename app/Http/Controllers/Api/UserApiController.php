<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DbService;
use App\Services\PermissionService;
use Illuminate\Http\Request;
use App\Helpers\HashHelper;
use Illuminate\Support\Facades\Log;

class UserApiController extends Controller
{
    public function __construct(
        private DbService $db,
        private PermissionService $permissions
    ) {}

    /**
     * POST /api/users/login
     * Handle admin AJAX login (Next.js dashboard).
     */
    public function adminLogin(Request $request)
    {
        try {
            $request->validate([
                'email'    => 'required|email',
                'password' => 'required',
            ]);

            $email    = strtolower(trim($request->email));
            $password = $request->password;

            $user = null;

            $users = $this->db->getAll('users');
            foreach ($users as $u) {
                if (isset($u['email']) && strtolower(trim($u['email'])) === $email) {
                    if (HashHelper::check($password, $u['password'] ?? '')) {
                        $user = $u;
                    }
                    break;
                }
            }

            if (!$user) {
                return response()->json(['error' => 'Authentication failed. Incorrect email or password.'], 401);
            }

            if (!empty($user['isBlocked']) || (isset($user['status']) && strtolower($user['status']) === 'suspended')) {
                return response()->json(['error' => 'Your administrator account has been suspended.'], 403);
            }

            // Resolve permissions
            $user['permissions'] = $this->permissions->getUserPermissions($user['id']);

            $this->permissions->logAuditEvent($user['id'], 'Admin logged in (API)', 'Authentication');

            return response()->json($user);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /api/users
     * List admin users.
     */
    public function indexAdmin(Request $request)
    {
        try {
            $list = $this->db->getAll('users');
            $query = $request->query('query');
            $role  = $request->query('role');

            $filtered = $list;

            if ($query) {
                $q = strtolower($query);
                $filtered = array_filter($filtered, function ($u) use ($q) {
                    return (str_contains(strtolower($u['displayName'] ?? ''), $q)) ||
                           (str_contains(strtolower($u['email'] ?? ''), $q));
                });
            }

            if ($role) {
                $filtered = array_filter($filtered, function ($u) use ($role) {
                    return ($u['role'] ?? '') === $role || ($u['roleId'] ?? '') === $role;
                });
            }

            $resolved = [];
            foreach ($filtered as $u) {
                $u['permissions'] = $this->permissions->getUserPermissions($u['id']);
                $u['createdAt']   = $u['createdAt'] ?? now()->toISOString();
                $resolved[] = $u;
            }

            // Sort by creation date descending
            usort($resolved, function ($a, $b) {
                return strcmp($b['createdAt'] ?? '', $a['createdAt'] ?? '');
            });

            return response()->json(array_values($resolved));
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * POST /api/users
     * Create admin user.
     */
    public function storeAdmin(Request $request)
    {
        try {
            $request->validate([
                'email'    => 'required|email',
                'password' => 'required',
            ]);

            $adminUserId = $request->header('x-user-id') ?? session('admin_user.id') ?? 'system';
            $email = strtolower(trim($request->email));

            // Validate uniqueness
            $existingUsers = $this->db->getAll('users');
            foreach ($existingUsers as $u) {
                if (strtolower(trim($u['email'] ?? '')) === $email) {
                    return response()->json(['error' => 'An account with this email address already exists.'], 400);
                }
            }

            $userData = [
                'email'             => $email,
                'name'              => $request->name ?? $request->displayName ?? 'Admin User',
                'displayName'       => $request->displayName ?? $request->name ?? 'Admin User',
                'roleId'            => $request->roleId ?? $request->role ?? 'viewer',
                'role'              => $request->role ?? $request->roleId ?? 'viewer',
                'password'          => HashHelper::make($request->password),
                'permissions'       => $request->permissions ?? [],
                'customPermissions' => $request->customPermissions ?? [],
                'isCustomPermissions' => $request->isCustomPermissions === true,
                'phoneNumber'       => $request->phoneNumber ?? '',
                'status'            => $request->status ?? 'Active',
                'isBlocked'         => ($request->status ?? '') === 'Suspended',
            ];

            $newUser = $this->db->add('users', $userData);
            unset($newUser['password']); // don't return hashed password

            $this->permissions->logAuditEvent($adminUserId, "Created administrator account: {$newUser['email']}", 'Users');

            return response()->json($newUser, 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /api/users/{id}
     * Show single admin user.
     */
    public function showAdmin(Request $request, $id)
    {
        try {
            $adminUserId = $request->header('x-user-id') ?? session('admin_user.id') ?? 'system';
            
            if ($adminUserId !== $id) {
                // Ensure caller is admin/superadmin
                $perms = $this->permissions->getUserPermissions($adminUserId);
                if (!in_array('*', $perms) && !in_array('users.view', $perms)) {
                    return response()->json(['error' => 'Forbidden. You do not have users.view permission.'], 403);
                }
            }

            $user = $this->db->getOne('users', $id);
            if (!$user) {
                return response()->json(['error' => 'User profile not found'], 404);
            }

            $user['permissions'] = $this->permissions->getUserPermissions($id);
            unset($user['password']);

            return response()->json($user);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * PUT /api/users/{id}
     * Update admin user.
     */
    public function updateAdmin(Request $request, $id)
    {
        try {
            $adminUserId = $request->header('x-user-id') ?? session('admin_user.id') ?? 'system';
            $user = $this->db->getOne('users', $id);
            if (!$user) {
                return response()->json(['error' => 'User not found.'], 404);
            }

            $updates = $request->all();

            // Verify permissions for editing other admins
            if ($adminUserId !== $id) {
                $perms = $this->permissions->getUserPermissions($adminUserId);
                if (!in_array('*', $perms) && !in_array('users.edit', $perms)) {
                    return response()->json(['error' => 'Forbidden. You do not have users.edit permission.'], 403);
                }
            }

            if ($request->has('password') && !empty($request->password)) {
                $updates['password'] = HashHelper::make($request->password);
            } else {
                unset($updates['password']);
            }

            if ($request->has('email')) {
                $updates['email'] = strtolower(trim($request->email));
            }

            if ($request->has('status')) {
                $updates['isBlocked'] = $request->status === 'Suspended';
            }

            $updatedUser = $this->db->update('users', $id, $updates);
            unset($updatedUser['password']);

            $this->permissions->logAuditEvent($adminUserId, "Updated administrator account details: {$updatedUser['email']}", 'Users');

            return response()->json($updatedUser);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * DELETE /api/users/{id}
     * Delete admin user.
     */
    public function destroyAdmin(Request $request, $id)
    {
        try {
            $adminUserId = $request->header('x-user-id') ?? session('admin_user.id') ?? 'system';
            
            if ($id === 'admin_super' || $id === $adminUserId) {
                return response()->json(['error' => 'Cannot delete system super administrator or your own account.'], 400);
            }

            $user = $this->db->getOne('users', $id);
            if (!$user) {
                return response()->json(['error' => 'User not found.'], 404);
            }

            $this->db->delete('users', $id);

            $this->permissions->logAuditEvent($adminUserId, "Deleted administrator account: {$user['email']}", 'Users');

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /* ═══════════════════════════════════════════════════════════
       MOBILE APP USERS MANAGEMENT (ADMIN DASHBOARD ENDPOINTS)
    * ═══════════════════════════════════════════════════════════ */

    /**
     * GET /api/users/app-users
     * Returns list of all app mobile users joined with ratings & subscriptions.
     */
    public function indexAppUsers(Request $request)
    {
        try {
            $list = $this->db->getAll('app_users');
            $query = $request->query('query');

            $ratings = [];
            $userSubscriptions = [];
            $drafts = [];
            $cards = [];

            try {
                $ratings = $this->db->getAll('ratings');
            } catch (\Exception $err) {
                Log::warning('Error fetching ratings: ' . $err->getMessage());
            }
            try {
                $userSubscriptions = $this->db->getAll('user_subscriptions');
            } catch (\Exception $err) {
                Log::warning('Error fetching user subscriptions: ' . $err->getMessage());
            }
            try {
                $drafts = $this->db->getAll('user_drafts');
            } catch (\Exception $err) {
                Log::warning('Error fetching user drafts: ' . $err->getMessage());
            }
            try {
                $cards = $this->db->getAll('user_cards');
            } catch (\Exception $err) {
                Log::warning('Error fetching user cards: ' . $err->getMessage());
            }

            $normalized = [];
            foreach ($list as $u) {
                // Find latest rating
                $userRatings = array_filter($ratings, fn($r) => ($r['userId'] ?? '') === $u['id']);
                usort($userRatings, fn($a, $b) => strcmp($b['createdAt'] ?? '', $a['createdAt'] ?? ''));
                $latestRating = !empty($userRatings) ? (float) ($userRatings[0]['rating'] ?? null) : null;

                // Find active subscription
                $userSub = null;
                foreach ($userSubscriptions as $s) {
                    if (($s['userId'] ?? '') === $u['id'] || $s['id'] === $u['id']) {
                        $userSub = $s;
                        break;
                    }
                }

                $subscription = $userSub ? [
                    'id'                 => $userSub['id'],
                    'userId'             => $userSub['userId'] ?? $userSub['id'],
                    'planType'           => $userSub['planType'] ?? $userSub['type'] ?? 'monthly',
                    'type'               => $userSub['planType'] ?? $userSub['type'] ?? 'monthly',
                    'isActive'           => ($userSub['isActive'] ?? true) !== false,
                    'startDate'          => $userSub['startDate'] ?? null,
                    'expiryDate'         => $userSub['expiryDate'] ?? null,
                    'amountPaid'         => (float) ($userSub['amountPaid'] ?? 0),
                    'purchasedTemplates' => $userSub['purchasedTemplates'] ?? [],
                    'updatedAt'          => $userSub['updatedAt'] ?? null,
                ] : null;

                // Calculate drafts and cards count dynamically
                $userDraftsCount = count(array_filter($drafts, fn($d) => ($d['userId'] ?? '') === $u['id']));
                $userCardsCount = count(array_filter($cards, fn($c) => ($c['userId'] ?? '') === $u['id']));

                $normalized[] = [
                    'id'              => $u['id'],
                    'displayName'     => $u['displayName'] ?? $u['name'] ?? 'Anonymous User',
                    'name'            => $u['name'] ?? $u['displayName'] ?? '',
                    'email'           => $u['email'] ?? '',
                    'phone'           => $u['phone'] ?? '',
                    'provider'        => $u['provider'] ?? 'phone',
                    'profilePhoto'    => $u['profilePhoto'] ?? '',
                    'accountStatus'   => $u['accountStatus'] ?? 'active',
                    'isBlocked'       => ($u['isBlocked'] ?? false) === true || ($u['accountStatus'] ?? '') === 'suspended',
                    'invitationCount' => $userCardsCount,
                    'draftsCount'     => $userDraftsCount,
                    'createdAt'       => $u['createdAt'] ?? now()->toISOString(),
                    'lastLoginAt'     => $u['lastLoginAt'] ?? null,
                    'rating'          => $latestRating,
                    'subscription'    => $subscription,
                ];
            }

            if ($query) {
                $q = strtolower($query);
                $normalized = array_filter($normalized, function ($u) use ($q) {
                    return (str_contains(strtolower($u['displayName'] ?? ''), $q)) ||
                           (str_contains(strtolower($u['email'] ?? ''), $q)) ||
                           (str_contains(strtolower($u['phone'] ?? ''), $q)) ||
                           (str_contains(strtolower($u['provider'] ?? ''), $q));
                });
            }

            // Sort by creation date descending
            usort($normalized, function ($a, $b) {
                return strcmp($b['createdAt'] ?? '', $a['createdAt'] ?? '');
            });

            return response()->json(array_values($normalized));
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * PUT /api/users/app-users/{id}
     * Block/unblock/update a mobile app user.
     */
    public function updateAppUser(Request $request, $id)
    {
        try {
            $adminUserId = $request->header('x-user-id') ?? session('admin_user.id');
            if (!$adminUserId) {
                return response()->json(['error' => 'Missing x-user-id header.'], 401);
            }

            $adminPerms = $this->permissions->getUserPermissions($adminUserId);
            $isSuperAdmin = in_array('*', $adminPerms);

            $userToEdit = $this->db->getOne('app_users', $id);
            if (!$userToEdit) {
                return response()->json(['error' => 'App user not found.'], 404);
            }

            // Validate suspension/activation permission
            $isBlocked = $request->isBlocked;
            $isStatusChange = $isBlocked !== null;

            if ($isStatusChange) {
                $requiredPerm = $isBlocked ? 'users.suspend' : 'users.activate';
                if (!$isSuperAdmin && !in_array($requiredPerm, $adminPerms)) {
                    return response()->json(['error' => "Forbidden. You do not have permission: {$requiredPerm}"], 403);
                }
            } else {
                if (!$isSuperAdmin && !in_array('users.edit', $adminPerms)) {
                    return response()->json(['error' => 'Forbidden. You do not have users.edit permission.'], 403);
                }
            }

            $updates = [];
            if ($request->has('isBlocked')) {
                $updates['isBlocked'] = (bool) $request->isBlocked;
                $updates['status'] = $request->isBlocked ? 'Suspended' : 'Active';
                $updates['accountStatus'] = $request->isBlocked ? 'suspended' : 'active';
            }
            if ($request->has('displayName')) {
                $updates['displayName'] = $request->displayName;
                $updates['name'] = $request->displayName;
            }
            if ($request->has('email')) $updates['email'] = $request->email;
            if ($request->has('phone')) $updates['phone'] = $request->phone;

            $updated = $this->db->update('app_users', $id, $updates);

            $nameStr = $updated['displayName'] ?? $updated['phone'] ?? $updated['email'] ?? $id;

            if ($isStatusChange) {
                $actionStr = $updated['isBlocked'] ? 'suspended' : 'activated';
                $this->permissions->logAuditEvent($adminUserId, strtoupper($actionStr) . " app user: {$nameStr}", 'Users');
            } else {
                $this->permissions->logAuditEvent($adminUserId, "Updated app user details for: {$nameStr}", 'Users');
            }

            return response()->json($updated);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * DELETE /api/users/app-users/{id}
     * Delete a mobile app user.
     */
    public function destroyAppUser(Request $request, $id)
    {
        try {
            $adminUserId = $request->header('x-user-id') ?? session('admin_user.id') ?? 'system';
            $userToDelete = $this->db->getOne('app_users', $id);
            $nameStr = $userToDelete ? ($userToDelete['displayName'] ?? $userToDelete['phone'] ?? $userToDelete['email'] ?? 'Unknown') : $id;

            $this->db->delete('app_users', $id);

            $this->permissions->logAuditEvent($adminUserId, "Deleted app user: {$nameStr}", 'Users');

            return response()->json(['success' => true, 'message' => 'App user deleted successfully.']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /* ═══════════════════════════════════════════════════════════
       FLUTTER APP ENDPOINTS (JWT-AUTHENTICATED)
    * ═══════════════════════════════════════════════════════════ */

    /**
     * GET /api/app/users/resolve/find
     */
    public function resolveUser(Request $request)
    {
        try {
            $email = $request->query('email');
            $phone = $request->query('phone');

            if (!$email && !$phone) {
                return response()->json(['error' => 'email or phone is required'], 400);
            }

            $matched = null;

            if ($email) {
                $targetEmail = strtolower(trim($email));
                $results = $this->db->getByField('app_users', 'email', $targetEmail);
                if (!empty($results)) {
                    $matched = $results[0];
                }
            }

            if (!$matched && $phone) {
                $targetPhone = preg_replace('/\D/', '', $phone);
                $list = $this->db->getAll('app_users');
                foreach ($list as $u) {
                    if (empty($u['phone'])) continue;
                    $p = preg_replace('/\D/', '', $u['phone']);
                    if ($p === $targetPhone || str_ends_with($p, $targetPhone) || str_ends_with($targetPhone, $p)) {
                        $matched = $u;
                        break;
                    }
                }
            }

            if ($matched) {
                return response()->json($matched);
            }

            return response()->json(['error' => 'User not found'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /api/app/users/{uid}
     */
    public function showAppUser($uid)
    {
        try {
            $user = $this->db->getOne('app_users', $uid);
            if (!$user) {
                return response()->json(['error' => 'User not found'], 404);
            }
            return response()->json($user);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * POST /api/app/users
     * Create or update a mobile app user's core document.
     */
    public function saveAppUser(Request $request)
    {
        try {
            $uid = $request->uid;
            if (!$uid) {
                return response()->json(['error' => 'uid is required'], 400);
            }

            $existing = $this->db->getOne('app_users', $uid);
            $now = now()->toISOString();

            // Validate email uniqueness
            if ($request->email) {
                $email = strtolower(trim($request->email));
                if ($email && $email !== 'user@example.com') {
                    $results = $this->db->getByField('app_users', 'email', $email);
                    foreach ($results as $u) {
                        if ($u['id'] !== $uid) {
                            return response()->json(['error' => 'This email address is already registered with another account.'], 400);
                        }
                    }
                }
            }

            // Validate phone uniqueness
            if ($request->phone) {
                $phone = preg_replace('/\D/', '', $request->phone);
                if ($phone) {
                    $allUsers = $this->db->getAll('app_users');
                    foreach ($allUsers as $u) {
                        if ($u['id'] === $uid || empty($u['phone'])) continue;
                        $p = preg_replace('/\D/', '', $u['phone']);
                        if ($p === $phone || str_ends_with($p, $phone) || str_ends_with($phone, $p)) {
                            return response()->json(['error' => 'This phone number is already registered with another account.'], 400);
                        }
                    }
                }
            }

            $userData = [
                'id'            => $uid,
                'uid'           => $uid,
                'name'          => $request->name ?? ($existing['name'] ?? 'New User'),
                'email'         => $request->email ?? ($existing['email'] ?? 'user@example.com'),
                'phone'         => $request->has('phone') ? $request->phone : ($existing['phone'] ?? ''),
                'profilePhoto'  => $request->has('profilePhoto') ? $request->profilePhoto : ($existing['profilePhoto'] ?? ''),
                'provider'      => $request->provider ?? ($existing['provider'] ?? 'google'),
                'role'          => $request->role ?? ($existing['role'] ?? 'user'),
                'accountStatus' => $request->accountStatus ?? ($existing['accountStatus'] ?? 'active'),
                'isBlocked'     => ($request->accountStatus ?? '') === 'suspended' ? true : ($existing['isBlocked'] ?? false),
                'lastLoginAt'   => $now,
                'updatedAt'     => $now,
            ];

            if ($existing) {
                $updated = $this->db->update('app_users', $uid, $userData);
                return response()->json($updated);
            } else {
                $config = $this->db->getOne('settings', 'system_config');
                if ($config && ($config['allowSelfRegistration'] ?? true) === false) {
                    return response()->json(['error' => 'Public registrations are currently disabled by settings.'], 403);
                }
                $userData['role'] = $userData['role'] ?? ($config['defaultUserRole'] ?? 'user');
                $userData['createdAt'] = $now;
                $created = $this->db->add('app_users', $userData);
                return response()->json($created, 201);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /api/app/users/{uid}/profile
     */
    public function getAppUserProfile($uid)
    {
        try {
            $user = $this->db->getOne('app_users', $uid);
            if (!$user) return response()->json(['error' => 'User not found'], 404);
            return response()->json($user['profile'] ?? (object) []);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * POST /api/app/users/{uid}/profile
     */
    public function saveAppUserProfile(Request $request, $uid)
    {
        try {
            $user = $this->db->getOne('app_users', $uid);
            if (!$user) return response()->json(['error' => 'User not found'], 404);

            $profile = $request->all();

            // Validate email uniqueness
            if (!empty($profile['email'])) {
                $email = strtolower(trim($profile['email']));
                if ($email && $email !== 'user@example.com') {
                    $results = $this->db->getByField('app_users', 'email', $email);
                    foreach ($results as $u) {
                        if ($u['id'] !== $uid) {
                            return response()->json(['error' => 'This email address is already registered with another account.'], 400);
                        }
                    }
                }
            }

            // Validate phone uniqueness
            if (!empty($profile['phone'])) {
                $phone = preg_replace('/\D/', '', $profile['phone']);
                if ($phone) {
                    $allUsers = $this->db->getAll('app_users');
                    foreach ($allUsers as $u) {
                        if ($u['id'] === $uid || empty($u['phone'])) continue;
                        $p = preg_replace('/\D/', '', $u['phone']);
                        if ($p === $phone || str_ends_with($p, $phone) || str_ends_with($phone, $p)) {
                            return response()->json(['error' => 'This phone number is already registered with another account.'], 400);
                        }
                    }
                }
            }

            $currentProfile = $user['profile'] ?? [];
            $merged = array_merge($currentProfile, $profile);

            $updated = $this->db->update('app_users', $uid, ['profile' => $merged]);
            return response()->json($updated['profile'] ?? (object) []);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /api/app/users/{uid}/settings
     */
    public function getAppUserSettings($uid)
    {
        try {
            $user = $this->db->getOne('app_users', $uid);
            if (!$user) return response()->json(['error' => 'User not found'], 404);
            return response()->json($user['settings'] ?? (object) []);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * POST /api/app/users/{uid}/settings
     */
    public function saveAppUserSettings(Request $request, $uid)
    {
        try {
            $user = $this->db->getOne('app_users', $uid);
            if (!$user) return response()->json(['error' => 'User not found'], 404);

            $settings = $request->all();
            $currentSettings = $user['settings'] ?? [];
            $merged = array_merge($currentSettings, $settings);

            $updated = $this->db->update('app_users', $uid, ['settings' => $merged]);
            return response()->json($updated['settings'] ?? (object) []);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /* Ratings */

    public function getUserRating($userId)
    {
        try {
            $rating = $this->db->getOne('ratings', $userId);
            if (!$rating) return response()->json(['error' => 'Rating not found'], 404);
            return response()->json($rating);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function saveUserRating(Request $request)
    {
        try {
            $request->validate([
                'userId' => 'required',
                'rating' => 'required',
            ]);

            $userId = $request->userId;
            $ratingData = [
                'id'        => $userId,
                'userId'    => $userId,
                'rating'    => (float) $request->rating,
                'userName'  => $request->userName ?? '',
                'userEmail' => $request->userEmail ?? '',
                'userPhone' => $request->userPhone ?? '',
                'updatedAt' => now()->toISOString(),
            ];

            $existing = $this->db->getOne('ratings', $userId);
            if ($existing) {
                $updated = $this->db->update('ratings', $userId, $ratingData);
                return response()->json($updated);
            } else {
                $created = $this->db->add('ratings', $ratingData);
                return response()->json($created, 201);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /* System settings for admin */

    public function getSystemConfig()
    {
        try {
            $config = $this->db->getOne('settings', 'system_config');
            if (!$config) {
                return response()->json([
                    'id'                    => 'system_config',
                    'appName'               => 'Amantran Invitation App CMS',
                    'supportEmail'          => 'support@amantran.com',
                    'maintenanceMode'       => false,
                    'defaultUserRole'       => 'user',
                    'allowSelfRegistration' => true,
                ]);
            }
            return response()->json($config);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function saveSystemConfig(Request $request)
    {
        try {
            $userId = $request->header('x-user-id') ?? session('admin_user.id') ?? 'system';
            
            $config = $this->db->getOne('settings', 'system_config');
            $updates = $request->all();
            
            if ($config) {
                $updated = $this->db->update('settings', 'system_config', $updates);
            } else {
                $updates['id'] = 'system_config';
                $updated = $this->db->add('settings', $updates);
            }

            $this->permissions->logAuditEvent($userId, 'Updated system settings configurations', 'Settings');

            return response()->json($updated);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
