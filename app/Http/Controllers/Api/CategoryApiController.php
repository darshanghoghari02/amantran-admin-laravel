<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DbService;
use App\Services\PermissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CategoryApiController extends Controller
{
    public function __construct(
        private DbService $db,
        private PermissionService $permissions
    ) {}

    /**
     * GET /api/categories
     */
    public function index()
    {
        try {
            $list = $this->db->getAll('categories');
            // Sort by displayOrder ascending
            usort($list, function ($a, $b) {
                return ($a['displayOrder'] ?? 0) - ($b['displayOrder'] ?? 0);
            });
            return response()->json($list);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * POST /api/categories
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required',
                'slug' => 'required',
            ]);

            $userId = $request->header('x-user-id') ?? session('admin_user.id') ?? 'system';

            $slug = strtolower(preg_replace('/[^a-zA-Z0-9_]/', '_', $request->slug));

            $categoryData = [
                'name'         => $request->name,
                'slug'         => $slug,
                'imageUrl'     => $request->imageUrl ?? '',
                'displayOrder' => (int) ($request->displayOrder ?? 1),
                'isActive'     => $request->isActive !== false,
            ];

            $newCategory = $this->db->add('categories', $categoryData);

            $this->permissions->logAuditEvent($userId, "Created category: {$newCategory['name']}", 'Categories');

            return response()->json($newCategory, 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /api/categories/{id}
     */
    public function show($id)
    {
        try {
            $category = $this->db->getOne('categories', $id);
            if (!$category) {
                return response()->json(['error' => 'Category not found'], 404);
            }
            return response()->json($category);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * PUT /api/categories/{id}
     */
    public function update(Request $request, $id)
    {
        try {
            $userId = $request->header('x-user-id') ?? session('admin_user.id') ?? 'system';
            $category = $this->db->getOne('categories', $id);
            if (!$category) {
                return response()->json(['error' => 'Category not found'], 404);
            }

            $updates = [];
            if ($request->has('name')) $updates['name'] = $request->name;
            if ($request->has('slug')) {
                $updates['slug'] = strtolower(preg_replace('/[^a-zA-Z0-9_]/', '_', $request->slug));
            }
            if ($request->has('imageUrl')) {
                // If the imageUrl is changing, delete the old local asset
                if ($request->imageUrl !== $category['imageUrl']) {
                    $this->deleteAssetFile($category['imageUrl']);
                }
                $updates['imageUrl'] = $request->imageUrl;
            }
            if ($request->has('displayOrder')) $updates['displayOrder'] = (int) $request->displayOrder;
            if ($request->has('isActive')) $updates['isActive'] = $request->isActive === true;

            $updatedCategory = $this->db->update('categories', $id, $updates);

            $this->permissions->logAuditEvent($userId, "Updated category: {$updatedCategory['name']}", 'Categories');

            return response()->json($updatedCategory);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * DELETE /api/categories/{id}
     */
    public function destroy(Request $request, $id)
    {
        try {
            $userId = $request->header('x-user-id') ?? session('admin_user.id') ?? 'system';
            $category = $this->db->getOne('categories', $id);
            if (!$category) {
                return response()->json(['error' => 'Category not found'], 404);
            }

            // Delete associated image
            if (!empty($category['imageUrl'])) {
                $this->deleteAssetFile($category['imageUrl']);
            }

            $this->db->delete('categories', $id);

            $this->permissions->logAuditEvent($userId, "Deleted category: {$category['name']}", 'Categories');

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
