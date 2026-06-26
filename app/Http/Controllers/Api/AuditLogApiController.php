<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DbService;
use Illuminate\Http\Request;

class AuditLogApiController extends Controller
{
    public function __construct(
        private DbService $db
    ) {}

    /**
     * GET /api/audit-logs
     */
    public function index()
    {
        try {
            $list = $this->db->getAll('audit_logs');

            // Sort by createdAt descending
            usort($list, function ($a, $b) {
                $dateA = $a['createdAt'] ?? $a['timestamp'] ?? '';
                $dateB = $b['createdAt'] ?? $b['timestamp'] ?? '';
                return strcmp($dateB, $dateA);
            });

            return response()->json($list);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * POST /api/audit-logs
     * Store logs submitted from clients/mobile app.
     */
    public function store(Request $request)
    {
        try {
            $userId      = $request->userId ?? 'anonymous';
            $type        = $request->type ?? 'info';
            $description = $request->description ?? '';
            $details     = $request->details ?? [];

            $logData = [
                'userId'      => $userId,
                'type'        => $type,
                'description' => $description,
                'details'     => $details,
                'createdAt'   => now()->toISOString(),
            ];

            $created = $this->db->add('audit_logs', $logData);
            
            return response()->json($created, 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
