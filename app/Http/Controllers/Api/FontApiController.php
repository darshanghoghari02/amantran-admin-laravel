<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DbService;
use App\Services\PermissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FontApiController extends Controller
{
    public function __construct(
        private DbService $db,
        private PermissionService $permissions
    ) {}

    /**
     * GET /api/fonts
     */
    public function index(Request $request)
    {
        try {
            $list = $this->db->getAll('fonts');

            // Format for Flutter compatibility if the request comes from the mobile app path
            if ($request->is('*app/*')) {
                $mapped = array_map(function ($f) {
                    return [
                        'id'         => $f['id'],
                        'fontFamily' => $f['family'] ?? $f['fontFamily'] ?? '',
                        'fontUrl'    => $f['localPath'] ?? $f['fontUrl'] ?? '',
                        'isActive'   => ($f['isActive'] ?? true) !== false,
                    ];
                }, $list);
                return response()->json($mapped);
            }

            return response()->json($list);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * POST /api/fonts
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'family'    => 'required',
                'localPath' => 'required',
            ]);

            $userId = $request->header('x-user-id') ?? session('admin_user.id') ?? 'system';

            $fontData = [
                'family'    => $request->family,
                'localPath' => $request->localPath,
                'isActive'  => $request->isActive !== false,
            ];

            $newFont = $this->db->add('fonts', $fontData);

            $this->permissions->logAuditEvent($userId, "Created font: {$newFont['family']}", 'Fonts');

            return response()->json($newFont, 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /api/fonts/{id}
     */
    public function show($id)
    {
        try {
            $font = $this->db->getOne('fonts', $id);
            if (!$font) {
                return response()->json(['error' => 'Font not found'], 404);
            }
            return response()->json($font);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * PUT /api/fonts/{id}
     */
    public function update(Request $request, $id)
    {
        try {
            $userId = $request->header('x-user-id') ?? session('admin_user.id') ?? 'system';
            $font = $this->db->getOne('fonts', $id);
            if (!$font) {
                return response()->json(['error' => 'Font not found'], 404);
            }

            $updates = [];
            if ($request->has('family')) $updates['family'] = $request->family;
            if ($request->has('localPath')) {
                if ($request->localPath !== $font['localPath']) {
                    $this->deleteAssetFile($font['localPath']);
                }
                $updates['localPath'] = $request->localPath;
            }
            if ($request->has('isActive')) $updates['isActive'] = $request->isActive === true;

            $updatedFont = $this->db->update('fonts', $id, $updates);

            $this->permissions->logAuditEvent($userId, "Updated font: {$updatedFont['family']}", 'Fonts');

            return response()->json($updatedFont);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * DELETE /api/fonts/{id}
     */
    public function destroy(Request $request, $id)
    {
        try {
            $userId = $request->header('x-user-id') ?? session('admin_user.id') ?? 'system';
            $font = $this->db->getOne('fonts', $id);
            if (!$font) {
                return response()->json(['error' => 'Font not found'], 404);
            }

            // Delete local font file
            if (!empty($font['localPath'])) {
                $this->deleteAssetFile($font['localPath']);
            }

            $this->db->delete('fonts', $id);

            $this->permissions->logAuditEvent($userId, "Deleted font: {$font['family']}", 'Fonts');

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Helper to delete local asset file.
     */
    private function deleteAssetFile(?string $filePath)
    {
        if (!$filePath) return;
        
        $path = parse_url($filePath, PHP_URL_PATH);
        if ($path) {
            if (str_starts_with($path, '/storage/')) {
                $relativePath = substr($path, 9); // strip '/storage/'
                if (Storage::disk('public')->exists($relativePath)) {
                    Storage::disk('public')->delete($relativePath);
                }
            } else {
                $fullPath = public_path(ltrim($path, '/'));
                if (file_exists($fullPath) && is_file($fullPath)) {
                    unlink($fullPath);
                }
            }
        }
    }
}
