<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DbService;
use App\Services\PermissionService;
use Illuminate\Http\Request;

class LanguageApiController extends Controller
{
    public function __construct(
        private DbService $db,
        private PermissionService $permissions
    ) {}

    /**
     * GET /api/languages
     */
    public function index()
    {
        try {
            $list = $this->db->getAll('languages');
            // Sort by name alphabetically
            usort($list, function ($a, $b) {
                return strcasecmp($a['name'] ?? '', $b['name'] ?? '');
            });
            return response()->json($list);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * POST /api/languages
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required',
                'code' => 'required',
            ]);

            $userId = $request->header('x-user-id') ?? session('admin_user.id') ?? 'system';

            $langData = [
                'name'     => $request->name,
                'code'     => strtolower(trim($request->code)),
                'isActive' => $request->isActive !== false,
            ];

            $newLang = $this->db->add('languages', $langData);

            $this->permissions->logAuditEvent($userId, "Created language: {$newLang['name']}", 'Languages');

            return response()->json($newLang, 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /api/languages/{id}
     */
    public function show($id)
    {
        try {
            $lang = $this->db->getOne('languages', $id);
            if (!$lang) {
                return response()->json(['error' => 'Language not found'], 404);
            }
            return response()->json($lang);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * PUT /api/languages/{id}
     */
    public function update(Request $request, $id)
    {
        try {
            $userId = $request->header('x-user-id') ?? session('admin_user.id') ?? 'system';
            $lang = $this->db->getOne('languages', $id);
            if (!$lang) {
                return response()->json(['error' => 'Language not found'], 404);
            }

            $updates = [];
            if ($request->has('name')) $updates['name'] = $request->name;
            if ($request->has('code')) $updates['code'] = strtolower(trim($request->code));
            if ($request->has('isActive')) $updates['isActive'] = $request->isActive === true;

            $updatedLang = $this->db->update('languages', $id, $updates);

            $this->permissions->logAuditEvent($userId, "Updated language: {$updatedLang['name']}", 'Languages');

            return response()->json($updatedLang);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * DELETE /api/languages/{id}
     */
    public function destroy(Request $request, $id)
    {
        try {
            $userId = $request->header('x-user-id') ?? session('admin_user.id') ?? 'system';
            $lang = $this->db->getOne('languages', $id);
            if (!$lang) {
                return response()->json(['error' => 'Language not found'], 404);
            }

            $this->db->delete('languages', $id);

            $this->permissions->logAuditEvent($userId, "Deleted language: {$lang['name']}", 'Languages');

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
