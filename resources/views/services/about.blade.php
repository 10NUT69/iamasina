@extends('layouts.app')

@section('title', 'Despre iaAuto.ro')
@section('meta_title', 'Despre iaAuto.ro - Platformă de anunțuri auto în România')
@section('meta_description', 'iaAuto.ro este o platformă de anunțuri auto gândită pentru cumpărători și vânzători care vor filtre clare, URL-uri curate și o experiență rapidă.')

@section('content')
    <div class="max-w-5xl mx-auto">
        <header class="mb-6 md:mb-8">
            <h1 class="text-2xl md:text-3xl lg:text-4xl font-extrabold text-gray-900 dark:text-gray-100 mb-2">
                Despre iaAuto.ro
            </h1>
            <p class="text-sm md:text-base text-gray-600 dark:text-gray-300 max-w-3xl">
                iaAuto.ro este construit ca un loc simplu și rapid pentru anunțuri auto: cauți după marcă,
                model, județ sau oraș și ajungi repede la mașinile relevante pentru tine.
            </p>
        </header>

        <section class="grid gap-4 md:gap-6 md:grid-cols-3">
            <article class="bg-white dark:bg-[#18181B] rounded-xl shadow-sm border border-gray-100 dark:border-gray-800 p-4 md:p-5">
                <h2 class="text-base md:text-lg font-bold text-gray-900 dark:text-gray-100 mb-3">
                    De ce există platforma
                </h2>
                <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed">
                    Piața auto are nevoie de filtre clare, pagini rapide și anunțuri ușor de parcurs. Am pornit
                    iaAuto.ro cu ideea de a face căutarea unei mașini mai directă, fără pași inutili.
                </p>
            </article>

            <article class="bg-white dark:bg-[#18181B] rounded-xl shadow-sm border border-gray-100 dark:border-gray-800 p-4 md:p-5">
                <h2 class="text-base md:text-lg font-bold text-gray-900 dark:text-gray-100 mb-3">
                    Pentru cumpărători
                </h2>
                <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed">
                    Poți filtra rapid după marcă, model, caroserie, combustibil, cutie, județ și oraș. Paginile sunt
                    optimizate pentru mobil, pentru că multe decizii încep direct din telefon.
                </p>
            </article>

            <article class="bg-white dark:bg-[#18181B] rounded-xl shadow-sm border border-gray-100 dark:border-gray-800 p-4 md:p-5">
                <h2 class="text-base md:text-lg font-bold text-gray-900 dark:text-gray-100 mb-3">
                    Pentru vânzători
                </h2>
                <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed">
                    Publicarea anunțului este gândită să fie curată și rapidă. Imaginile sunt procesate automat,
                    iar anunțurile primesc linkuri descriptive, utile pentru oameni și pentru motoarele de căutare.
                </p>
            </article>
        </section>

        <section class="mt-8 md:mt-10 bg-[#fff4f5] dark:bg-[#2a1013] border border-red-100 dark:border-red-900/40 rounded-xl p-4 md:p-5">
            <h2 class="text-sm md:text-base font-bold text-[#8f111a] dark:text-red-100 mb-2">
                Direcția iaAuto.ro
            </h2>
            <p class="text-xs md:text-sm text-[#7f1d1d] dark:text-red-100/90 leading-relaxed">
                Construim platforma cu accent pe viteză, URL-uri curate, experiență bună pe mobil și informații auto
                afișate cât mai clar. Scopul este simplu: să ajungi mai repede la mașina potrivită.
            </p>
        </section>
    </div>
@endsection
