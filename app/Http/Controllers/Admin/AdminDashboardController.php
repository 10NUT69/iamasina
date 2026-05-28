<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Service;
use App\Models\User;
use App\Models\Visit;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    private const TRAFFIC_CACHE_MINUTES = 5;
    private const OPERATIONAL_CACHE_SECONDS = 60;

    private ?array $adminUserIds = null;

    public function index(Request $request)
    {
        $range = $this->normalizeRange($request->input('range', 'today'));
        [$startDate, $endDate] = $this->resolveDateRange($range);

        $trafficStats = Cache::remember(
            $this->trafficCacheKey($range, $startDate, $endDate),
            now()->addMinutes(self::TRAFFIC_CACHE_MINUTES),
            fn () => $this->buildTrafficStats($range, $startDate->copy(), $endDate->copy())
        );

        $operationalStats = Cache::remember(
            'admin.dashboard.operational',
            now()->addSeconds(self::OPERATIONAL_CACHE_SECONDS),
            fn () => $this->buildOperationalStats()
        );

        $onlineNow = $this->cleanVisitsQuery()
            ->where('created_at', '>=', now()->subMinutes(5))
            ->distinct('ip')
            ->count('ip');

        return view('admin.dashboard', [
            ...$operationalStats,
            ...$trafficStats,
            'onlineNow' => $onlineNow,
            'range' => $range,
        ]);
    }

    private function normalizeRange(?string $range): string
    {
        $allowedRanges = ['today', 'yesterday', '7days', '30days', 'this_month'];

        return in_array($range, $allowedRanges, true) ? $range : 'today';
    }

    private function resolveDateRange(string $range): array
    {
        return match ($range) {
            'yesterday' => [
                now()->subDay()->startOfDay(),
                now()->subDay()->endOfDay(),
            ],
            '7days' => [
                now()->subDays(6)->startOfDay(),
                now()->endOfDay(),
            ],
            '30days' => [
                now()->subDays(29)->startOfDay(),
                now()->endOfDay(),
            ],
            'this_month' => [
                now()->startOfMonth(),
                now()->endOfDay(),
            ],
            default => [
                now()->startOfDay(),
                now()->endOfDay(),
            ],
        };
    }

    private function trafficCacheKey(string $range, Carbon $startDate, Carbon $endDate): string
    {
        return sprintf(
            'admin.dashboard.traffic.%s.%s.%s',
            $range,
            $startDate->toDateString(),
            $endDate->toDateString()
        );
    }

    private function buildOperationalStats(): array
    {
        $todayStart = now()->startOfDay();
        $todayEnd = now()->endOfDay();

        $serviceStats = Service::query()
            ->whereNull('deleted_at')
            ->selectRaw('COUNT(*) as total_services')
            ->selectRaw("SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_services")
            ->selectRaw("SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_services")
            ->selectRaw(
                "SUM(CASE WHEN status = 'expired' OR (expires_at IS NOT NULL AND expires_at < ?) THEN 1 ELSE 0 END) as expired_services",
                [now()]
            )
            ->first();

        $servicesWithoutImages = Service::query()
            ->whereNull('deleted_at')
            ->where(function ($query) {
                $query->whereNull('images')
                    ->orWhere('images', '[]')
                    ->orWhereJsonLength('images', 0);
            })
            ->count();

        $userStats = User::query()
            ->selectRaw('COUNT(*) as total_users')
            ->selectRaw(
                'SUM(CASE WHEN created_at BETWEEN ? AND ? THEN 1 ELSE 0 END) as new_users_today',
                [$todayStart, $todayEnd]
            )
            ->selectRaw(
                "SUM(CASE WHEN user_type = 'dealer' AND created_at BETWEEN ? AND ? THEN 1 ELSE 0 END) as new_dealers_today",
                [$todayStart, $todayEnd]
            )
            ->first();

        return [
            'userCount' => (int) ($userStats->total_users ?? 0),
            'serviceCount' => (int) ($serviceStats->total_services ?? 0),
            'pendingServices' => (int) ($serviceStats->pending_services ?? 0),
            'expiredServices' => (int) ($serviceStats->expired_services ?? 0),
            'servicesWithoutImages' => $servicesWithoutImages,
            'newUsersToday' => (int) ($userStats->new_users_today ?? 0),
            'newDealersToday' => (int) ($userStats->new_dealers_today ?? 0),
            'newConversationsToday' => Conversation::whereBetween('created_at', [$todayStart, $todayEnd])->count(),
            'newMessagesToday' => Message::whereBetween('created_at', [$todayStart, $todayEnd])->count(),
            'activeServices' => (int) ($serviceStats->active_services ?? 0),
        ];
    }

    private function buildTrafficStats(string $range, Carbon $startDate, Carbon $endDate): array
    {
        $periodVisits = $this->cleanVisitsQuery()
            ->whereBetween('created_at', [$startDate, $endDate]);

        $periodTotals = (clone $periodVisits)
            ->selectRaw('COUNT(*) as total_visits, COUNT(DISTINCT ip) as unique_visitors')
            ->first();

        $dayComparison = $this->cleanVisitsQuery()
            ->whereBetween('created_at', [now()->subDay()->startOfDay(), now()->endOfDay()])
            ->selectRaw('DATE(created_at) as visit_date, COUNT(*) as total')
            ->groupBy('visit_date')
            ->pluck('total', 'visit_date');

        $topPages = Visit::select('url', DB::raw('COUNT(*) as total'))
            ->tap(fn ($query) => $this->applyCleanVisitFilters($query))
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('url')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        $topLocations = Visit::select('country', 'city', DB::raw('COUNT(*) as total'))
            ->tap(fn ($query) => $this->applyCleanVisitFilters($query))
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where(function ($query) {
                $query->whereNotNull('country')
                    ->orWhereNotNull('city');
            })
            ->groupBy('country', 'city')
            ->orderByDesc('total')
            ->limit(8)
            ->get()
            ->map(fn ($location) => (object) [
                'label' => $this->formatLocation($location->city, $location->country),
                'total' => (int) $location->total,
            ])
            ->filter(fn ($location) => $location->label !== null)
            ->values();

        $browsers = Visit::select('browser', DB::raw('COUNT(*) as total'))
            ->tap(fn ($query) => $this->applyCleanVisitFilters($query))
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereNotNull('browser')
            ->groupBy('browser')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        $devices = Visit::select('device', DB::raw('COUNT(*) as total'))
            ->tap(fn ($query) => $this->applyCleanVisitFilters($query))
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereNotNull('device')
            ->groupBy('device')
            ->orderByDesc('total')
            ->get();

        $trafficSourcesRaw = Visit::select('referer', DB::raw('COUNT(*) as total'))
            ->tap(fn ($query) => $this->applyCleanVisitFilters($query))
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('referer')
            ->orderByDesc('total')
            ->limit(50)
            ->get();

        $trafficSources = $trafficSourcesRaw
            ->map(fn ($source) => (object) [
                'source' => $this->formatRefererSource($source->referer),
                'total' => (int) $source->total,
            ])
            ->groupBy('source')
            ->map(fn ($group) => (object) [
                'source' => $group->first()->source,
                'total' => $group->sum('total'),
            ])
            ->sortByDesc('total')
            ->take(8)
            ->values();

        return [
            'totalVisits' => (int) ($periodTotals->total_visits ?? 0),
            'uniqueVisitors' => (int) ($periodTotals->unique_visitors ?? 0),
            'todayVisits' => (int) ($dayComparison->get(now()->toDateString(), 0)),
            'yesterdayVisits' => (int) ($dayComparison->get(now()->subDay()->toDateString(), 0)),
            'dailyStats' => $this->buildDailyStats($range, $startDate, $endDate),
            'topPages' => $topPages,
            'topLocations' => $topLocations,
            'browsers' => $browsers,
            'devices' => $devices,
            'trafficSources' => $trafficSources,
            'recentVisits' => $this->buildRecentVisits($startDate, $endDate),
        ];
    }

    private function buildDailyStats(string $range, Carbon $startDate, Carbon $endDate)
    {
        if (in_array($range, ['today', 'yesterday'], true)) {
            $hourlyRows = Visit::selectRaw('HOUR(created_at) as period_key, COUNT(*) as visits, COUNT(DISTINCT ip) as unique_ips')
                ->tap(fn ($query) => $this->applyCleanVisitFilters($query))
                ->whereBetween('created_at', [$startDate, $endDate])
                ->groupBy('period_key')
                ->orderBy('period_key')
                ->get()
                ->keyBy('period_key');

            return collect(range(0, 23))->map(function ($hour) use ($hourlyRows) {
                $item = $hourlyRows->get($hour);

                return (object) [
                    'date' => str_pad((string) $hour, 2, '0', STR_PAD_LEFT) . ':00',
                    'visits' => (int) ($item->visits ?? 0),
                    'unique_ips' => (int) ($item->unique_ips ?? 0),
                ];
            });
        }

        $dailyRows = Visit::selectRaw('DATE(created_at) as period_key, COUNT(*) as visits, COUNT(DISTINCT ip) as unique_ips')
            ->tap(fn ($query) => $this->applyCleanVisitFilters($query))
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('period_key')
            ->orderBy('period_key')
            ->get()
            ->keyBy('period_key');

        return collect(CarbonPeriod::create($startDate->copy()->startOfDay(), $endDate->copy()->startOfDay()))
            ->map(function (Carbon $date) use ($dailyRows) {
                $key = $date->toDateString();
                $item = $dailyRows->get($key);

                return (object) [
                    'date' => $key,
                    'visits' => (int) ($item->visits ?? 0),
                    'unique_ips' => (int) ($item->unique_ips ?? 0),
                ];
            });
    }

    private function buildRecentVisits(Carbon $startDate, Carbon $endDate)
    {
        $summaries = $this->cleanVisitsQuery()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereNotNull('ip')
            ->selectRaw('ip, MAX(id) as latest_visit_id, MAX(created_at) as last_seen_at, COUNT(*) as page_views')
            ->groupBy('ip')
            ->orderByDesc('last_seen_at')
            ->limit(20)
            ->get();

        $visitsById = Visit::whereIn('id', $summaries->pluck('latest_visit_id'))
            ->get()
            ->keyBy('id');

        return $summaries
            ->map(function ($summary) use ($visitsById) {
                $visit = $visitsById->get($summary->latest_visit_id);

                if (! $visit) {
                    return null;
                }

                $visit->setAttribute('page_views', (int) $summary->page_views);
                $visit->setAttribute('location_label', $this->formatLocation($visit->city, $visit->country));
                $visit->setAttribute('referer_label', $this->formatRefererSource($visit->referer));

                return $visit;
            })
            ->filter()
            ->values();
    }

    private function formatLocation(?string $city, ?string $country): ?string
    {
        $parts = [];

        foreach ([$city, $country] as $value) {
            $value = trim((string) $value);
            $normalized = strtolower($value);

            if ($value === '' || in_array($normalized, ['unknown', 'necunoscut', 'necunoscuta'], true)) {
                continue;
            }

            if (! in_array($value, $parts, true)) {
                $parts[] = $value;
            }
        }

        return $parts === [] ? null : implode(', ', $parts);
    }

    private function formatRefererSource(?string $referer): string
    {
        $referer = trim((string) $referer);

        if ($referer === '') {
            return 'Direct / Bookmark';
        }

        $host = parse_url($referer, PHP_URL_HOST) ?: $referer;
        $host = strtolower(preg_replace('/^www\./', '', trim($host)));

        return match (true) {
            str_contains($host, 'google.') => 'Google',
            str_contains($host, 'facebook.') || $host === 'fb.com' => 'Facebook',
            str_contains($host, 'instagram.') => 'Instagram',
            str_contains($host, 'tiktok.') => 'TikTok',
            str_contains($host, 'bing.') => 'Bing',
            str_contains($host, 'yahoo.') => 'Yahoo',
            default => $host,
        };
    }

    private function cleanVisitsQuery()
    {
        return $this->applyCleanVisitFilters(Visit::query());
    }

    private function applyCleanVisitFilters($query)
    {
        $exactUrls = [
            '/login',
            '/logout',
            '/register',
            '/forgot-password',
            '/reset-password',
            '/verify-email',
            '/confirm-password',
            '/dashboard',
            '/contul-meu',
        ];

        $query->whereNotIn('url', $exactUrls);

        $adminUserIds = $this->adminUserIds();
        if ($adminUserIds !== []) {
            $query->where(function ($query) use ($adminUserIds) {
                $query->whereNull('user_id')
                    ->orWhereNotIn('user_id', $adminUserIds);
            });
        }

        return $query
            ->where('url', 'not like', '/panou-secret%')
            ->where('url', 'not like', '/profile/%')
            ->where('url', 'not like', '/mesaje%')
            ->where('url', 'not like', '/mesaje/status/%')
            ->where('url', 'not like', '/api/%')
            ->where('url', 'not like', '/ajax/%');
    }

    private function adminUserIds(): array
    {
        if ($this->adminUserIds !== null) {
            return $this->adminUserIds;
        }

        $this->adminUserIds = Cache::remember(
            'admin.analytics.admin_user_ids',
            now()->addMinutes(10),
            fn () => User::where('is_admin', true)
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all()
        );

        return $this->adminUserIds;
    }
}
