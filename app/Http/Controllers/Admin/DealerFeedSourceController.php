<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DealerFeedRun;
use App\Models\DealerFeedSource;
use App\Models\Locality;
use App\Models\User;
use App\Services\DealerFeedCheck;
use App\Services\DealerFeedException;
use App\Services\DealerFeedImport;
use App\Services\DealerFeedPreview;
use App\Services\DealerFeedSimulation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DealerFeedSourceController extends Controller
{
    public const AUTOMATIC_MAPPING = '@auto';

    public function index(): View
    {
        $sources = DealerFeedSource::query()->with('dealer:id,name,company_name')
            ->latest('id')->paginate(15, ['*'], 'surse')->withQueryString();
        $runs = DealerFeedRun::query()->successful()->withImportProgress()->with(['source:id,dealer_id,name', 'source.dealer:id,name,company_name'])
            ->latest('id')->paginate(15, ['*'], 'verificari')->withQueryString();
        $dealers = $this->dealers();
        $profiles = config('dealer-feeds.profiles');

        return view('admin.dealer-feeds.index', compact('sources', 'runs', 'dealers', 'profiles'));
    }

    public function create(): Response
    {
        return $this->form(new DealerFeedSource());
    }

    public function edit(Request $request, DealerFeedSource $source, DealerFeedSimulation $simulation): Response
    {
        if (! $request->boolean('simuleaza')) {
            return $this->form($source);
        }

        $report = null;
        $simulationError = null;
        try {
            $report = $simulation->run($source);
        } catch (DealerFeedException $exception) {
            $simulationError = $exception->getMessage();
        }

        return $this->form($source, compact('report', 'simulationError'));
    }

    public function store(Request $request, DealerFeedCheck $checks): RedirectResponse
    {
        return $this->save($request, new DealerFeedSource(), $checks);
    }

    public function update(Request $request, DealerFeedSource $source, DealerFeedCheck $checks, DealerFeedSimulation $simulation): RedirectResponse
    {
        if ($request->has('location_choices')) {
            return $this->saveLocations($request, $source, $simulation);
        }

        return $this->save($request, $source, $checks);
    }

    private function saveLocations(Request $request, DealerFeedSource $source, DealerFeedSimulation $simulation): RedirectResponse
    {
        $input = $request->validate([
            'run_id' => ['required', 'integer', 'min:1'],
            'fingerprint' => ['required', 'regex:/\A[a-f0-9]{64}\z/'],
            'location_choices' => ['required', 'json'],
        ]);
        $redirect = redirect()->route('admin.dealer-feeds.edit', ['source' => $source, 'simuleaza' => 1])->withFragment('simulare-import');
        $choices = json_decode($input['location_choices'], true);
        $validator = Validator::make(['choices' => $choices], [
            'choices' => ['present', 'array'],
            'choices.*' => ['nullable', 'integer', 'min:1'],
        ]);
        if ($validator->fails()) {
            return $redirect->withErrors(['locations' => 'Alege localitățile din catalog.']);
        }

        try {
            DB::transaction(function () use ($source, $input, $choices, $simulation) {
                $source = DealerFeedSource::query()->lockForUpdate()->findOrFail($source->id);
                $report = $simulation->run($source);
                if ($report['run_id'] !== (int) $input['run_id'] || ! hash_equals($report['fingerprint'], $input['fingerprint'])
                    || array_diff_key($choices, $report['locations'] + $report['branches'])) {
                    throw new DealerFeedException('Simularea s-a schimbat. Verifică din nou locațiile afișate.');
                }

                $ids = array_unique(array_filter($choices));
                if (Locality::query()->cities()->whereHas('county')->whereKey($ids)->count() !== count($ids)) {
                    throw new DealerFeedException('Alege localitățile din catalog.');
                }
                $saved = $source->location_overrides ?? [];
                foreach ($choices as $key => $id) {
                    if ($id === null) {
                        unset($saved[$key]);
                    } else {
                        $saved[$key] = (int) $id;
                    }
                }
                if ($choices !== []) {
                    ksort($saved);
                    $source->forceFill(['location_overrides' => $saved ?: null])->save();
                }
            });
        } catch (DealerFeedException $exception) {
            return $redirect->withErrors(['locations' => $exception->getMessage()]);
        }

        return $redirect->with('status', 'Locațiile selectate au fost salvate.');
    }

    public function check(DealerFeedSource $source, DealerFeedCheck $checks): RedirectResponse
    {
        $error = $checks->run($source);
        $redirect = redirect()->route('admin.dealer-feeds.edit', $source)->withFragment('previzualizare-feed');

        return $error ? $redirect->withErrors(['feed' => $error]) : $redirect;
    }

    public function import(Request $request, DealerFeedSource $source, DealerFeedImport $import): RedirectResponse
    {
        $confirmed = $request->validate([
            'run_id' => ['required', 'integer', 'min:1'],
            'fingerprint' => ['required', 'regex:/\A[a-f0-9]{64}\z/'],
        ]);
        $redirect = redirect()->route('admin.dealer-feeds.edit', $source)->withFragment('status-import');
        try {
            $import->queue($source, (int) $confirmed['run_id'], $confirmed['fingerprint']);
        } catch (DealerFeedException $exception) {
            return $redirect->withErrors(['import' => $exception->getMessage()]);
        }

        return $redirect->with('status', 'Lucrarea a fost trimisă către worker.');
    }

    private function save(Request $request, DealerFeedSource $source, DealerFeedCheck $checks): RedirectResponse
    {
        $knownColumns = array_unique(array_merge([self::AUTOMATIC_MAPPING], $source->last_check['columns'] ?? [], array_values($source->mapping ?? [])));
        $validator = Validator::make($request->all(), [
            'dealer_id' => $source->exists
                ? ['sometimes', 'integer', Rule::in([$source->dealer_id])]
                : ['required', 'integer', Rule::exists('users', 'id')->where('user_type', 'dealer')],
            'name' => ['nullable', 'string', 'max:120'],
            'url' => ['required', 'string', 'url:https', 'max:4096'],
            'profile' => ['sometimes', 'required', Rule::in(array_keys(config('dealer-feeds.profiles')))],
            'mapping' => ['sometimes', 'array:'.implode(',', array_keys(DealerFeedPreview::FIELDS))],
            'mapping.*' => ['nullable', 'string', 'max:255', Rule::in($knownColumns)],
        ], attributes: ['url' => 'URL feed', 'dealer_id' => 'dealer', 'name' => 'numele sursei']);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput($request->only(['dealer_id', 'name', 'profile', 'mapping']));
        }

        $data = $validator->validated();
        $data['name'] ??= $source->name ?? mb_substr((string) parse_url($data['url'], PHP_URL_HOST), 0, 120);
        if (isset($data['mapping'])) {
            $overrides = array_filter($data['mapping'], fn ($value) => $value !== self::AUTOMATIC_MAPPING);
            $data['mapping'] = array_map(fn ($value) => $value ?? '', $overrides) ?: null;
        }

        $creating = ! $source->exists;
        $startCheck = $creating && $request->boolean('test_feed');
        $source = DB::transaction(function () use ($source, $data, $creating, $checks) {
            if ($creating) {
                $dealer = User::query()->lockForUpdate()->findOrFail($data['dealer_id']);
                abort_unless($dealer->user_type === 'dealer', 422);
                $existing = $dealer->dealerFeedSource()->first();
                if ($existing) {
                    return $existing;
                }
            } else {
                $source = DealerFeedSource::query()->lockForUpdate()->findOrFail($source->id);
                unset($data['dealer_id']);
            }

            $source->fill($data);
            if ($source->exists && $source->isDirty('profile')) {
                $source->mapping = null;
            }
            if ($source->isDirty(['url', 'profile'])) {
                $preview = $source->runs()->whereNull('import_status')->find($source->last_check['run_id'] ?? 0);
                if ($preview) {
                    $checks->discardFile($preview, $source, deleteRun: true);
                }
                $source->last_checked_at = null;
                $source->last_check = null;
            } elseif ($source->isDirty('mapping') && isset($source->last_check['run_id'], $source->last_check['item_count'])) {
                $checks->refreshMapping($source);
            }
            $source->save();

            return $source;
        });

        if ($creating && ! $source->wasRecentlyCreated && (
            ! $startCheck || $source->url !== $data['url'] || $source->profile !== ($data['profile'] ?? DealerFeedSource::DEFAULT_PROFILE)
        )) {
            return redirect()->route('admin.dealer-feeds.edit', $source)->withErrors([
                'dealer_id' => 'Dealerul are deja o sursă. Modifică linkul sau modelul aici.',
            ]);
        }

        if ($startCheck) {
            return $this->check($source, $checks);
        }

        return redirect()->route('admin.dealer-feeds.edit', $source)->with('status', 'Sursa a fost salvată.');
    }

    private function form(DealerFeedSource $source, array $simulation = []): Response
    {
        $dealers = $source->exists ? collect() : $this->dealers();
        $fields = DealerFeedPreview::FIELDS;
        $profiles = config('dealer-feeds.profiles');
        $automaticMapping = self::AUTOMATIC_MAPPING;
        $latestImport = $source->exists
            ? $source->runs()->whereNotNull('import_status')->withImportProgress()->latest('id')->first()
            : null;
        $failedItems = $latestImport?->importedListings()->where('import_status', 'failed')
            ->orderBy('id')->paginate(20, ['id', 'external_id', 'import_error'], 'erori')->fragment('status-import');
        $galleryUpdates = $latestImport?->sync_summary !== null
            ? $source->listings()->where('sync_gallery->run_id', $latestImport->id)->get(['external_id', 'sync_gallery'])
            : collect();
        $galleryWaiting = $galleryUpdates->whereIn('sync_gallery.status', ['waiting', 'processing'])->count();
        $galleryFailed = $galleryUpdates->where('sync_gallery.status', 'failed');
        $locationCatalog = collect();
        if (! empty($simulation['report']['locations']) || ! empty($simulation['report']['branches'])) {
            $locationCatalog = Locality::query()->cities()->whereHas('county')->with('county:id,name')
                ->orderBy('name')->get(['id', 'county_id', 'name'])->groupBy('county_id')
                ->map(fn ($cities) => [
                    'name' => $cities->first()->county->name,
                    'cities' => $cities->map(fn (Locality $city) => [$city->id, $city->name])->all(),
                ])->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE);
        }
        $trials = $source->exists
            ? $source->trials()
                ->whereHas('listing.service', fn (Builder $query) => $query->withoutTrashed())
                ->with('listing')->latest('dealer_feed_trials.updated_at')->limit(10)->get()
            : collect();

        return response()->view('admin.dealer-feeds.form', compact(
            'source', 'dealers', 'fields', 'trials', 'profiles', 'automaticMapping', 'simulation', 'locationCatalog', 'latestImport', 'failedItems',
            'galleryWaiting', 'galleryFailed',
        ))
            ->header('Cache-Control', 'no-store');
    }

    private function dealers()
    {
        return User::query()->select('id', 'name')->where('user_type', 'dealer')->orderBy('name')->get();
    }
}
