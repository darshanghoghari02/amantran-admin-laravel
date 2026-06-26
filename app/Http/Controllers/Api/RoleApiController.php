<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DbService;
use App\Services\PermissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RoleApiController extends Controller
{
    public function __construct(
        private DbService $db,
        private PermissionService $permissions
    ) {}

    /**
     * GET /api/roles
     */
    public function index()
    {
        try {
            $list = $this->db->getAll('roles');

            // Seed default roles if empty
            if (empty($list)) {
                $list = [];
                $now = now()->toISOString();
                foreach (PermissionService::DEFAULT_ROLES as $role) {
                    $role['createdAt'] = $now;
                    $seeded = $this->db->add('roles', $role);
                    $list[] = $seeded;
                }
            }

            return response()->json($list);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /api/roles/{id}
     */
    public function show($id)
    {
        try {
            $role = $this->db->getOne('roles', $id);
            if (!$role) {
                return response()->json(['error' => 'Role not found.'], 404);
            }
            return response()->json($role);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * POST /api/roles
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required',
            ]);

            $userId = $request->header('x-user-id') ?? session('admin_user.id') ?? 'system';

            $permissions = $request->permissions;
            $finalPermissions = is_array($permissions) ? $permissions : [];

            if ($request->cloneRoleId) {
                $sourceRole = $this->db->getOne('roles', $request->cloneRoleId);
                if ($sourceRole) {
                    $finalPermissions = $sourceRole['permissions'] ?? [];
                }
            }

            $id = 'role_' . strtolower(Str::random(9));
            $roleData = [
                'id'          => $id,
                'name'        => $request->name,
                'description' => $request->description ?? '',
                'permissions' => $finalPermissions,
                'isDefault'   => false,
                'isActive'    => $request->isActive !== false,
            ];

            $newRole = $this->db->add('roles', $roleData);

            $this->permissions->logAuditEvent($userId, "Created role: {$newRole['name']}", 'Roles');

            return response()->json($newRole, 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * PUT /api/roles/{id}
     */
    public function update(Request $request, $id)
    {
        try {
            $userId = $request->header('x-user-id') ?? session('admin_user.id') ?? 'system';
            $role = $this->db->getOne('roles', $id);
            if (!$role) {
                return response()->json(['error' => 'Role not found.'], 404);
            }

            if ($role['id'] === 'super_admin') {
                return response()->json(['error' => 'Super Admin role permissions are read-only and system-protected.'], 400);
            }

            $updates = [];
            if ($request->has('permissions')) {
                $updates['permissions'] = is_array($request->permissions) ? $request->permissions : [];
            }
            if ($request->has('description')) $updates['description'] = $request->description;
            if ($request->has('isActive')) $updates['isActive'] = $request->isActive === true;

            // Only allow updating name if it is a custom role
            if (empty($role['isDefault']) && $request->has('name')) {
                $updates['name'] = $request->name;
            }

            $updatedRole = $this->db->update('roles', $id, $updates);

            $this->permissions->logAuditEvent($userId, "Updated role: {$updatedRole['name']}", 'Roles');

            return response()->json($updatedRole);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * DELETE /api/roles/{id}
     */
    public function destroy(Request $request, $id)
    {
        try {
            $userId = $request->header('x-user-id') ?? session('admin_user.id') ?? 'system';
            $role = $this->db->getOne('roles', $id);
            if (!$role) {
                return response()->json(['error' => 'Role not found.'], 404);
            }

            if (!empty($role['isDefault']) || $role['id'] === 'super_admin') {
                return response()->json(['error' => 'Cannot delete system default administrator roles.'], 400);
            }

            $this->db->delete('roles', $id);

            $this->permissions->logAuditEvent($userId, "Deleted role: {$role['name']}", 'Roles');

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
