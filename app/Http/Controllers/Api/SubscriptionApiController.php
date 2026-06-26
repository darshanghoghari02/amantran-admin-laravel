<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DbService;
use App\Services\PermissionService;
use Illuminate\Http\Request;

class SubscriptionApiController extends Controller
{
    public function __construct(
        private DbService $db,
        private PermissionService $permissions
    ) {}

    /**
     * GET /api/subscriptions
     * Get all subscription plans.
     */
    public function index()
    {
        try {
            $list = $this->db->getAll('subscriptions');
            return response()->json($list);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * POST /api/subscriptions
     * Create subscription plan.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'name'  => 'required',
                'price' => 'required|numeric',
            ]);

            $userId = $request->header('x-user-id') ?? session('admin_user.id') ?? 'system';

            $planData = [
                'name'               => $request->name,
                'price'              => (float) $request->price,
                'description'        => $request->description ?? '',
                'isActive'           => $request->isActive !== false,
                'includedCategories' => $request->includedCategories ?? [],
                'includedTemplateIds'=> $request->includedTemplateIds ?? [],
                'durationType'       => $request->durationType ?? 'monthly',
                'durationDays'       => (int) ($request->durationDays ?? 30),
            ];

            $newPlan = $this->db->add('subscriptions', $planData);

            $this->permissions->logAuditEvent($userId, "Created subscription plan: {$newPlan['name']}", 'Subscriptions');

            return response()->json($newPlan, 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /api/subscriptions/{id}
     */
    public function show($id)
    {
        try {
            $plan = $this->db->getOne('subscriptions', $id);
            if (!$plan) {
                return response()->json(['error' => 'Subscription plan not found'], 404);
            }
            return response()->json($plan);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * PUT /api/subscriptions/{id}
     */
    public function update(Request $request, $id)
    {
        try {
            $userId = $request->header('x-user-id') ?? session('admin_user.id') ?? 'system';
            $plan = $this->db->getOne('subscriptions', $id);
            if (!$plan) {
                return response()->json(['error' => 'Subscription plan not found'], 404);
            }

            $updates = $request->all();
            if ($request->has('price')) $updates['price'] = (float) $request->price;
            if ($request->has('durationDays')) $updates['durationDays'] = (int) $request->durationDays;

            $updatedPlan = $this->db->update('subscriptions', $id, $updates);

            $this->permissions->logAuditEvent($userId, "Updated subscription plan: {$updatedPlan['name']}", 'Subscriptions');

            return response()->json($updatedPlan);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * DELETE /api/subscriptions/{id}
     */
    public function destroy(Request $request, $id)
    {
        try {
            $userId = $request->header('x-user-id') ?? session('admin_user.id') ?? 'system';
            $plan = $this->db->getOne('subscriptions', $id);
            if (!$plan) {
                return response()->json(['error' => 'Subscription plan not found'], 404);
            }

            $this->db->delete('subscriptions', $id);

            $this->permissions->logAuditEvent($userId, "Deleted subscription plan: {$plan['name']}", 'Subscriptions');

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /* ═══════════════════════════════════════════════════════════
       MOBILE CLIENT SUBSCRIPTIONS AND GATEWAYS
    * ═══════════════════════════════════════════════════════════ */

    /**
     * GET /api/app/user-subscriptions/{userId}
     */
    public function getUserSubscription($userId)
    {
        try {
            $allSubs = $this->db->getAll('user_subscriptions');
            
            // Filter by userId
            $userSubs = array_values(array_filter($allSubs, function ($s) use ($userId) {
                return ($s['userId'] ?? '') === $userId;
            }));

            if (empty($userSubs)) {
                return response()->json([
                    'planType' => 'none',
                    'type'     => 'none',
                    'isActive' => false,
                    'status'   => 'expired',
                ]);
            }

            // Sort by startDate/createdAt descending (newest first)
            usort($userSubs, function ($a, $b) {
                $dateA = $a['startDate'] ?? $a['createdAt'] ?? '';
                $dateB = $b['startDate'] ?? $b['createdAt'] ?? '';
                return strcmp($dateB, $dateA);
            });

            return response()->json($userSubs[0]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * POST /api/app/user-subscriptions/purchase (Mock checkout gateway)
     */
    public function purchaseSubscription(Request $request)
    {
        try {
            $request->validate([
                'userId'   => 'required',
                'planType' => 'required',
            ]);

            $userId   = $request->userId;
            $planType = $request->planType;
            $isTrial  = $request->isTrial === true;
            $price    = $request->price;

            $now = new \DateTime();

            // Deactivate previous active records
            $allSubs = $this->db->getAll('user_subscriptions');
            $activeSubs = array_filter($allSubs, function ($s) use ($userId) {
                return ($s['userId'] ?? '') === $userId && ($s['isActive'] ?? false) === true;
            });

            foreach ($activeSubs as $sub) {
                $this->db->update('user_subscriptions', $sub['id'], [
                    'isActive'  => false,
                    'status'    => 'expired',
                    'updatedAt' => $now->format(\DateTime::ATOM),
                ]);
            }

            if ($isTrial) {
                $expiry = (clone $now)->add(new \DateInterval('P3D')); // 3 days
                $status = 'trial';
                $finalPrice = 0.0;
            } else {
                $status = 'active';
                $finalPrice = (float) ($price ?? ($planType === 'yearly' ? 499.0 : ($planType === 'lifetime' ? 999.0 : 99.0)));
                
                $days = 30;
                if ($planType === 'yearly') {
                    $days = 365;
                } elseif ($planType === 'lifetime') {
                    $days = 36500; // 100 years
                }
                $expiry = (clone $now)->add(new \DateInterval("P{$days}D"));
            }

            $newSub = $this->db->add('user_subscriptions', [
                'userId'             => $userId,
                'planType'           => $planType,
                'type'               => $planType,
                'status'             => $status,
                'isActive'           => true,
                'startDate'          => $now->format(\DateTime::ATOM),
                'expiryDate'         => $expiry->format(\DateTime::ATOM),
                'amountPaid'         => $finalPrice,
                'autoRenew'          => !$isTrial,
                'purchasedTemplates' => [],
            ]);

            // Add Transaction Log
            $this->db->add('transactions', [
                'userId'    => $userId,
                'type'      => 'subscription',
                'amount'    => $finalPrice,
                'planId'    => $planType,
                'status'    => 'success',
                'timestamp' => $now->format(\DateTime::ATOM),
                'details'   => $isTrial ? '3-day trial activated' : 'Mock gateway checkout',
            ]);

            return response()->json($newSub, 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * POST /api/app/user-subscriptions/{userId}/cancel
     */
    public function cancelSubscription(Request $request, $userId)
    {
        try {
            $allSubs = $this->db->getAll('user_subscriptions');
            $userSubs = array_values(array_filter($allSubs, function ($s) use ($userId) {
                return ($s['userId'] ?? '') === $userId && ($s['isActive'] ?? false) === true;
            }));

            if (empty($userSubs)) {
                return response()->json(['error' => 'No active subscription found to cancel'], 400);
            }

            // Sort newest first
            usort($userSubs, function ($a, $b) {
                $dateA = $a['startDate'] ?? $a['createdAt'] ?? '';
                $dateB = $b['startDate'] ?? $b['createdAt'] ?? '';
                return strcmp($dateB, $dateA);
            });

            $activeSub = $userSubs[0];

            $updated = $this->db->update('user_subscriptions', $activeSub['id'], [
                'status'    => 'cancelled',
                'autoRenew' => false,
            ]);

            return response()->json($updated);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * POST /api/app/user-subscriptions/{userId}/reactivate
     */
    public function reactivateSubscription(Request $request, $userId)
    {
        try {
            $allSubs = $this->db->getAll('user_subscriptions');
            $userSubs = array_values(array_filter($allSubs, function ($s) use ($userId) {
                return ($s['userId'] ?? '') === $userId && ($s['isActive'] ?? false) === true;
            }));

            if (empty($userSubs)) {
                return response()->json(['error' => 'No active subscription found to reactivate'], 400);
            }

            usort($userSubs, function ($a, $b) {
                $dateA = $a['startDate'] ?? $a['createdAt'] ?? '';
                $dateB = $b['startDate'] ?? $b['createdAt'] ?? '';
                return strcmp($dateB, $dateA);
            });

            $activeSub = $userSubs[0];

            $updated = $this->db->update('user_subscriptions', $activeSub['id'], [
                'status'    => 'active',
                'autoRenew' => true,
            ]);

            return response()->json($updated);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * POST /api/app/user-subscriptions/purchase-template
     */
    public function purchaseTemplate(Request $request)
    {
        try {
            $request->validate([
                'userId'     => 'required',
                'templateId' => 'required',
            ]);

            $userId     = $request->userId;
            $templateId = $request->templateId;
            $price      = $request->price;

            $allSubs = $this->db->getAll('user_subscriptions');
            $userSubs = array_values(array_filter($allSubs, function ($s) use ($userId) {
                return ($s['userId'] ?? '') === $userId;
            }));

            $doc = null;
            $currentPurchased = [];

            if (!empty($userSubs)) {
                usort($userSubs, function ($a, $b) {
                    $dateA = $a['startDate'] ?? $a['createdAt'] ?? '';
                    $dateB = $b['startDate'] ?? $b['createdAt'] ?? '';
                    return strcmp($dateB, $dateA);
                });
                $doc = $userSubs[0];
                $currentPurchased = $doc['purchasedTemplates'] ?? [];
            } else {
                // Create a basic blank subscription document
                $doc = $this->db->add('user_subscriptions', [
                    'userId'             => $userId,
                    'planType'           => 'none',
                    'status'             => 'expired',
                    'isActive'           => false,
                    'startDate'          => now()->toISOString(),
                    'expiryDate'         => now()->toISOString(),
                    'amountPaid'         => 0.0,
                    'autoRenew'          => false,
                    'purchasedTemplates' => [],
                ]);
            }

            if (!in_array($templateId, $currentPurchased)) {
                $currentPurchased[] = $templateId;
            }

            $updated = $this->db->update('user_subscriptions', $doc['id'], [
                'purchasedTemplates' => $currentPurchased,
            ]);

            // Add transaction
            $this->db->add('transactions', [
                'userId'    => $userId,
                'type'      => 'single_purchase',
                'amount'    => (float) ($price ?? 49.0),
                'planId'    => $templateId,
                'status'    => 'success',
                'timestamp' => now()->toISOString(),
                'details'   => 'Template purchase',
            ]);

            return response()->json($updated);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /api/app/transactions/{userId}
     */
    public function getUserTransactions($userId)
    {
        try {
            $list = $this->db->getAll('transactions');
            $userTxns = array_values(array_filter($list, function ($t) use ($userId) {
                return ($t['userId'] ?? '') === $userId;
            }));
            
            // Sort by date descending
            usort($userTxns, function ($a, $b) {
                return strcmp($b['timestamp'] ?? $b['createdAt'] ?? '', $a['timestamp'] ?? $a['createdAt'] ?? '');
            });

            return response()->json($userTxns);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
