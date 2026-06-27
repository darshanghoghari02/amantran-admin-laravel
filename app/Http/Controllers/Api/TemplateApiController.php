<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DbService;
use App\Services\PermissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TemplateApiController extends Controller
{
    public function __construct(
        private DbService $db,
        private PermissionService $permissions
    ) {}

    /**
     * GET /api/templates
     */
    public function index(Request $request)
    {
        try {
            $page = max(1, (int) $request->get('page', 1));
            $perPage = max(1, (int) $request->get('perPage', 12));
            
            // Use database-level pagination for better performance
            $list = $this->db->getPaginated('templates', $page, $perPage);
            $total = $this->db->getCount('templates');
            
            // Filter by categoryId if provided (client-side filter for now)
            if ($request->has('categoryId') && !empty($request->categoryId)) {
                $allTemplates = $this->db->getAll('templates');
                $filtered = array_filter($allTemplates, fn($tpl) => $tpl['categoryId'] === $request->categoryId);
                $total = count($filtered);
                $offset = ($page - 1) * $perPage;
                $list = array_slice(array_values($filtered), $offset, $perPage);
            }
            
            return response()->json([
                'data' => $list,
                'pagination' => [
                    'total' => $total,
                    'page' => $page,
                    'perPage' => $perPage,
                    'totalPages' => ceil($total / $perPage),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * POST /api/templates
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required',
            ]);

            $userId = $request->header('x-user-id') ?? session('admin_user.id') ?? 'system';

            $slug = $request->slug 
                ? strtolower(preg_replace('/[^a-zA-Z0-9_]/', '_', $request->slug))
                : strtolower(preg_replace('/[^a-zA-Z0-9_]/', '_', $request->name));

            $thumb = $request->thumbnailUrl ?? $request->thumbnail ?? '';
            $templateData = [
                'name'         => $request->name,
                'slug'         => $slug,
                'categoryId'   => $request->categoryId ?? '',
                'thumbnailUrl' => $thumb,
                'thumbnail'    => $thumb,
                'isActive'     => $request->isActive !== false,
                'isPremium'    => $request->isPremium === true,
                'fonts'        => $request->fonts ?? [],
                'languages'    => $request->languages ?? [],
                'singlePurchasePrice' => $request->singlePurchasePrice ?? 0,
                'includedInMonthlyPlan' => $request->includedInMonthlyPlan ?? false,
                'includedInYearlyPlan' => $request->includedInYearlyPlan ?? false,
                'pages'        => $request->pages ?? [
                    [
                        'id' => 'page_1',
                        'backgroundImage' => '',
                        'elements' => []
                    ]
                ],
            ];

            $newTemplate = $this->db->add('templates', $templateData);

            $this->permissions->logAuditEvent($userId, "Created template: {$newTemplate['name']}", 'Templates');

            return response()->json($newTemplate, 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /api/templates/{id}
     */
    public function show($id)
    {
        try {
            $template = $this->db->getOne('templates', $id);
            if (!$template) {
                return response()->json(['error' => 'Template not found'], 404);
            }
            return response()->json($template);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * PUT /api/templates/{id}
     */
    public function update(Request $request, $id)
    {
        try {
            $userId = $request->header('x-user-id') ?? session('admin_user.id') ?? 'system';
            $template = $this->db->getOne('templates', $id);
            if (!$template) {
                return response()->json(['error' => 'Template not found'], 404);
            }

            // Since it's a JSON database, we can merge the request body directly
            // except fields we want to format (like slug).
            $updates = $request->all();
            
            if ($request->has('slug')) {
                $updates['slug'] = strtolower(preg_replace('/[^a-zA-Z0-9_]/', '_', $request->slug));
            }

            // Sync thumbnail and thumbnailUrl
            if ($request->has('thumbnail') || $request->has('thumbnailUrl')) {
                $thumb = $request->thumbnail ?? $request->thumbnailUrl ?? '';
                $updates['thumbnail'] = $thumb;
                $updates['thumbnailUrl'] = $thumb;
            }

            // If thumbnail changes, delete old one
            $oldThumb = $template['thumbnailUrl'] ?? $template['thumbnail'] ?? '';
            $newThumb = $updates['thumbnailUrl'] ?? '';
            if (($request->has('thumbnailUrl') || $request->has('thumbnail')) && $newThumb !== $oldThumb) {
                $this->deleteAssetFile($oldThumb);
            }

            // Detect page background changes to clean up deleted assets
            if ($request->has('pages') && is_array($request->pages)) {
                $this->cleanupRemovedPageBackgrounds($template['pages'] ?? [], $request->pages);
            }

            $updatedTemplate = $this->db->update('templates', $id, $updates);

            $this->permissions->logAuditEvent($userId, "Updated template: {$updatedTemplate['name']}", 'Templates');

            return response()->json($updatedTemplate);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * DELETE /api/templates/{id}
     */
    public function destroy(Request $request, $id)
    {
        try {
            $userId = $request->header('x-user-id') ?? session('admin_user.id') ?? 'system';
            $template = $this->db->getOne('templates', $id);
            if (!$template) {
                return response()->json(['error' => 'Template not found'], 404);
            }

            // 1. Delete thumbnail
            $this->deleteAssetFile($template['thumbnailUrl'] ?? '');

            // 2. Delete backgrounds of all pages
            if (isset($template['pages']) && is_array($template['pages'])) {
                foreach ($template['pages'] as $page) {
                    $this->deleteAssetFile($page['backgroundImage'] ?? '');
                    
                    // Also delete any local image/sticker uploads in elements
                    if (isset($page['elements']) && is_array($page['elements'])) {
                        foreach ($page['elements'] as $elem) {
                            if (($elem['type'] ?? '') === 'image' && !empty($elem['src'])) {
                                $this->deleteAssetFile($elem['src']);
                            }
                        }
                    }
                }
            }

            // 3. Delete the entire template folder from assets
            $this->deleteTemplateFolder($template['slug'] ?? '');

            $this->db->delete('templates', $id);

            $this->permissions->logAuditEvent($userId, "Deleted template: {$template['name']}", 'Templates');

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Cleanup backgrounds of pages that are deleted or whose backgrounds changed.
     */
    private function cleanupRemovedPageBackgrounds(array $oldPages, array $newPages)
    {
        $newBgs = [];
        foreach ($newPages as $p) {
            if (!empty($p['backgroundImage'])) {
                $newBgs[] = $p['backgroundImage'];
            }
        }

        foreach ($oldPages as $oldPage) {
            $oldBg = $oldPage['backgroundImage'] ?? '';
            if ($oldBg && !in_array($oldBg, $newBgs)) {
                $this->deleteAssetFile($oldBg);
            }
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

    /**
     * Delete the entire template folder from assets directory.
     */
    private function deleteTemplateFolder(?string $slug)
    {
        if (!$slug) return;

        // Check both wedding and engagement directories
        $categories = ['wedding', 'engagement'];
        
        foreach ($categories as $category) {
            $folderPath = public_path("assets/{$category}/{$slug}");
            
            if (is_dir($folderPath)) {
                $this->deleteDirectory($folderPath);
            }
        }
    }

    /**
     * Recursively delete a directory and its contents.
     */
    private function deleteDirectory($dir)
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);
        
        foreach ($files as $file) {
            $path = $dir . DIRECTORY_SEPARATOR . $file;
            
            if (is_dir($path)) {
                $this->deleteDirectory($path);
            } else {
                unlink($path);
            }
        }
        
        rmdir($dir);
    }
}
