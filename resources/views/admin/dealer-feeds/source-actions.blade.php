@php
    $count = (int) $importRun?->import_trashable;
@endphp

<div class="flex flex-wrap items-center justify-end gap-x-3 gap-y-2">
    <a href="{{ route('admin.dealer-feeds.edit', $source) }}" class="font-semibold text-slate-700 underline">Editează sursa</a>
    @if ($importRun?->import_status === 'prepared' && ! $importRun->import_waiting && $count > 0)
    <form method="POST" action="{{ route('admin.dealer-feeds.trash-stock', $source) }}"
          data-confirm-message="Muți în coș {{ $count }} anunțuri din importul #{{ $importRun->id }}? Pozele și miniaturile lor vor fi șterse."
          onsubmit="return confirm(this.dataset.confirmMessage)">
        @csrf
        @method('DELETE')
        <input type="hidden" name="run_id" value="{{ $importRun->id }}">
        <input type="hidden" name="expected_count" value="{{ $count }}">
        <button type="submit" class="rounded-md border border-red-300 px-2.5 py-1 text-sm font-semibold text-red-700 hover:bg-red-50 dark:border-red-800 dark:text-red-300 dark:hover:bg-red-950">
            Mută în coș
        </button>
    </form>
    @endif
</div>
