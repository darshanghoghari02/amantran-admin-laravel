<?php

namespace App\Services;

use App\Services\DbService;
use Illuminate\Support\Facades\Log;

/**
 * PermissionService — Port of the Node.js auth middleware permission logic.
 *
 * Handles role-based access control (RBAC) for admin users.
 */
class PermissionService
{
    /**
     * Default roles — mirrors Node.js DEFAULT_ROLES array.
     */
    public const DEFAULT_ROLES = [
        [
            'id'          => 'super_admin',
            'name'        => 'Super Administrator',
            'description' => 'Full unrestricted access to all system features',
            'permissions' => ['*'],
            'isDefault'   => true,
            'isActive'    => true,
        ],
        [
            'id'          => 'admin',
            'name'        => 'Administrator',
            'description' => 'Full access to templates, categories, users, analytics, and subscriptions',
            'permissions' => [
                'dashboard.view',
                'templates.view', 'templates.create', 'templates.edit', 'templates.delete',
                'categories.view', 'categories.create', 'categories.edit', 'categories.delete',
                'fonts.view', 'fonts.create', 'fonts.edit', 'fonts.delete',
                'languages.view', 'languages.create', 'languages.edit', 'languages.delete',
                'subscriptions.view', 'subscriptions.edit', 'subscriptions.manage_pricing',
                'subscriptions.activate', 'subscriptions.deactivate',
                'users.view', 'users.edit',
                'roles.view',
                'audit-logs.view',
                'settings.view',
            ],
            'isDefault'   => true,
            'isActive'    => true,
        ],
        [
            'id'          => 'editor',
            'name'        => 'Template Editor',
            'description' => 'Can create, edit, and manage templates and categories',
            'permissions' => [
                'dashboard.view',
                'templates.view', 'templates.create', 'templates.edit',
                'categories.view',
                'fonts.view', 'fonts.create',
                'languages.view',
            ],
            'isDefault'   => true,
            'isActive'    => true,
        ],
        [
            'id'          => 'viewer',
            'name'        => 'Read-Only Viewer',
            'description' => 'Can only view templates and categories',
            'permissions' => [
                'dashboard.view',
                'templates.view',
                'categories.view',
                'fonts.view',
                'languages.view',
            ],
            'isDefault'   => true,
            'isActive'    => true,
        ],
    ];

    private DbService $db;

    public function __construct(DbService $db)
    {
        $this->db = $db;
    }

    /**
     * Get the list of permissions for a given admin user ID.
     * Returns ['*'] for super_admin (wildcard — all permissions).
     */
    public function getUserPermissions(string $userId): array
    {
        // Special built-in super admin
        if ($userId === 'admin_super') {
            return ['*'];
        }

        try {
            $user = $this->db->getOne('users', $userId);
            if (!$user) return [];

            $roleId = $user['roleId'] ?? $user['role'] ?? 'viewer';

            // Super admin role = wildcard
            if ($roleId === 'super_admin') return ['*'];

            // Check for custom per-user permissions override
            if (!empty($user['isCustomPermissions']) && !empty($user['customPermissions'])) {
                return is_array($user['customPermissions']) ? $user['customPermissions'] : [];
            }

            // Resolve from role
            return $this->getRolePermissions($roleId);
        } catch (\Exception $e) {
            Log::error('PermissionService::getUserPermissions error', ['userId' => $userId, 'error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Get permissions for a role by ID.
     */
    public function getRolePermissions(string $roleId): array
    {
        // Check built-in defaults first
        foreach (self::DEFAULT_ROLES as $role) {
            if ($role['id'] === $roleId) {
                return $role['permissions'];
            }
        }

        // Look up in database for custom roles
        try {
            $role = $this->db->getOne('roles', $roleId);
            if ($role && isset($role['permissions'])) {
                return is_array($role['permissions']) ? $role['permissions'] : [];
            }
        } catch (\Exception $e) {
            // ignore
        }

        return [];
    }

    /**
     * Check if a user has a specific permission.
     */
    public function hasPermission(string $userId, string $permission): bool
    {
        $perms = $this->getUserPermissions($userId);

        if (in_array('*', $perms)) return true;
        return in_array($permission, $perms);
    }

    /**
     * Log an audit event to the audit_logs table.
     */
    public function logAuditEvent(string $userId, string $action, string $section = 'General'): void
    {
        try {
            $this->db->add('audit_logs', [
                'userId'    => $userId,
                'action'    => $action,
                'section'   => $section,
                'timestamp' => now()->toISOString(),
                'ip'        => request()->ip(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log audit event', ['error' => $e->getMessage()]);
        }
    }
}
