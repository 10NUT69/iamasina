<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\User;
use App\Support\ServiceImageStorage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class AdminUserController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search', ''));
        $sort = in_array($request->input('sort'), ['ads', 'user', 'dealer_tier', 'registered'], true)
            ? $request->input('sort')
            : null;
        $direction = $request->input('direction') === 'asc' ? 'asc' : 'desc';

        $query = User::with([
                'services' => function ($query) {
                    $query->withTrashed()
                        ->with(['category', 'county', 'locality', 'brandRel', 'modelRel'])
                        ->orderBy('created_at', 'desc');
                },
            ])
            ->withCount('services')
            ->withCount([
                'services as all_services_count' => fn ($query) => $query->withTrashed(),
            ])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%');

                    if (ctype_digit($search)) {
                        $query->orWhere('id', (int) $search);
                    }
                });
            });

        switch ($sort) {
            case 'ads':
                $query->orderBy('all_services_count', $direction)
                    ->orderBy('created_at', 'desc');
                break;

            case 'user':
                $query->orderBy('name', $direction)
                    ->orderBy('email', $direction)
                    ->orderBy('created_at', 'desc');
                break;

            case 'dealer_tier':
                if (Schema::hasColumn('users', 'dealer_tier')) {
                    $query->orderByRaw("CASE WHEN user_type = 'dealer' THEN 0 ELSE 1 END ASC")
                        ->orderByRaw("
                            CASE dealer_tier
                                WHEN 'standard' THEN 1
                                WHEN 'founding' THEN 2
                                WHEN 'premium' THEN 3
                                ELSE 0
                            END {$direction}
                        ")
                        ->orderBy('name', 'asc');
                } else {
                    $query->orderBy('name', $direction)
                        ->orderBy('created_at', 'desc');
                }
                break;

            case 'registered':
                $query->orderBy('created_at', $direction)
                    ->orderBy('id', $direction);
                break;

            default:
                $query->orderBy('created_at', 'desc');
        }

        $users = $query->paginate(20)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function exportEmailsWithServices()
    {
        return $this->exportEmails('with_services');
    }

    public function exportEmailsWithoutServices()
    {
        return $this->exportEmails('without_services');
    }

    public function bulkAction(Request $request)
    {
        $action = $request->input('action');
        $rawIds = $request->input('ids');

        if (empty($rawIds)) {
            return back()->with('error', 'Selectează cel puțin un utilizator.');
        }

        $ids = array_filter(explode(',', $rawIds));
        $users = User::whereIn('id', $ids)->get();
        $count = 0;

        foreach ($users as $user) {
            if ($user->id === auth()->id()) {
                continue;
            }

            switch ($action) {
                case 'activate':
                    if (Schema::hasColumn('users', 'is_active')) {
                        $user->is_active = 1;
                        $user->save();
                    }
                    $count++;
                    break;

                case 'deactivate':
                    if (Schema::hasColumn('users', 'is_active')) {
                        $user->is_active = 0;
                        $user->save();
                    }
                    $count++;
                    break;

                case 'delete':
                    $this->cleanupUserResources($user);
                    $user->delete();
                    $count++;
                    break;

                case 'dealer_tier_standard':
                case 'dealer_tier_founding':
                case 'dealer_tier_premium':
                    if (Schema::hasColumn('users', 'dealer_tier') && $user->user_type === 'dealer') {
                        $user->dealer_tier = str_replace('dealer_tier_', '', $action);
                        $user->save();
                        $count++;
                    }
                    break;
            }
        }

        if ($count === 0 && count($ids) > 0) {
            return back()->with('error', 'Nu poți efectua acțiuni asupra propriului cont.');
        }

        return back()->with('success', "Actiunea '{$this->bulkActionLabel($action)}' a fost aplicata pe {$count} utilizatori.");
    }

    public function updateDealerTier(Request $request, User $user)
    {
        if (! Schema::hasColumn('users', 'dealer_tier')) {
            return back()->with('error', 'Coloana dealer_tier lipseste din tabelul users. Ruleaza migrarile.');
        }

        if ($user->user_type !== 'dealer') {
            return back()->with('error', 'Tipul de dealer poate fi setat doar pentru conturile Parc auto.');
        }

        $validated = $request->validate([
            'dealer_tier' => ['required', Rule::in(User::DEALER_TIERS)],
        ]);

        $user->dealer_tier = $validated['dealer_tier'];
        $user->save();

        return back()->with('success', 'Tipul dealerului a fost actualizat.');
    }

    public function toggle($id)
    {
        $user = User::findOrFail($id);

        if ($user->id === auth()->id()) {
            return back()->with('error', 'Nu poți dezactiva propriul cont.');
        }

        if (!Schema::hasColumn('users', 'is_active')) {
            return back()->with('error', 'Coloana is_active lipseste din tabelul users. Ruleaza migrarile.');
        }

        $user->is_active = !$user->is_active;
        $user->save();

        return back()->with('success', 'Status utilizator actualizat.');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);

        if ($user->id === auth()->id()) {
            return back()->with('error', 'Nu poți șterge propriul cont.');
        }

        $this->cleanupUserResources($user);
        $user->delete();

        return back()->with('success', 'Utilizatorul și anunțurile sale au fost șterse.');
    }

    private function cleanupUserResources(User $user): void
    {
        $services = Service::withTrashed()->where('user_id', $user->id)->get();

        foreach ($services as $service) {
            $this->deleteServiceImages($service);
            $service->images = null;
            $service->save();
            $service->delete();
        }
    }

    private function deleteServiceImages(Service $service): void
    {
        ServiceImageStorage::deleteServiceImages($service->images);
    }

    private function bulkActionLabel(?string $action): string
    {
        return match ($action) {
            'activate' => 'Deblocheaza',
            'deactivate' => 'Blocheaza',
            'delete' => 'Sterge',
            'dealer_tier_standard' => 'Seteaza dealer Standard',
            'dealer_tier_founding' => 'Seteaza dealer Fondator',
            'dealer_tier_premium' => 'Seteaza dealer Premium',
            default => (string) $action,
        };
    }

    private function exportEmails(string $type)
    {
        $withServices = $type === 'with_services';
        $fileName = $withServices
            ? 'iaauto-emailuri-utilizatori-cu-anunturi-' . now()->format('Y-m-d-H-i-s') . '.csv'
            : 'iaauto-emailuri-utilizatori-fara-anunturi-' . now()->format('Y-m-d-H-i-s') . '.csv';

        $query = User::query()
            ->select(['id', 'name', 'email', 'created_at'])
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->withCount('services')
            ->when($withServices, fn ($query) => $query->whereHas('services'))
            ->when(!$withServices, fn ($query) => $query->doesntHave('services'))
            ->orderBy('email');

        Log::info('Admin user emails export', [
            'type' => $type,
            'admin_id' => auth()->id(),
            'admin_email' => auth()->user()?->email,
            'timestamp' => now()->toIso8601String(),
        ]);

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');

            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, ['email', 'name', 'anunturi', 'inregistrat_la']);

            foreach ($query->cursor() as $user) {
                fputcsv($handle, [
                    $user->email,
                    $user->name,
                    $user->services_count,
                    optional($user->created_at)->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
