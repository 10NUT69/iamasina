<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServiceDeactivationFeedback;
use Illuminate\Http\Request;

class AdminDeactivationFeedbackController extends Controller
{
    public function index(Request $request)
    {
        $baseQuery = ServiceDeactivationFeedback::query()
            ->with(['service', 'user']);

        if ($request->filled('answer')) {
            $baseQuery->where('answer', $request->string('answer')->toString());
        }

        if ($request->filled('sold_on')) {
            $baseQuery->where('sold_on', $request->string('sold_on')->toString());
        }

        if ($request->filled('completion_status')) {
            $baseQuery->where('completion_status', $request->string('completion_status')->toString());
        }

        if ($request->filled('from')) {
            $baseQuery->whereDate('deactivated_at', '>=', $request->date('from'));
        }

        if ($request->filled('to')) {
            $baseQuery->whereDate('deactivated_at', '<=', $request->date('to'));
        }

        if ($request->filled('search')) {
            $search = trim($request->string('search')->toString());

            $baseQuery->where(function ($query) use ($search) {
                $query->where('title', 'like', '%'.$search.'%')
                    ->orWhere('brand_name', 'like', '%'.$search.'%')
                    ->orWhere('model_name', 'like', '%'.$search.'%')
                    ->orWhere('user_id', is_numeric($search) ? (int) $search : -1)
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('email', 'like', '%'.$search.'%')
                            ->orWhere('name', 'like', '%'.$search.'%');
                    });
            });
        }

        $statsQuery = clone $baseQuery;
        $stats = [
            'total' => (clone $statsQuery)->count(),
            'completed' => (clone $statsQuery)->where('completion_status', 'completed')->count(),
            'sold' => (clone $statsQuery)->where('answer', 'sold')->count(),
            'iaauto' => (clone $statsQuery)->where('sold_on', 'iaauto')->count(),
            'other_site' => (clone $statsQuery)->where('sold_on', 'other_site')->count(),
            'skipped' => (clone $statsQuery)->where('completion_status', 'skipped')->count(),
        ];

        $feedback = $baseQuery
            ->latest('deactivated_at')
            ->latest('id')
            ->paginate(30)
            ->withQueryString();

        return view('admin.deactivation-feedback.index', compact('feedback', 'stats'));
    }
}
