<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServiceDeactivationFeedback;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class AdminDeactivationFeedbackController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->input('period', $request->filled('from') && $request->filled('to') ? 'custom' : '30d');
        if (! in_array($period, ['today', 'yesterday', '7d', '30d', '90d', 'all', 'custom'], true)) {
            $period = '30d';
        }

        $today = now()->startOfDay();
        $from = match ($period) {
            'today' => $today->copy(),
            'yesterday' => $today->copy()->subDay(),
            '7d' => $today->copy()->subDays(6),
            '30d' => $today->copy()->subDays(29),
            '90d' => $today->copy()->subDays(89),
            default => null,
        };
        $toExclusive = $period === 'yesterday' ? $today->copy() : $today->copy()->addDay();

        if ($period === 'custom') {
            $dates = $request->validate([
                'from' => ['required', 'date_format:Y-m-d'],
                'to' => ['required', 'date_format:Y-m-d', 'after_or_equal:from'],
            ]);
            $from = Carbon::parse($dates['from'])->startOfDay();
            $toExclusive = Carbon::parse($dates['to'])->startOfDay()->addDay();
        }

        $answerFilter = $request->input('answer', 'sold');
        if (! in_array($answerFilter, ['sold', 'not_sold', 'skipped', 'all'], true)) {
            $answerFilter = 'sold';
        }

        $search = trim((string) $request->input('search', ''));
        $showExcluded = $request->boolean('show_excluded');
        $baseQuery = $this->reportingQuery($search);
        $this->applyPeriod($baseQuery, $from, $toExclusive);

        // Aceeași mulțime de rânduri alimenta separat fiecare card; o singură
        // agregare evită scanări repetate la fiecare deschidere a paginii.
        $totals = (clone $baseQuery)
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("SUM(CASE WHEN completion_status = 'completed' THEN 1 ELSE 0 END) as completed")
            ->selectRaw("SUM(CASE WHEN answer = 'sold' THEN 1 ELSE 0 END) as sold")
            ->selectRaw("SUM(CASE WHEN answer = 'not_sold' THEN 1 ELSE 0 END) as not_sold")
            ->selectRaw("SUM(CASE WHEN answer = 'sold' AND sold_on = 'iaauto' THEN 1 ELSE 0 END) as iaauto")
            ->selectRaw("SUM(CASE WHEN answer = 'sold' AND sold_on = 'other_site' THEN 1 ELSE 0 END) as other_site")
            ->selectRaw("SUM(CASE WHEN completion_status = 'skipped' THEN 1 ELSE 0 END) as skipped")
            ->selectRaw("SUM(CASE WHEN answer = 'sold' AND days_to_deactivate IS NOT NULL THEN 1 ELSE 0 END) as duration_count")
            ->selectRaw("AVG(CASE WHEN answer = 'sold' THEN days_to_deactivate END) as average_days")
            ->first();

        $stats = [
            'total' => (int) $totals->total,
            'completed' => (int) $totals->completed,
            'sold' => (int) $totals->sold,
            'not_sold' => (int) $totals->not_sold,
            'iaauto' => (int) $totals->iaauto,
            'other_site' => (int) $totals->other_site,
            'skipped' => (int) $totals->skipped,
            'average_days' => $totals->average_days,
        ];

        $stats['response_rate'] = $stats['total'] > 0
            ? round(100 * $stats['completed'] / $stats['total'], 1)
            : null;

        $durationQuery = (clone $baseQuery)->where('answer', 'sold')->whereNotNull('days_to_deactivate');
        $durationCount = (int) $totals->duration_count;
        $middleDays = $durationCount > 0
            ? (clone $durationQuery)->orderBy('days_to_deactivate')
                ->offset(intdiv($durationCount - 1, 2))
                ->limit($durationCount % 2 === 0 ? 2 : 1)
                ->pluck('days_to_deactivate')
            : collect();
        $stats['median_days'] = $middleDays->isNotEmpty() ? $middleDays->avg() : null;

        $previousSold = null;
        if ($from !== null) {
            $previousFrom = $from->copy()->subSeconds(abs((int) $from->diffInSeconds($toExclusive)));
            $previousQuery = $this->reportingQuery($search);
            $this->applyPeriod($previousQuery, $previousFrom, $from);
            $previousSold = $previousQuery->where('answer', 'sold')->count();
        }

        $chartLimited = $period === 'all'
            || ($period === 'custom' && $from !== null && abs((int) $from->diffInDays($toExclusive)) > 366);
        $chartFrom = $chartLimited
            ? $toExclusive->copy()->subDay()->startOfMonth()->subMonths(11)
            : $from;
        $dailySales = (clone $baseQuery)
            ->where('answer', 'sold')
            ->where('deactivated_at', '>=', $chartFrom)
            ->selectRaw('DATE(deactivated_at) as sale_day, COUNT(*) as total')
            ->groupByRaw('DATE(deactivated_at)')
            ->orderBy('sale_day')
            ->get();
        $chart = $this->buildChart($dailySales, $period, $chartFrom, $toExclusive);

        $topModels = (clone $baseQuery)
            ->where('answer', 'sold')
            ->whereNotNull('brand_name')
            ->whereNotNull('model_name')
            ->selectRaw('brand_name, model_name, COUNT(*) as total')
            ->groupBy('brand_name', 'model_name')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        $feedback = $showExcluded
            ? $this->reportingQuery($search, true)
            : clone $baseQuery;
        if ($showExcluded) {
            $this->applyPeriod($feedback, $from, $toExclusive);
        }
        if ($answerFilter === 'skipped') {
            $feedback->whereNull('answer')->where('completion_status', 'skipped');
        } elseif (in_array($answerFilter, ['sold', 'not_sold'], true)) {
            $feedback->where('answer', $answerFilter);
        }

        if (in_array($request->input('sold_on'), ['iaauto', 'other_site'], true)) {
            $feedback->where('sold_on', $request->input('sold_on'));
        }

        if (in_array($request->input('completion_status'), ['completed', 'skipped'], true)) {
            $feedback->where('completion_status', $request->input('completion_status'));
        }

        $feedback = $feedback->with(['service', 'user'])
            ->latest('deactivated_at')
            ->latest('id')
            ->paginate(30)
            ->withQueryString();

        return view('admin.deactivation-feedback.index', compact(
            'feedback', 'stats', 'answerFilter', 'period', 'from', 'toExclusive',
            'previousSold', 'chart', 'chartLimited', 'topModels', 'showExcluded'
        ));
    }

    public function clear(): RedirectResponse
    {
        $deleted = ServiceDeactivationFeedback::query()->delete();

        return redirect()
            ->route('admin.deactivation-feedback.index')
            ->with('success', $deleted === 1
                ? 'A fost ștearsă 1 statistică de vânzare.'
                : 'Au fost șterse ' . number_format($deleted, 0, ',', '.') . ' statistici de vânzare.');
    }

    public function toggleExclusion(ServiceDeactivationFeedback $feedback): RedirectResponse
    {
        $feedback->excluded_at = $feedback->excluded_at ? null : now();
        $feedback->save();

        return back()->with('success', $feedback->excluded_at
            ? 'Înregistrarea a fost exclusă din statistici.'
            : 'Înregistrarea a fost inclusă din nou în statistici.');
    }

    private function reportingQuery(string $search, bool $includeExcluded = false): Builder
    {
        $query = ServiceDeactivationFeedback::query()
            ->where('is_current', true)
            // O declarație veche nu mai reprezintă o vânzare curentă după reactivare.
            ->whereDoesntHave('service', function (Builder $serviceQuery) {
                $serviceQuery->where('status', 'active')->whereNull('deleted_at');
            });

        if (! $includeExcluded) {
            $query->whereNull('excluded_at');
        }

        if ($search !== '') {
            $query->where(function (Builder $query) use ($search) {
                $query->where('title', 'like', '%'.$search.'%')
                    ->orWhere('brand_name', 'like', '%'.$search.'%')
                    ->orWhere('model_name', 'like', '%'.$search.'%')
                    ->orWhere('user_id', is_numeric($search) ? (int) $search : -1)
                    ->orWhereHas('user', function (Builder $userQuery) use ($search) {
                        $userQuery->where('email', 'like', '%'.$search.'%')
                            ->orWhere('name', 'like', '%'.$search.'%');
                    });
            });
        }

        return $query;
    }

    private function applyPeriod(Builder $query, ?Carbon $from, Carbon $toExclusive): void
    {
        if ($from !== null) {
            $query->where('deactivated_at', '>=', $from);
            $query->where('deactivated_at', '<', $toExclusive);
        }
    }

    private function buildChart(Collection $dailySales, string $period, Carbon $from, Carbon $toExclusive): array
    {
        $spanDays = abs((int) $from->diffInDays($toExclusive));
        $mode = $period === 'all' || $spanDays > 180
            ? 'month'
            : ($spanDays > 31 ? 'week' : 'day');
        $buckets = [];
        $cursor = match ($mode) {
            'month' => $from->copy()->startOfMonth(),
            'week' => $from->copy()->startOfWeek(),
            default => $from->copy(),
        };

        while ($cursor->lt($toExclusive)) {
            $key = $cursor->format('Y-m-d');
            $buckets[$key] = [
                'label' => match ($mode) {
                    'month' => $cursor->translatedFormat('M Y'),
                    'week' => $cursor->format('d.m'),
                    default => $cursor->format('d.m'),
                },
                'total' => 0,
            ];
            match ($mode) {
                'month' => $cursor->addMonth(),
                'week' => $cursor->addWeek(),
                default => $cursor->addDay(),
            };
        }

        foreach ($dailySales as $day) {
            $date = Carbon::parse($day->sale_day);
            $key = match ($mode) {
                'month' => $date->startOfMonth()->format('Y-m-d'),
                'week' => $date->startOfWeek()->format('Y-m-d'),
                default => $date->format('Y-m-d'),
            };

            if (isset($buckets[$key])) {
                $buckets[$key]['total'] += (int) $day->total;
            }
        }

        return array_values($buckets);
    }
}
