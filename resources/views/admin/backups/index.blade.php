@extends('admin.layout')

@section('content')
<div class="max-w-[1600px] mx-auto py-8 px-4 sm:px-6 lg:px-8 bg-[#F8FAFC] min-h-screen font-sans text-slate-600">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Backup si restaurare</h1>
        <p class="text-sm text-slate-500 mt-1 max-w-4xl">
            Exporta si restaureaza baza de date si fisierele media ale platformei iaAuto.ro. Foloseste importurile doar cand ai verificat fisierul sursa.
        </p>
    </div>

    @if(session('success'))
        <div class="mb-6 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-lg shadow-sm">
            <span class="font-bold">{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg shadow-sm">
            <span class="font-bold">{{ session('error') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg shadow-sm">
            <p class="font-bold mb-2">Verifica formularul:</p>
            <ul class="list-disc list-inside text-sm space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="mb-6 grid grid-cols-1 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-4">
            <p class="text-xs font-bold uppercase text-slate-400">Foldere media</p>
            <div class="mt-2 space-y-2">
                @foreach($mediaDirectories as $label => $path)
                    <p class="text-sm text-slate-700 break-all">
                        <span class="font-bold">{{ $label }}:</span> {{ $path }}
                    </p>
                @endforeach
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-4">
            <p class="text-xs font-bold uppercase text-slate-400">Backupuri de siguranta</p>
            <p class="mt-1 text-sm text-slate-700 break-all">{{ $safetyDirectory }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-4">
            <p class="text-xs font-bold uppercase text-slate-400">Import media mare</p>
            <p class="mt-1 text-sm text-slate-700 break-all">{{ $manualMediaImportDirectory }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-4">
            <p class="text-xs font-bold uppercase text-slate-400">Limita upload server</p>
            <p class="mt-1 text-sm text-slate-700">{{ $maxUploadSize }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        <section class="bg-white rounded-xl shadow-sm border border-slate-100 p-5">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-lg font-bold text-slate-800">Export baza de date</h2>
                    <p class="mt-2 text-sm text-slate-600 leading-relaxed">
                        Descarca un backup complet al bazei de date, compatibil cu functia de import.
                    </p>
                </div>
                <span class="rounded-full px-3 py-1 text-xs font-bold {{ $mysqldumpAvailable ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700' }}">
                    {{ $mysqldumpAvailable ? 'mysqldump disponibil' : 'mysqldump lipsa' }}
                </span>
            </div>

            @unless($mysqldumpAvailable)
                <div class="mt-4 rounded-lg border border-amber-200 bg-amber-50 p-3 text-sm text-amber-800">
                    mysqldump nu este disponibil pe server. Exportul bazei de date nu poate fi realizat automat.
                </div>
            @endunless

            <form method="POST" action="{{ route('admin.backups.database.export') }}" class="mt-5">
                @csrf
                <button type="submit"
                        class="inline-flex items-center justify-center rounded-lg bg-slate-800 px-4 py-2 text-sm font-bold text-white shadow-sm transition hover:bg-slate-900 disabled:cursor-not-allowed disabled:opacity-50"
                        @disabled(!$mysqldumpAvailable)>
                    Exporta baza de date
                </button>
            </form>
        </section>

        <section class="bg-white rounded-xl shadow-sm border border-slate-100 p-5">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-lg font-bold text-slate-800">Import baza de date</h2>
                    <p class="mt-2 text-sm text-slate-600 leading-relaxed">
                        Incarca un fisier .sql generat de exportul bazei de date.
                    </p>
                </div>
                <span class="rounded-full px-3 py-1 text-xs font-bold {{ $mysqlAvailable && $mysqldumpAvailable ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700' }}">
                    {{ $mysqlAvailable && $mysqldumpAvailable ? 'CLI disponibil' : 'CLI incomplet' }}
                </span>
            </div>

            <div class="mt-4 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700">
                <strong>Atentie:</strong> aceasta actiune poate sterge datele existente si le poate inlocui cu datele din fisierul importat.
                Inainte de import se creeaza automat un backup de siguranta.
            </div>

            @unless($mysqlAvailable && $mysqldumpAvailable)
                <div class="mt-3 rounded-lg border border-amber-200 bg-amber-50 p-3 text-sm text-amber-800">
                    Restaurarea automata necesita mysql CLI si mysqldump pe server.
                </div>
            @endunless

            <form method="POST" action="{{ route('admin.backups.database.import') }}" enctype="multipart/form-data" class="mt-5 space-y-4">
                @csrf

                <div>
                    <label for="sql_file" class="block text-sm font-bold text-slate-700 mb-1">Fisier .sql</label>
                    <input id="sql_file" name="sql_file" type="file" accept=".sql"
                           class="block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm shadow-sm focus:border-red-500 focus:ring-red-500">
                </div>

                <label class="flex items-start gap-2 text-sm text-slate-700">
                    <input type="checkbox" name="understand_db_import" value="1" class="mt-1 rounded border-slate-300 text-red-600">
                    <span>Inteleg ca aceasta actiune va sterge datele existente si va importa datele din fisier.</span>
                </label>

                <div>
                    <label for="db_confirm" class="block text-sm font-bold text-slate-700 mb-1">Confirmare</label>
                    <input id="db_confirm" name="db_confirm" type="text" placeholder="CONFIRM IMPORT"
                           class="block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm shadow-sm focus:border-red-500 focus:ring-red-500">
                </div>

                <button type="submit"
                        class="inline-flex items-center justify-center rounded-lg bg-red-600 px-4 py-2 text-sm font-bold text-white shadow-sm transition hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-50"
                        @disabled(!$mysqlAvailable || !$mysqldumpAvailable)>
                    Importa baza de date
                </button>
            </form>
        </section>

        <section class="bg-white rounded-xl shadow-sm border border-slate-100 p-5">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-lg font-bold text-slate-800">Export media</h2>
                    <p class="mt-2 text-sm text-slate-600 leading-relaxed">
                        Descarca o arhiva ZIP cu imaginile anunturilor, thumbnailurile si galeriile dealerilor.
                    </p>
                </div>
                <span class="rounded-full px-3 py-1 text-xs font-bold {{ $zipAvailable ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700' }}">
                    {{ $zipAvailable ? 'ZIP disponibil' : 'ZIP lipsa' }}
                </span>
            </div>

            @unless($zipAvailable)
                <div class="mt-4 rounded-lg border border-amber-200 bg-amber-50 p-3 text-sm text-amber-800">
                    Extensia PHP ZipArchive nu este disponibila. Exportul media nu poate fi realizat automat.
                </div>
            @endunless

            <form method="POST" action="{{ route('admin.backups.media.export') }}" class="mt-5">
                @csrf
                <button type="submit"
                        class="inline-flex items-center justify-center rounded-lg bg-slate-800 px-4 py-2 text-sm font-bold text-white shadow-sm transition hover:bg-slate-900 disabled:cursor-not-allowed disabled:opacity-50"
                        @disabled(!$zipAvailable)>
                    Exporta media
                </button>
            </form>
        </section>

        <section class="bg-white rounded-xl shadow-sm border border-slate-100 p-5">
            <div>
                <h2 class="text-lg font-bold text-slate-800">Import media</h2>
                <p class="mt-2 text-sm text-slate-600 leading-relaxed">
                    Incarca o arhiva .zip generata de exportul media sau importa o arhiva deja urcata pe server.
                </p>
            </div>

            <div class="mt-4 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700">
                <strong>Atentie:</strong> aceasta actiune poate inlocui imaginile existente.
                Inainte de import se creeaza automat un backup de siguranta.
            </div>

            <form method="POST" action="{{ route('admin.backups.media.import') }}" enctype="multipart/form-data" class="mt-5 space-y-4">
                @csrf

                <div>
                    <label for="media_file" class="block text-sm font-bold text-slate-700 mb-1">Fisier .zip</label>
                    <input id="media_file" name="media_file" type="file" accept=".zip"
                           class="block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm shadow-sm focus:border-red-500 focus:ring-red-500">
                </div>

                <label class="flex items-start gap-2 text-sm text-slate-700">
                    <input type="checkbox" name="understand_media_import" value="1" class="mt-1 rounded border-slate-300 text-red-600">
                    <span>Inteleg ca aceasta actiune poate inlocui imaginile existente.</span>
                </label>

                <div>
                    <label for="media_confirm" class="block text-sm font-bold text-slate-700 mb-1">Confirmare</label>
                    <input id="media_confirm" name="media_confirm" type="text" placeholder="CONFIRM MEDIA IMPORT"
                           class="block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm shadow-sm focus:border-red-500 focus:ring-red-500">
                </div>

                <label class="flex items-start gap-2 text-sm text-slate-700">
                    <input type="checkbox" name="delete_existing_media" value="1" class="mt-1 rounded border-slate-300 text-red-600">
                    <span>Sterge media existenta inainte de import</span>
                </label>

                <button type="submit"
                        class="inline-flex items-center justify-center rounded-lg bg-red-600 px-4 py-2 text-sm font-bold text-white shadow-sm transition hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-50"
                        @disabled(!$zipAvailable)>
                    Importa media prin upload
                </button>
            </form>

            <div class="my-6 border-t border-slate-100"></div>

            <div>
                <h3 class="text-base font-bold text-slate-800">Import media din fisier de pe server</h3>
                <p class="mt-2 text-sm text-slate-600 leading-relaxed">
                    Pentru arhive mari, urca fisierul prin SFTP/SSH in folderul de mai jos, apoi selecteaza-l aici.
                    Varianta aceasta nu depinde de limita de upload din PHP.
                </p>
                <p class="mt-2 rounded-lg bg-slate-50 border border-slate-200 px-3 py-2 text-xs text-slate-600 break-all">
                    {{ $manualMediaImportDirectory }}
                </p>
            </div>

            @if(empty($manualMediaImports))
                <div class="mt-4 rounded-lg border border-amber-200 bg-amber-50 p-3 text-sm text-amber-800">
                    Nu exista arhive .zip disponibile in folderul de import manual.
                </div>
            @else
                <form method="POST" action="{{ route('admin.backups.media.import-server') }}" class="mt-5 space-y-4">
                    @csrf

                    <div>
                        <label for="server_media_file" class="block text-sm font-bold text-slate-700 mb-1">Arhiva de pe server</label>
                        <select id="server_media_file" name="server_media_file"
                                class="block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm shadow-sm focus:border-red-500 focus:ring-red-500">
                            @foreach($manualMediaImports as $file)
                                <option value="{{ $file['name'] }}">
                                    {{ $file['name'] }} - {{ $file['size'] }} - {{ $file['modified_at'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <label class="flex items-start gap-2 text-sm text-slate-700">
                        <input type="checkbox" name="understand_server_media_import" value="1" class="mt-1 rounded border-slate-300 text-red-600">
                        <span>Inteleg ca aceasta actiune poate inlocui imaginile existente.</span>
                    </label>

                    <div>
                        <label for="server_media_confirm" class="block text-sm font-bold text-slate-700 mb-1">Confirmare</label>
                        <input id="server_media_confirm" name="server_media_confirm" type="text" placeholder="CONFIRM MEDIA IMPORT"
                               class="block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm shadow-sm focus:border-red-500 focus:ring-red-500">
                    </div>

                    <label class="flex items-start gap-2 text-sm text-slate-700">
                        <input type="checkbox" name="delete_existing_server_media" value="1" class="mt-1 rounded border-slate-300 text-red-600">
                        <span>Sterge media existenta inainte de import</span>
                    </label>

                    <button type="submit"
                            class="inline-flex items-center justify-center rounded-lg bg-red-600 px-4 py-2 text-sm font-bold text-white shadow-sm transition hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-50"
                            @disabled(!$zipAvailable)>
                        Importa media din server
                    </button>
                </form>
            @endif
        </section>
    </div>
</div>
@endsection
