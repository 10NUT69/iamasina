<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DealerFeedSource;
use App\Models\DealerFeedTrial;
use App\Models\Locality;
use App\Services\DealerFeedException;
use App\Services\DealerFeedPreview;
use App\Services\DealerFeedTrialImport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DealerFeedTrialController extends Controller
{
    public function review(Request $request, DealerFeedSource $source, DealerFeedTrialImport $import): View
    {
        $input = $request->validate([
            'external_id' => ['required', 'string', 'max:191'],
            'locality_id' => ['nullable', 'integer', 'min:1'],
        ]);
        $externalId = $input['external_id'];
        $localityId = isset($input['locality_id']) ? (int) $input['locality_id'] : null;
        $fields = DealerFeedPreview::FIELDS;
        $review = null;
        $error = null;
        try {
            $review = $import->review($source, $externalId, $localityId);
        } catch (DealerFeedException $exception) {
            $error = $exception->getMessage();
        }

        $localities = ($review['needs_location'] ?? false)
            ? Locality::query()->cities()->whereHas('county')->with('county:id,name')->orderBy('name')->get(['id', 'county_id', 'name'])
            : collect();

        return view('admin.dealer-feeds.trial-review', compact('source', 'externalId', 'review', 'fields', 'error', 'localityId', 'localities'));
    }

    public function store(Request $request, DealerFeedSource $source, DealerFeedTrialImport $import): RedirectResponse
    {
        $input = $request->validate([
            'external_id' => ['required', 'string', 'max:191'],
            'fingerprint' => ['required', 'string', 'size:64'],
            'locality_id' => ['nullable', 'integer', 'min:1'],
        ]);
        try {
            $review = $import->review($source, $input['external_id'], isset($input['locality_id']) ? (int) $input['locality_id'] : null);
            if (! hash_equals($review['fingerprint'], $input['fingerprint'])) {
                throw new DealerFeedException('Datele s-au schimbat după verificare. Revizuiește din nou anunțul.');
            }
            $trial = $import->createTrial($source, $input['external_id'], $review);
        } catch (DealerFeedException $exception) {
            return redirect()->route('admin.dealer-feeds.trials.review', [
                'source' => $source,
                'external_id' => $input['external_id'],
                'locality_id' => $input['locality_id'] ?? null,
            ])->withErrors(['trial' => $exception->getMessage()]);
        }

        return redirect()->route('admin.dealer-feeds.trials.show', [$source, $trial]);
    }

    public function show(DealerFeedSource $source, DealerFeedTrial $trial): View
    {
        $trial->load('listing.service');
        abort_unless($trial->listing?->source_id === $source->id, 404);
        $service = $trial->listing->service;
        abort_if(! $service || $service->trashed(), 404);

        return view('admin.dealer-feeds.trial-result', compact('source', 'trial'));
    }
}
