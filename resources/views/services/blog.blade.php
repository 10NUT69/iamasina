@extends('layouts.app')

@section('title', 'Blog iaAuto.ro')
@section('meta_title', 'Blog iaAuto.ro - Ghiduri pentru cumpărători și vânzători auto')
@section('meta_description', 'Ghiduri simple despre cumpărarea unei mașini second-hand, acte de vânzare, verificări utile și publicarea unui anunț auto mai bun.')

@section('content')
    <div class="max-w-6xl mx-auto">
        <header class="mb-8">
            <p class="text-xs font-extrabold uppercase tracking-wider text-[#C81424] mb-2">Blog iaAuto.ro</p>
            <h1 class="text-3xl md:text-4xl font-extrabold text-gray-900 dark:text-gray-100 tracking-tight">
                Ghiduri auto clare, fără zgomot inutil
            </h1>
            <p class="mt-3 text-sm md:text-base text-gray-600 dark:text-gray-300 max-w-3xl">
                Aici pregătim articole practice pentru cumpărători și vânzători: verificări înainte de achiziție,
                acte necesare, fotografii mai bune pentru anunțuri și sfaturi pentru o tranzacție mai sigură.
            </p>
        </header>

        <section class="grid gap-4 md:grid-cols-3">
            @foreach([
                ['title' => 'Cum verifici o mașină înainte de cumpărare', 'text' => 'Checklist pentru acte, istoric, kilometraj, dotări și primele semne la care merită să fii atent.'],
                ['title' => 'Ce acte sunt necesare la vânzare', 'text' => 'Pașii de bază pentru o tranzacție mai curată între proprietar și cumpărător.'],
                ['title' => 'Cum faci un anunț auto care inspiră încredere', 'text' => 'Fotografii, titlu, descriere și detalii tehnice care ajută cumpărătorii să decidă mai repede.'],
            ] as $article)
                <article class="bg-white dark:bg-[#18181B] border border-gray-100 dark:border-gray-800 rounded-xl p-5 shadow-sm">
                    <h2 class="text-lg font-bold text-gray-900 dark:text-gray-100 leading-snug">
                        {{ $article['title'] }}
                    </h2>
                    <p class="mt-3 text-sm text-gray-600 dark:text-gray-300 leading-relaxed">
                        {{ $article['text'] }}
                    </p>
                    <span class="mt-5 inline-flex text-xs font-bold uppercase tracking-wide text-[#C81424]">
                        În curând
                    </span>
                </article>
            @endforeach
        </section>
    </div>
@endsection
