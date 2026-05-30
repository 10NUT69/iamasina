Anunțurile cu poze primesc mai multă atenție

Salut{{ !empty($user->name) ? ', ' . $user->name : '' }},

Am observat că pe {{ $periodLabel }} ai publicat {{ $serviceCount === 1 ? 'un anunț fără poze' : $serviceCount . ' anunțuri fără poze' }} pe iaAuto.ro.

Pozele îi ajută pe cumpărători să vadă mai repede starea mașinii și cresc șansele să fii contactat.

@foreach($listingItems as $item)
- {{ $item['label'] }}@if(!empty($item['publishedAt'])) (publicat: {{ $item['publishedAt'] }})@endif
@endforeach

Adaugă poze în cont:
{{ $accountUrl }}

Recomandarea noastră este să adaugi poze clare din exterior, interior și bord. Un anunț complet inspiră mai multă încredere.

Mulțumim,
iaAuto.ro

© {{ date('Y') }} iaAuto.ro. Toate drepturile rezervate.
