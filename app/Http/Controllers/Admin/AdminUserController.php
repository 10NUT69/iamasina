<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\User;
use App\Support\ServiceImageStorage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class AdminUserController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search', ''));
        $sort = $request->input('sort');
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

        if ($sort === 'ads') {
            $query->orderBy('all_services_count', $direction)
                ->orderBy('created_at', 'desc');
        } else {
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
            }
        }

        if ($count === 0 && count($ids) > 0) {
            return back()->with('error', 'Nu poți efectua acțiuni asupra propriului cont.');
        }

        return back()->with('success', "Actiunea '{$action}' a fost aplicata pe {$count} utilizatori.");
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
