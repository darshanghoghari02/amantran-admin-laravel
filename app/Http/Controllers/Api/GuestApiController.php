<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DbService;
use Illuminate\Http\Request;

class GuestApiController extends Controller
{
    public function __construct(
        private DbService $db
    ) {}

    /**
     * GET /api/app/guests/{userId}
     */
    public function index($userId)
    {
        try {
            $list = $this->db->getAll('guests');
            $userGuests = array_values(array_filter($list, function ($g) use ($userId) {
                return ($g['userId'] ?? '') === $userId;
            }));

            // Sort by updatedAt descending
            usort($userGuests, function ($a, $b) {
                return strcmp($b['updatedAt'] ?? $b['createdAt'] ?? '', $a['updatedAt'] ?? $a['createdAt'] ?? '');
            });

            return response()->json($userGuests);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * POST /api/app/guests
     */
    public function save(Request $request)
    {
        try {
            $request->validate([
                'id'     => 'required',
                'userId' => 'required',
            ]);

            $id     = $request->id;
            $userId = $request->userId;

            $guestData = [
                'id'           => $id,
                'userId'       => $userId,
                'name'         => $request->name ?? '',
                'phone'        => $request->phone ?? '',
                'relation'     => $request->relation ?? '',
                'inviteStatus' => $request->inviteStatus ?? 'pending',
                'updatedAt'    => $request->updatedAt ?? now()->toISOString(),
            ];

            $existing = $this->db->getOne('guests', $id);
            if ($existing) {
                $updated = $this->db->update('guests', $id, $guestData);
                return response()->json($updated);
            } else {
                $created = $this->db->add('guests', $guestData);
                return response()->json($created, 201);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * DELETE /api/app/guests/{guestId}
     */
    public function delete($guestId)
    {
        try {
            $this->db->delete('guests', $guestId);
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * DELETE /api/app/guests/clear/{userId}
     */
    public function clearAll($userId)
    {
        try {
            $list = $this->db->getAll('guests');
            $userGuests = array_filter($list, function ($g) use ($userId) {
                return ($g['userId'] ?? '') === $userId;
            });

            foreach ($userGuests as $guest) {
                $this->db->delete('guests', $guest['id']);
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
