@extends('admin.layout')

@section('content')
    @php
        $listing = $trial->listing;
        $service = $listing->service;
    @endphp

    <div class="mx-auto max-w-5xl space-y-4 px-4 py-8 sm:px-6">
        <a class="text-sm font-semibold text-slate-700 underline" href="{{ route('admin.dealer-feeds.edit', $source) }}">Înapoi la sursă</a>
        <h1 class="text-2xl font-bold text-slate-800">Probă import · ID {{ $listing->external_id }}</h1>
        <div class="space-y-3 rounded-xl border border-slate-200 bg-white p-5">
            <p class="font-semibold text-slate-800">{{ $service->title }}</p>
            <p class="text-sm text-slate-700">
                {{ match ($trial->status) {
                    'queued' => 'În coadă pentru descărcare',
                    'processing' => 'Fotografiile sunt în procesare',
                    'complete' => 'Procesare încheiată',
                    default => 'Proba nu a putut fi terminată',
                } }}
            </p>
            <p class="text-sm text-slate-700">
                {{ $trial->photo_processed }} din {{ min($trial->photo_total, \App\Support\ServiceImageStorage::MAX_IMAGES) }} poze procesate.
                @if ($trial->photo_total > \App\Support\ServiceImageStorage::MAX_IMAGES)
                    {{ $trial->photo_total - \App\Support\ServiceImageStorage::MAX_IMAGES }} poze după limita de 30 au fost omise.
                @endif
            </p>
            @foreach ($trial->messages ?? [] as $message)
                <p class="text-sm text-amber-800">{{ $message }}</p>
            @endforeach
            <div class="flex flex-wrap gap-3 text-sm font-semibold text-slate-700">
                <a class="underline" href="{{ route('admin.services.edit', $service) }}">Deschide anunțul #{{ $service->id }} în Admin</a>
                @if (in_array($trial->status, ['queued', 'processing'], true))
                    <a class="underline" href="{{ route('admin.dealer-feeds.trials.show', [$source, $trial]) }}">Actualizează starea</a>
                @else
                    <a class="underline" href="{{ route('admin.dealer-feeds.trials.review', ['source' => $source, 'external_id' => $listing->external_id]) }}">Verifică din nou</a>
                @endif
            </div>
        </div>

        @if ($service->images)
            <section class="space-y-3 rounded-xl border border-slate-200 bg-white p-5">
                <h2 class="font-semibold text-slate-800">Fotografiile salvate</h2>
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
                    @foreach ($service->images as $image)
                        <figure class="overflow-hidden rounded-md border border-slate-200">
                            <a href="{{ \App\Support\ServiceImageStorage::publicImageUrl($image) }}" target="_blank" rel="noopener">
                                <img src="{{ \App\Support\ServiceImageStorage::cardImageUrl($image) }}" alt="Fotografia {{ $loop->iteration }}" loading="lazy" class="aspect-[4/3] w-full object-cover">
                            </a>
                            <figcaption class="px-2 py-1 text-xs text-slate-600">{{ $loop->first ? 'Principală' : 'Poza '.$loop->iteration }}</figcaption>
                        </figure>
                    @endforeach
                </div>
            </section>
        @endif
    </div>
@endsection
