<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Models\Service;
use App\Models\Visit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminDashboardController extends Controller
{
    public function index(Request $request)
    {
        // =========================================================
        // 1. GESTIONAREA DATELOR (DATE RANGE)
        // =========================================================
        $range = $request->input('range', 'today'); // Default: 'today'
        
        $startDate = now()->startOfDay();
        $endDate   = now()->endOfDay();
        // $previousStartDate se poate folosi pentru calcule de creștere/scădere
        $previousStartDate = now()->subDay()->startOfDay(); 

        switch ($range) {
            case 'yesterday':
                $startDate = now()->subDay()->startOfDay();
                $endDate   = now()->subDay()->endOfDay();
                break;
            case '7days':
                $startDate = now()->subDays(6)->startOfDay(); // Ultimele 7 zile inclusiv azi
                $endDate   = now()->endOfDay();
                break;
            case '30days':
                $startDate = now()->subDays(29)->startOfDay();
                $endDate   = now()->endOfDay();
                break;
            case 'this_month':
                $startDate = now()->startOfMonth();
                $endDate   = now()->endOfDay();
                break;
            default: // 'today'
                $startDate = now()->startOfDay();
                $endDate   = now()->endOfDay();
                break;
        }

        // =========================================================
        // 2. KPI-uri GLOBALE (Totale)
        // =========================================================
        $userCount    = User::count();
        $serviceCount = Service::count();

        // =========================================================
        // 3. STATISTICI FILTRATE (Respectă Data Selectată)
        // =========================================================
        
        // Query de bază pentru perioada selectată, curățată de rute interne.
        $periodVisits = $this->cleanVisitsQuery()
            ->whereBetween('created_at', [$startDate, $endDate]);

        // A. Totale pe perioada aleasă
        $totalVisits    = (clone $periodVisits)->count();
        $uniqueVisitors = (clone $periodVisits)->distinct('ip')->count('ip');

        // B. Date pentru comparație (Săgețile roșii/verzi din Widget-uri)
        // Calculăm fix ziua de azi vs ziua de ieri pentru Widget-ul 1
        $todayVisits     = $this->cleanVisitsQuery()->whereDate('created_at', now()->toDateString())->count();
        $yesterdayVisits = $this->cleanVisitsQuery()->whereDate('created_at', now()->subDay()->toDateString())->count();

        // C. ONLINE ACUM (Utilizatori activi în ultimele 5 minute)
        // Independent de filtre, mereu arată realitatea curentă
        $onlineNow = $this->cleanVisitsQuery()
                          ->where('created_at', '>=', now()->subMinutes(5))
                          ->distinct('ip')
                          ->count('ip');

        // C2. OPERATIONAL: lucruri care cer atenție în admin.
        $pendingServices = Service::whereNull('deleted_at')
            ->where('status', 'pending')
            ->count();

        $expiredServices = Service::whereNull('deleted_at')
            ->where(function ($query) {
                $query->where('status', 'expired')
                    ->orWhere(function ($query) {
                        $query->whereNotNull('expires_at')
                            ->where('expires_at', '<', now());
                    });
            })
            ->count();

        $servicesWithoutImages = Service::whereNull('deleted_at')
            ->where(function ($query) {
                $query->whereNull('images')
                    ->orWhere('images', '[]')
                    ->orWhereJsonLength('images', 0);
            })
            ->count();

        $newUsersToday = User::whereDate('created_at', now()->toDateString())->count();
        $newDealersToday = User::where('user_type', 'dealer')
            ->whereDate('created_at', now()->toDateString())
            ->count();
        $newConversationsToday = Conversation::whereDate('created_at', now()->toDateString())->count();
        $newMessagesToday = Message::whereDate('created_at', now()->toDateString())->count();
        $activeServices = Service::whereNull('deleted_at')
            ->where('status', 'active')
            ->count();

        // =========================================================
        // 4. GRAFICE & TABELE (Filtrate)
        // =========================================================

        // D. Grafic Principal (Line Chart)
        // Dacă intervalul e scurt (azi/ieri), grupăm pe ore, altfel pe zile
        $groupBy = ($range == 'today' || $range == 'yesterday') ? 'hour' : 'date';
        
        if ($groupBy == 'hour') {
            $dailyStats = Visit::selectRaw('HOUR(created_at) as date, COUNT(*) as visits, COUNT(DISTINCT ip) as unique_ips')
                ->tap(fn ($query) => $this->applyCleanVisitFilters($query))
                ->whereBetween('created_at', [$startDate, $endDate])
                ->groupBy('date')
                ->orderBy('date')
                ->get()
                ->keyBy('date');

            // Zero-fill 00:00 - 23:00 ca graficul "Azi/Ieri" să nu aibă un singur punct plutitor.
            $dailyStats = collect(range(0, 23))->map(function ($hour) use ($dailyStats) {
                $item = $dailyStats->get($hour);

                return (object) [
                    'date' => str_pad((string) $hour, 2, '0', STR_PAD_LEFT) . ':00',
                    'visits' => $item?->visits ?? 0,
                    'unique_ips' => $item?->unique_ips ?? 0,
                ];
            });
        } else {
            $dailyStats = Visit::selectRaw('DATE(created_at) as date, COUNT(*) as visits, COUNT(DISTINCT ip) as unique_ips')
                ->tap(fn ($query) => $this->applyCleanVisitFilters($query))
                ->whereBetween('created_at', [$startDate, $endDate])
                ->groupBy('date')
                ->orderBy('date')
                ->get();
        }

        // E. Top Pagini
        $topPages = Visit::select('url', DB::raw('count(*) as total'))
            ->tap(fn ($query) => $this->applyCleanVisitFilters($query))
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('url')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        // F. Top Țări (Pentru Hartă și Tabel)
        // whereNotNull('country') asigură că harta nu primește date invalide
        $topCountries = Visit::select('country', DB::raw('count(*) as total'))
            ->tap(fn ($query) => $this->applyCleanVisitFilters($query))
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereNotNull('country')
            ->groupBy('country')
            ->orderByDesc('total')
            ->get(); 

        // G. Browsere
        $browsers = Visit::select('browser', DB::raw('count(*) as total'))
            ->tap(fn ($query) => $this->applyCleanVisitFilters($query))
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereNotNull('browser')
            ->groupBy('browser')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        // H. Dispozitive
        $devices = Visit::select('device', DB::raw('count(*) as total'))
            ->tap(fn ($query) => $this->applyCleanVisitFilters($query))
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereNotNull('device')
            ->groupBy('device')
            ->orderByDesc('total')
            ->get();

        // I. Surse Trafic (Referrers) - Logică PHP pentru curățare URL
        // Pas 1: Luăm datele brute
        $trafficSourcesRaw = Visit::select('referer', DB::raw('count(*) as total'))
            ->tap(fn ($query) => $this->applyCleanVisitFilters($query))
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('referer')
            ->orderByDesc('total')
            ->limit(50) // Luăm mai multe inițial pentru a avea ce grupa
            ->get();

        // Pas 2: Procesăm colecția pentru a grupa subdomenii (m.facebook vs facebook)
        $trafficSources = $trafficSourcesRaw->map(function($item) {
            if (empty($item->referer)) {
                $item->source = 'Direct / Bookmark';
            } else {
                // Extragem doar domeniul (google.com) din url complet
                $host = parse_url($item->referer, PHP_URL_HOST);
                $item->source = $host ? $host : 'Other';
            }
            return $item;
        })->groupBy('source')->map(function($group) {
            return (object) [
                'source' => $group->first()->source,
                'total' => $group->sum('total')
            ];
        })->sortByDesc('total')->take(8); // Păstrăm top 8 surse curate

        // J. Jurnal Live sumarizat: un rând per IP, ultima pagină + număr pagini în perioada aleasă.
        $recentVisitSubquery = $this->cleanVisitsQuery()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('MAX(id) as id')
            ->groupBy('ip');

        $recentVisits = Visit::query()
            ->joinSub($recentVisitSubquery, 'latest_visits', function ($join) {
                $join->on('visits.id', '=', 'latest_visits.id');
            })
            ->select('visits.*')
            ->selectSub(function ($query) use ($startDate, $endDate) {
                $this->applyCleanVisitFilters($query->from('visits as visit_counts'))
                    ->whereColumn('visit_counts.ip', 'visits.ip')
                    ->whereBetween('visit_counts.created_at', [$startDate, $endDate])
                    ->selectRaw('COUNT(*)');
            }, 'page_views')
            ->orderByDesc('visits.created_at')
            ->limit(20)
            ->get();

        // =========================================================
        // 5. RETURN VIEW
        // =========================================================
        return view('admin.dashboard', compact(
            'userCount', 'serviceCount', 
            'totalVisits', 'uniqueVisitors', 'onlineNow',
            'pendingServices', 'expiredServices', 'servicesWithoutImages',
            'newUsersToday', 'newDealersToday', 'newConversationsToday', 'newMessagesToday',
            'activeServices',
            'todayVisits', 'yesterdayVisits', 
            'dailyStats', 'topPages', 'topCountries', 
            'browsers', 'devices', 'trafficSources', 'recentVisits'
        ));
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

        return $query
            ->whereNotIn('url', $exactUrls)
            ->where(function ($query) {
                $query->whereNull('user_id')
                    ->orWhereNotIn('user_id', User::query()->select('id')->where('is_admin', true));
            })
            ->where('url', 'not like', '/panou-secret%')
            ->where('url', 'not like', '/profile/%')
            ->where('url', 'not like', '/mesaje%')
            ->where('url', 'not like', '/mesaje/status/%')
            ->where('url', 'not like', '/api/%')
            ->where('url', 'not like', '/ajax/%');
    }
}
