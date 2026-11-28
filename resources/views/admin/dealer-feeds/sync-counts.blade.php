<p class="text-sm text-slate-700">
    {{ $counts['new'] }} noi · {{ $counts['updated'] }} modificate · {{ $counts['withdrawn'] }} retrase ·
    {{ $counts['reactivated'] }} reactivate · {{ $counts['unchanged'] }} neschimbate · {{ $counts['galleries'] }} galerii de schimbat ·
    {{ $counts['missing'] }} lipsă din feed · {{ $counts['skipped'] }} omise
    @if (($counts['deactivated_missing'] ?? 0) > 0) · {{ $counts['deactivated_missing'] }} dezactivate din cauza absenței @endif
    @if (($counts['trashed_missing'] ?? 0) > 0) · {{ $counts['trashed_missing'] }} mutate în Coș după 5 zile @endif
</p>
