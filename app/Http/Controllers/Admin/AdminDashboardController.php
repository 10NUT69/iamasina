<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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
        
        // Query de bază pentru perioada selectată
        $periodVisits = Visit::whereBetween('created_at', [$startDate, $endDate]);

        // A. Totale pe perioada aleasă
        $totalVisits    = (clone $periodVisits)->count();
        $uniqueVisitors = (clone $periodVisits)->distinct('ip')->count('ip');

        // B. Date pentru comparație (Săgețile roșii/verzi din Widget-uri)
        // Calculăm fix ziua de azi vs ziua de ieri pentru Widget-ul 1
        $todayVisits     = Visit::whereDate('created_at', now()->toDateString())->count();
        $yesterdayVisits = Visit::whereDate('created_at', now()->subDay()->toDateString())->count();

        // C. ONLINE ACUM (Utilizatori activi în ultimele 5 minute)
        // Independent de filtre, mereu arată realitatea curentă
        $onlineNow = Visit::where('created_at', '>=', now()->subMinutes(5))
                          ->distinct('ip')
                          ->count('ip');

        // =========================================================
        // 4. GRAFICE & TABELE (Filtrate)
        // =========================================================

        // D. Grafic Principal (Line Chart)
        // Dacă intervalul e scurt (azi/ieri), grupăm pe ore, altfel pe zile
        $groupBy = ($range == 'today' || $range == 'yesterday') ? 'hour' : 'date';
        
        if ($groupBy == 'hour') {
            $dailyStats = Visit::selectRaw('HOUR(created_at) as date, COUNT(*) as visits, COUNT(DISTINCT ip) as unique_ips')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->groupBy('date')
                ->orderBy('date')
                ->get()
                // Formatăm ora pentru afișare (ex: "14" devine "14:00") pentru a fi detectată corect în JS
                ->map(function ($item) {
                    $item->date = $item->date . ':00'; 
                    return $item;
                });
        } else {
            $dailyStats = Visit::selectRaw('DATE(created_at) as date, COUNT(*) as visits, COUNT(DISTINCT ip) as unique_ips')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->groupBy('date')
                ->orderBy('date')
                ->get();
        }

        // E. Top Pagini
        $topPages = Visit::select('url', DB::raw('count(*) as total'))
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('url')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        // F. Top Țări (Pentru Hartă și Tabel)
        // whereNotNull('country') asigură că harta nu primește date invalide
        $topCountries = Visit::select('country', DB::raw('count(*) as total'))
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereNotNull('country')
            ->groupBy('country')
            ->orderByDesc('total')
            ->get(); 

        // G. Browsere
        $browsers = Visit::select('browser', DB::raw('count(*) as total'))
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereNotNull('browser')
            ->groupBy('browser')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        // H. Dispozitive
        $devices = Visit::select('device', DB::raw('count(*) as total'))
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereNotNull('device')
            ->groupBy('device')
            ->orderByDesc('total')
            ->get();

        // I. Surse Trafic (Referrers) - Logică PHP pentru curățare URL
        // Pas 1: Luăm datele brute
        $trafficSourcesRaw = Visit::select('referer', DB::raw('count(*) as total'))
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

        // J. Jurnal Live (Ultimii 20 vizitatori)
        $recentVisits = Visit::latest()->limit(20)->get();

        // =========================================================
        // 5. RETURN VIEW
        // =========================================================
        return view('admin.dashboard', compact(
            'userCount', 'serviceCount', 
            'totalVisits', 'uniqueVisitors', 'onlineNow',
            'todayVisits', 'yesterdayVisits', 
            'dailyStats', 'topPages', 'topCountries', 
            'browsers', 'devices', 'trafficSources', 'recentVisits'
        ));
    }
}