<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DbService;
use Illuminate\Http\Request;

class DraftApiController extends Controller
{
    public function __construct(
        private DbService $db
    ) {}

    /**
     * GET /api/app/drafts/{userId}
     */
    public function indexDrafts($userId)
    {
        try {
            $list = $this->db->getAll('user_drafts');
            $userDrafts = array_values(array_filter($list, function ($d) use ($userId) {
                return ($d['userId'] ?? '') === $userId;
            }));
            
            // Sort by updatedAt descending
            usort($userDrafts, function ($a, $b) {
                return strcmp($b['updatedAt'] ?? $b['createdAt'] ?? '', $a['updatedAt'] ?? $a['createdAt'] ?? '');
            });

            return response()->json($userDrafts);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * POST /api/app/drafts
     */
    public function saveDraft(Request $request)
    {
        try {
            $request->validate([
                'id'     => 'required',
                'userId' => 'required',
            ]);

            $id     = $request->id;
            $userId = $request->userId;
            $template = $request->template;

            $draftData = [
                'id'             => $id,
                'userId'         => $userId,
                'templateId'     => $request->templateId ?? ($template['id'] ?? ''),
                'templateName'   => $request->templateName ?? ($template['title'] ?? ''),
                'customizedData' => $request->customizedData ?? $request->all(),
                'isDraft'        => $request->isDraft !== false,
                'updatedAt'      => now()->toISOString(),
            ];

            // If template contains specific items, keep it
            if ($request->has('elements')) {
                $draftData['elements'] = $request->elements;
            }

            $existing = $this->db->getOne('user_drafts', $id);
            if ($existing) {
                $updated = $this->db->update('user_drafts', $id, $draftData);
                return response()->json($updated);
            } else {
                $created = $this->db->add('user_drafts', $draftData);
                return response()->json($created, 201);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * DELETE /api/app/drafts/{draftId}
     */
    public function deleteDraft($draftId)
    {
        try {
            $this->db->delete('user_drafts', $draftId);
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /api/app/cards/{userId}
     */
    public function indexCards($userId)
    {
        try {
            $list = $this->db->getAll('user_cards');
            $userCards = array_values(array_filter($list, function ($c) use ($userId) {
                return ($c['userId'] ?? '') === $userId;
            }));

            // Sort by updatedAt descending
            usort($userCards, function ($a, $b) {
                return strcmp($b['updatedAt'] ?? $b['createdAt'] ?? '', $a['updatedAt'] ?? $a['createdAt'] ?? '');
            });

            return response()->json($userCards);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * POST /api/app/cards
     */
    public function saveCard(Request $request)
    {
        try {
            $request->validate([
                'id'     => 'required',
                'userId' => 'required',
            ]);

            $id     = $request->id;
            $userId = $request->userId;
            $template = $request->template;

            $cardData = [
                'id'             => $id,
                'userId'         => $userId,
                'templateId'     => $request->templateId ?? ($template['id'] ?? ''),
                'templateName'   => $request->templateName ?? ($template['title'] ?? ''),
                'customizedData' => $request->customizedData ?? $request->all(),
                'isDraft'        => $request->isDraft === true,
                'updatedAt'      => now()->toISOString(),
            ];

            $existing = $this->db->getOne('user_cards', $id);
            if ($existing) {
                $updated = $this->db->update('user_cards', $id, $cardData);
                return response()->json($updated);
            } else {
                $created = $this->db->add('user_cards', $cardData);
                return response()->json($created, 201);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * DELETE /api/app/cards/{cardId}
     */
    public function deleteCard($cardId)
    {
        try {
            $this->db->delete('user_cards', $cardId);
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
