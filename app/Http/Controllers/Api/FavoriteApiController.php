<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DbService;
use Illuminate\Http\Request;

class FavoriteApiController extends Controller
{
    public function __construct(
        private DbService $db
    ) {}

    /**
     * GET /api/app/favorites/{userId}
     */
    public function index($userId)
    {
        try {
            $list = $this->db->getAll('user_favorites');
            $userFavs = array_values(array_filter($list, function ($f) use ($userId) {
                return ($f['userId'] ?? '') === $userId;
            }));

            // Return just the template IDs as an array
            $templateIds = array_map(function ($f) {
                return $f['templateId'];
            }, $userFavs);

            return response()->json($templateIds);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * POST /api/app/favorites
     */
    public function toggle(Request $request)
    {
        try {
            $request->validate([
                'userId'     => 'required',
                'templateId' => 'required',
            ]);

            $userId     = $request->userId;
            $templateId = $request->templateId;
            $isFavorite = $request->isFavorite === true;

            $favId = "{$userId}_{$templateId}";

            if ($isFavorite) {
                $favData = [
                    'id'         => $favId,
                    'userId'     => $userId,
                    'templateId' => $templateId,
                    'isFavorite' => true,
                    'updatedAt'  => now()->toISOString(),
                ];
                $this->db->add('user_favorites', $favData);
                return response()->json(['success' => true, 'isFavorite' => true]);
            } else {
                $this->db->delete('user_favorites', $favId);
                return response()->json(['success' => true, 'isFavorite' => false]);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
