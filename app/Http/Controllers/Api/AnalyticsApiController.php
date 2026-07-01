<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DbService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AnalyticsApiController extends Controller
{
    public function __construct(
        private DbService $db
    ) {}

    /**
     * GET /api/analytics/summary
     */
    public function summary()
    {
        try {
            $templates    = $this->db->getAll('templates');
            $categories   = $this->db->getAll('categories');
            $users        = $this->db->getAll('app_users');
            $drafts       = $this->db->getAll('user_drafts');
            $cards        = $this->db->getAll('user_cards');
            $transactions = $this->db->getAll('transactions');

            $totalTemplates  = count($templates);
            $totalCategories = count($categories);
            $totalUsers      = count($users);

            $premiumTemplates = count(array_filter($templates, fn($t) => !empty($t['isPremium'])));
            $activeUsersCount = count(array_filter($users, fn($u) => empty($u['isBlocked']) && ($u['accountStatus'] ?? '') !== 'suspended'));
            $totalDrafts      = count($drafts);
            $totalInvitations = count($cards);

            // Recent activities accumulator
            $activities = [];

            // Add templates
            foreach ($templates as $t) {
                if (!empty($t['createdAt'])) {
                    $activities[] = [
                        'id'        => 'tpl_' . $t['id'],
                        'user'      => 'Admin',
                        'action'    => 'Published template "' . ($t['name'] ?? 'Untitled') . '" (' . (!empty($t['isPremium']) ? 'Premium' : 'Free') . ')',
                        'timestamp' => Carbon::parse($t['createdAt']),
                    ];
                }
            }

            // Add users
            foreach ($users as $u) {
                if (!empty($u['createdAt'])) {
                    $activities[] = [
                        'id'        => 'usr_' . $u['id'],
                        'user'      => 'System',
                        'action'    => 'New user registered: ' . ($u['displayName'] ?? $u['email'] ?? $u['phone'] ?? 'User'),
                        'timestamp' => Carbon::parse($u['createdAt']),
                    ];
                }
            }

            // Add transactions
            foreach ($transactions as $tx) {
                $timeVal = $tx['timestamp'] ?? $tx['createdAt'] ?? null;
                if ($timeVal) {
                    $activities[] = [
                        'id'        => 'tx_' . $tx['id'],
                        'user'      => $tx['userEmail'] ?? 'User',
                        'action'    => ($tx['type'] ?? '') === 'subscription'
                            ? 'Purchased ' . ($tx['planId'] ?? 'plan') . ' subscription (₹' . ($tx['amount'] ?? 0) . ')'
                            : 'Purchased template "' . ($tx['templateName'] ?? 'invitation') . '" (₹' . ($tx['amount'] ?? 0) . ')',
                        'timestamp' => Carbon::parse($timeVal),
                    ];
                }
            }

            // Build template name lookup map
            $templateNameMap = [];
            foreach ($templates as $t) {
                if (!empty($t['id'])) {
                    $templateNameMap[$t['id']] = $t['name'] ?? 'Untitled';
                }
            }

            // Build app user name map
            $appUserMap = [];
            foreach ($users as $u) {
                if (!empty($u['id'])) {
                    $appUserMap[$u['id']] = $u['displayName'] ?? $u['name'] ?? $u['email'] ?? $u['phone'] ?? null;
                }
            }

            // Add drafts
            foreach ($drafts as $d) {
                $timeVal = $d['savedAt'] ?? $d['createdAt'] ?? $d['updatedAt'] ?? null;
                if ($timeVal && !empty($d['userId'])) {
                    $tid = $d['templateId'] ?? '';
                    $tname = $d['templateName'] ?? '';
                    if (empty($tname)) {
                        $tname = $templateNameMap[$tid] ?? 'Template';
                    }
                    
                    $resolvedUser = $appUserMap[$d['userId']] ?? ('User ID: ' . substr($d['userId'], 0, 6) . '...');

                    $activities[] = [
                        'id'        => 'drf_' . $d['id'],
                        'user'      => $resolvedUser,
                        'action'    => 'Created draft invitation for "' . $tname . '"',
                        'timestamp' => Carbon::parse($timeVal),
                    ];
                }
            }

            // Add completed cards (downloads)
            foreach ($cards as $c) {
                $timeVal = $c['savedAt'] ?? $c['createdAt'] ?? $c['updatedAt'] ?? null;
                if ($timeVal && !empty($c['userId'])) {
                    $tid = $c['templateId'] ?? '';
                    $tname = $c['templateName'] ?? '';
                    if (empty($tname)) {
                        $tname = $templateNameMap[$tid] ?? 'Template';
                    }

                    $resolvedUser = $appUserMap[$c['userId']] ?? ('User ID: ' . substr($c['userId'], 0, 6) . '...');

                    $activities[] = [
                        'id'        => 'crd_' . $c['id'],
                        'user'      => $resolvedUser,
                        'action'    => 'Finalized/Downloaded invitation for "' . $tname . '"',
                        'timestamp' => Carbon::parse($timeVal),
                    ];
                }
            }

            // Sort descending by timestamp
            usort($activities, function ($a, $b) {
                return $b['timestamp']->timestamp - $a['timestamp']->timestamp;
            });

            // Top 5 recent activities with relative time strings
            $recentActivities = [];
            $now = Carbon::now();
            foreach (array_slice($activities, 0, 5) as $act) {
                $diffSeconds = $now->timestamp - $act['timestamp']->timestamp;
                $timeStr = 'Just now';

                if ($diffSeconds >= 86400) {
                    $diffDays = floor($diffSeconds / 86400);
                    $timeStr = $diffDays . ' day' . ($diffDays > 1 ? 's' : '') . ' ago';
                } elseif ($diffSeconds >= 3600) {
                    $diffHours = floor($diffSeconds / 3600);
                    $timeStr = $diffHours . ' hour' . ($diffHours > 1 ? 's' : '') . ' ago';
                } elseif ($diffSeconds >= 60) {
                    $diffMins = floor($diffSeconds / 60);
                    $timeStr = $diffMins . ' min' . ($diffMins > 1 ? 's' : '') . ' ago';
                }

                $recentActivities[] = [
                    'id'     => $act['id'],
                    'user'   => $act['user'],
                    'action' => $act['action'],
                    'time'   => $timeStr,
                ];
            }

            // Popular templates calculated from cards (completed templates / downloads)
            $templateCounts = [];
            foreach ($cards as $c) {
                if (!empty($c['templateId'])) {
                    $tid = $c['templateId'];
                    $templateCounts[$tid] = ($templateCounts[$tid] ?? 0) + 1;
                }
            }
            // Add transactions if single purchase
            foreach ($transactions as $tx) {
                if (($tx['type'] ?? '') === 'single_purchase' && !empty($tx['planId'])) {
                    $tid = $tx['planId'];
                    $templateCounts[$tid] = ($templateCounts[$tid] ?? 0) + 1;
                }
            }

            $topTemplates = [];
            foreach ($templates as $t) {
                $count = $templateCounts[$t['id']] ?? 0;
                $topTemplates[] = [
                    'id'        => $t['id'],
                    'name'      => $t['name'] ?? 'Untitled',
                    'slug'      => $t['slug'] ?? '',
                    'downloads' => $count,
                    'isPremium' => !empty($t['isPremium']),
                ];
            }

            usort($topTemplates, fn($a, $b) => $b['downloads'] - $a['downloads']);
            $finalTopTemplates = array_slice($topTemplates, 0, 3);

            return response()->json([
                'counters' => [
                    'totalTemplates'   => $totalTemplates,
                    'totalCategories'  => $totalCategories,
                    'totalUsers'       => $totalUsers,
                    'premiumTemplates' => $premiumTemplates,
                    'activeUsersCount' => $activeUsersCount,
                    'totalInvitations' => $totalInvitations,
                    'totalDrafts'      => $totalDrafts,
                ],
                'recentActivities' => $recentActivities,
                'topTemplates'     => $finalTopTemplates,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /api/analytics/charts
     */
    public function charts(Request $request)
    {
        try {
            $templates  = $this->db->getAll('templates');
            $categories = $this->db->getAll('categories');
            $users      = $this->db->getAll('app_users');

            $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            $userGrowthTrend = [];
            $now = Carbon::now();
            
            $range = $request->query('userGrowthRange', '6m');
            
            if ($range === '6m') {
                $stepMonths = 6;
                $start = Carbon::now()->subMonths(5)->startOfMonth();
            } elseif ($range === '12m') {
                $stepMonths = 12;
                $start = Carbon::now()->subMonths(11)->startOfMonth();
            } elseif ($range === 'this_year') {
                $stepMonths = $now->month;
                $start = Carbon::now()->startOfYear();
            } elseif ($range === 'last_year') {
                $stepMonths = 12;
                $start = Carbon::now()->subYear()->startOfYear();
            } else {
                // custom
                $startDateStr = $request->query('userGrowthStart');
                $endDateStr   = $request->query('userGrowthEnd');
                
                $start = $startDateStr ? Carbon::parse($startDateStr) : Carbon::now()->subMonths(5)->startOfMonth();
                $end   = $endDateStr ? Carbon::parse($endDateStr) : Carbon::now();
                
                $stepMonths = max(1, ($end->year - $start->year) * 12 + ($end->month - $start->month) + 1);
            }

            for ($i = 0; $i < $stepMonths; $i++) {
                $d = $start->copy()->addMonths($i);
                $monthLabel = $months[$d->month - 1] . ($range === '12m' || $range === 'last_year' || $range === 'custom' ? " '" . substr($d->year, -2) : '');
                $endOfMonth = $d->copy()->endOfMonth();

                $count = count(array_filter($users, function ($u) use ($endOfMonth) {
                    if (empty($u['createdAt'])) return false;
                    return Carbon::parse($u['createdAt'])->lte($endOfMonth);
                }));

                $userGrowthTrend[] = [
                    'month' => $monthLabel,
                    'users' => $count,
                ];
            }

            // Template distribution by category
            $filteredTemplates = $templates;
            $distRange = $request->query('distributionRange', 'this_month');

            if ($distRange === 'this_month') {
                $startOfMonth = Carbon::now()->startOfMonth();
                $filteredTemplates = array_filter($templates, function ($t) use ($startOfMonth) {
                    if (empty($t['createdAt'])) return false;
                    return Carbon::parse($t['createdAt'])->gte($startOfMonth);
                });
            } elseif ($distRange === 'last_month') {
                $startOfLast = Carbon::now()->subMonth()->startOfMonth();
                $endOfLast   = Carbon::now()->subMonth()->endOfMonth();
                $filteredTemplates = array_filter($templates, function ($t) use ($startOfLast, $endOfLast) {
                    if (empty($t['createdAt'])) return false;
                    $c = Carbon::parse($t['createdAt']);
                    return $c->gte($startOfLast) && $c->lte($endOfLast);
                });
            } elseif ($distRange === 'this_year') {
                $startOfYear = Carbon::now()->startOfYear();
                $filteredTemplates = array_filter($templates, function ($t) use ($startOfYear) {
                    if (empty($t['createdAt'])) return false;
                    return Carbon::parse($t['createdAt'])->gte($startOfYear);
                });
            } elseif ($distRange === 'custom') {
                $startDateStr = $request->query('distributionStart');
                $endDateStr   = $request->query('distributionEnd');
                
                if ($startDateStr) {
                    $startD = Carbon::parse($startDateStr);
                    $filteredTemplates = array_filter($filteredTemplates, function ($t) use ($startD) {
                        if (empty($t['createdAt'])) return false;
                        return Carbon::parse($t['createdAt'])->gte($startD);
                    });
                }
                if ($endDateStr) {
                    $endD = Carbon::parse($endDateStr)->endOfDay();
                    $filteredTemplates = array_filter($filteredTemplates, function ($t) use ($endD) {
                        if (empty($t['createdAt'])) return false;
                        return Carbon::parse($t['createdAt'])->lte($endD);
                    });
                }
            }

            $categoryDistribution = [];
            foreach ($categories as $cat) {
                $count = count(array_filter($filteredTemplates, fn($t) => ($t['categoryId'] ?? '') === $cat['id']));
                $categoryDistribution[] = [
                    'name'  => $cat['name'] ?? 'Untitled',
                    'count' => $count,
                ];
            }

            usort($categoryDistribution, fn($a, $b) => $b['count'] - $a['count']);

            return response()->json([
                'userGrowthTrend'      => $userGrowthTrend,
                'categoryDistribution' => $categoryDistribution,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /api/analytics/subscription-summary
     */
    public function subscriptionSummary(Request $request)
    {
        try {
            $allSubs = $this->db->getAll('user_subscriptions');
            $allTxns = $this->db->getAll('transactions');

            $now = Carbon::now();

            // Group subs by userId
            $userMap = [];
            foreach ($allSubs as $sub) {
                if (empty($sub['userId'])) continue;
                $uid = $sub['userId'];
                if (!isset($userMap[$uid])) {
                    $userMap[$uid] = [];
                }
                $userMap[$uid][] = $sub;
            }

            $totalActive = 0;
            $totalCancelled = 0;
            $activeTrials = 0;
            $subscribers = [];

            foreach ($userMap as $uid => $subs) {
                usort($subs, function ($a, $b) {
                    $dateA = $a['startDate'] ?? $a['createdAt'] ?? '';
                    $dateB = $b['startDate'] ?? $b['createdAt'] ?? '';
                    return strcmp($dateB, $dateA);
                });
                $latest = $subs[0];

                $expiryDate = Carbon::parse($latest['expiryDate'] ?? 'now');

                if (!empty($latest['isActive']) && $expiryDate->gt($now)) {
                    $subscribers[] = $uid;
                    $status = strtolower($latest['status'] ?? '');
                    if ($status === 'active') {
                        $totalActive++;
                    } elseif ($status === 'trial') {
                        $activeTrials++;
                        $totalActive++;
                    } elseif ($status === 'cancelled') {
                        $totalCancelled++;
                        $totalActive++;
                    }
                }
            }

            $totalSubscribers = count(array_unique($subscribers));

            // Monthly subscription revenue
            $startOfMonth = Carbon::now()->startOfMonth();
            $monthlyRevenue = 0.0;
            foreach ($allTxns as $t) {
                $timeVal = $t['timestamp'] ?? $t['createdAt'] ?? null;
                if ($timeVal && ($t['type'] ?? '') === 'subscription' && ($t['status'] ?? '') === 'success') {
                    if (Carbon::parse($timeVal)->gte($startOfMonth)) {
                        $monthlyRevenue += (float) ($t['amount'] ?? 0);
                    }
                }
            }

            // Churn rate
            $churnRate = $totalActive > 0 ? round(($totalCancelled / $totalActive) * 100, 1) : 0.0;

            // Monthly growth trend
            $growthTrend = [];
            $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            
            $range = $request->query('subGrowthRange', '6m');
            if ($range === '6m') {
                $stepMonths = 6;
                $start = Carbon::now()->subMonths(5)->startOfMonth();
            } elseif ($range === '12m') {
                $stepMonths = 12;
                $start = Carbon::now()->subMonths(11)->startOfMonth();
            } elseif ($range === 'this_year') {
                $stepMonths = $now->month;
                $start = Carbon::now()->startOfYear();
            } elseif ($range === 'last_year') {
                $stepMonths = 12;
                $start = Carbon::now()->subYear()->startOfYear();
            } else {
                $startDateStr = $request->query('subGrowthStart');
                $endDateStr   = $request->query('subGrowthEnd');
                $start = $startDateStr ? Carbon::parse($startDateStr) : Carbon::now()->subMonths(5)->startOfMonth();
                $end   = $endDateStr ? Carbon::parse($endDateStr) : Carbon::now();
                $stepMonths = max(1, ($end->year - $start->year) * 12 + ($end->month - $start->month) + 1);
            }

            for ($i = 0; $i < $stepMonths; $i++) {
                $d = $start->copy()->addMonths($i);
                $label = $months[$d->month - 1] . " '" . substr($d->year, -2);
                $endOfMonth = $d->copy()->endOfMonth();

                $activeAsOf = 0;
                foreach ($userMap as $uid => $subs) {
                    $activeAtEnd = false;
                    foreach ($subs as $s) {
                        $sStart  = Carbon::parse($s['startDate'] ?? 'now');
                        $sExpiry = Carbon::parse($s['expiryDate'] ?? 'now');
                        
                        if ($sStart->lte($endOfMonth) && $sExpiry->gte($endOfMonth) && ($s['status'] ?? '') !== 'expired') {
                            $activeAtEnd = true;
                            break;
                        }
                    }
                    if ($activeAtEnd) {
                        $activeAsOf++;
                    }
                }

                $growthTrend[] = [
                    'month'       => $label,
                    'subscribers' => $activeAsOf,
                ];
            }

            return response()->json([
                'totalSubscribers' => $totalSubscribers,
                'activeTrials'     => $activeTrials,
                'monthlyRevenue'   => $monthlyRevenue,
                'churnRate'        => $churnRate,
                'growthTrend'      => $growthTrend,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /api/transactions/stats/summary
     */
    public function transactionSummary()
    {
        try {
            $list = $this->db->getAll('transactions');
            $successful = array_filter($list, fn($t) => ($t['status'] ?? '') === 'success');

            $totalRevenue = 0.0;
            $subscriptionRevenue = 0.0;
            $purchaseRevenue = 0.0;
            $monthlyCount = 0;
            $yearlyCount = 0;
            $singlePurchaseCount = 0;

            foreach ($successful as $t) {
                $amount = (float) ($t['amount'] ?? 0);
                $totalRevenue += $amount;

                if (($t['type'] ?? '') === 'subscription') {
                    $subscriptionRevenue += $amount;
                    if (($t['planId'] ?? '') === 'monthly') {
                        $monthlyCount++;
                    } elseif (($t['planId'] ?? '') === 'yearly') {
                        $yearlyCount++;
                    }
                } elseif (($t['type'] ?? '') === 'single_purchase') {
                    $purchaseRevenue += $amount;
                    $singlePurchaseCount++;
                }
            }

            return response()->json([
                'totalRevenue'           => $totalRevenue,
                'subscriptionRevenue'    => $subscriptionRevenue,
                'purchaseRevenue'        => $purchaseRevenue,
                'totalTransactions'      => count($list),
                'successfulTransactions' => count($successful),
                'monthlySubscriptions'   => $monthlyCount,
                'yearlySubscriptions'    => $yearlyCount,
                'singlePurchases'        => $singlePurchaseCount,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /api/transactions
     */
    public function transactionsIndex()
    {
        try {
            $list  = $this->db->getAll('transactions');
            $users = $this->db->getAll('app_users');

            $userMap = [];
            foreach ($users as $u) {
                $userMap[$u['id']] = $u['email'] ?? $u['phone'] ?? '';
            }

            $enriched = [];
            foreach ($list as $t) {
                $email = $t['userEmail'] ?? $userMap[$t['userId'] ?? ''] ?? 'anonymous';
                $t['userEmail'] = $email;
                $enriched[] = $t;
            }

            // Sort newest first
            usort($enriched, function ($a, $b) {
                $dateA = $a['createdAt'] ?? $a['timestamp'] ?? '';
                $dateB = $b['createdAt'] ?? $b['timestamp'] ?? '';
                return strcmp($dateB, $dateA);
            });

            return response()->json($enriched);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
