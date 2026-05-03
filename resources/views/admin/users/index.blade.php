@extends('admin.layout')

@section('content')
@php
    $adsSortActive = request('sort') === 'ads';
    $adsSortDirection = request('direction') === 'asc' ? 'asc' : 'desc';
    $nextAdsSortDirection = $adsSortActive && $adsSortDirection === 'desc' ? 'asc' : 'desc';
    $adsSortUrl = route('admin.users.index', array_merge(
        request()->except(['page', 'sort', 'direction']),
        ['sort' => 'ads', 'direction' => $nextAdsSortDirection]
    ));
@endphp

<div class="max-w-[1600px] mx-auto py-8 px-4 sm:px-6 lg:px-8 bg-[#F8FAFC] min-h-screen font-sans text-slate-600">
    <div class="flex flex-col md:flex-row justify-between items-end mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Utilizatori</h1>
            <p class="text-sm text-slate-500 mt-1">Gestioneaza conturile si anunturile lor.</p>
        </div>

        <div class="flex flex-col sm:flex-row sm:items-center gap-2 w-full md:w-auto">
            <form method="POST" action="{{ route('admin.users.export-emails.with-services') }}">
                @csrf
                <button type="submit"
                        class="w-full sm:w-auto inline-flex items-center justify-center rounded-lg bg-slate-800 px-3 py-2 text-xs font-bold text-white shadow-sm transition hover:bg-slate-900">
                    Export emailuri cu anunturi
                </button>
            </form>

            <form method="POST" action="{{ route('admin.users.export-emails.without-services') }}">
                @csrf
                <button type="submit"
                        class="w-full sm:w-auto inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-bold text-slate-700 shadow-sm transition hover:bg-slate-50">
                    Export emailuri fara anunturi
                </button>
            </form>

            <span class="bg-white border border-slate-200 px-3 py-1 rounded-md text-xs font-medium text-slate-500 shadow-sm">
                Total: <strong class="text-slate-800">{{ $users->total() }}</strong>
            </span>
        </div>
    </div>

    <div class="mb-6 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
        Exporturile includ emailurile utilizatorilor inregistrati. Foloseste listele doar conform consimtamantului si regulilor aplicabile.
    </div>

    @if(session('success'))
        <div class="mb-6 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-lg flex items-center shadow-sm relative">
            <i class="fas fa-check-circle mr-3 text-xl"></i>
            <div><span class="font-bold">Succes!</span> {{ session('success') }}</div>
            <button onclick="this.parentElement.remove()" class="absolute top-3 right-3 text-emerald-400 hover:text-emerald-600"><i class="fas fa-times"></i></button>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg flex items-center shadow-sm relative">
            <i class="fas fa-exclamation-circle mr-3 text-xl"></i>
            <div><span class="font-bold">Eroare!</span> {{ session('error') }}</div>
            <button onclick="this.parentElement.remove()" class="absolute top-3 right-3 text-red-400 hover:text-red-600"><i class="fas fa-times"></i></button>
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden">
        <form action="{{ route('admin.users.bulk') }}" method="POST" id="bulkForm" onsubmit="return submitBulk(event)">
            @csrf
            <input type="hidden" name="ids" id="bulkIds">

            <div class="p-4 border-b border-slate-100 bg-slate-50/50 flex flex-col sm:flex-row justify-between items-center gap-4">
                <div class="flex items-center gap-2 w-full sm:w-auto">
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <i class="fas fa-user-cog text-xs"></i>
                        </div>
                        <select name="action" id="bulkActionSelect" class="pl-9 pr-8 py-2 bg-white border border-slate-200 text-slate-700 text-sm rounded-lg focus:ring-2 focus:ring-blue-500 cursor-pointer font-medium w-full sm:w-48 shadow-sm">
                            <option value="">Actiuni...</option>
                            <option value="activate" class="text-green-600 font-bold">Deblocheaza</option>
                            <option value="deactivate" class="text-orange-600 font-bold">Blocheaza</option>
                            <option value="delete" class="text-red-600 font-black">Sterge</option>
                        </select>
                    </div>
                    <button type="submit" class="bg-slate-800 hover:bg-slate-900 text-white px-4 py-2 rounded-lg text-sm font-medium transition-all shadow-md">
                        Aplica
                    </button>
                </div>

                <div class="relative w-full sm:w-72">
                    <input id="userSearchInput"
                           type="text"
                           value="{{ request('search') }}"
                           placeholder="Cauta utilizator..."
                           class="w-full pl-10 pr-20 py-2 bg-white border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-sm">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <i class="fas fa-search"></i>
                    </div>
                    @if(request('search'))
                        <a href="{{ route('admin.users.index', request()->except(['page', 'search'])) }}"
                           class="absolute inset-y-0 right-10 px-2 flex items-center text-slate-400 hover:text-red-500"
                           title="Sterge cautarea">
                            <i class="fas fa-times"></i>
                        </a>
                    @endif
                    <button type="button"
                            onclick="applyUserSearch()"
                            class="absolute inset-y-0 right-0 px-3 flex items-center text-slate-400 hover:text-blue-600"
                            title="Cauta">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/80 text-slate-500 text-xs uppercase tracking-wider border-b border-slate-200 font-semibold">
                            <th class="p-4 w-10 text-center">
                                <input type="checkbox" id="selectAll" class="rounded border-slate-300 text-blue-600 cursor-pointer w-4 h-4">
                            </th>
                            <th class="p-4">Utilizator</th>
                            <th class="p-4">Rol</th>
                            <th class="p-4 text-center">
                                <a href="{{ $adsSortUrl }}" class="inline-flex items-center justify-center gap-1 hover:text-blue-600">
                                    <span>Anunturi</span>
                                    @if($adsSortActive)
                                        <i class="fas {{ $adsSortDirection === 'desc' ? 'fa-sort-amount-down' : 'fa-sort-amount-up' }} text-[11px]"></i>
                                    @else
                                        <i class="fas fa-sort text-[11px] text-slate-300"></i>
                                    @endif
                                </a>
                            </th>
                            <th class="p-4 text-center">Status</th>
                            <th class="p-4">Inregistrat</th>
                            <th class="p-4 text-right">Actiuni</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse($users as $user)
                            <tr class="group hover:bg-slate-50/80 transition-colors duration-150">
                                <td class="p-4 text-center">
                                    @if($user->id !== auth()->id())
                                        <input type="checkbox" value="{{ $user->id }}" class="rowCheck rounded border-slate-300 text-blue-600 cursor-pointer w-4 h-4">
                                    @else
                                        <i class="fas fa-user-shield text-slate-300" title="Tu esti acesta"></i>
                                    @endif
                                </td>

                                <td class="p-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full bg-gradient-to-br {{ $user->is_admin ? 'from-purple-600 to-indigo-700' : 'from-slate-600 to-slate-800' }} text-white flex items-center justify-center text-sm font-bold shadow-md ring-2 ring-white">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </div>
                                        <div class="min-w-0">
                                            <div class="text-sm font-bold text-slate-800 truncate leading-tight">{{ $user->name }}</div>
                                            <div class="text-xs text-slate-500 truncate font-mono mt-0.5">{{ $user->email }}</div>
                                        </div>
                                    </div>
                                </td>

                                <td class="p-4">
                                    @if($user->is_admin)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-100">
                                            <i class="fas fa-crown text-[10px]"></i> Admin
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-slate-100 text-slate-600 border border-slate-200">
                                            User
                                        </span>
                                    @endif
                                </td>

                                <td class="p-4 text-center">
                                    @if($user->all_services_count > 0)
                                        <button type="button"
                                                data-user-services-toggle="{{ $user->id }}"
                                                class="inline-flex items-center justify-center gap-1 min-w-[34px] h-7 px-2 bg-blue-50 text-blue-600 text-xs font-bold rounded-full border border-blue-100 hover:bg-blue-100 transition">
                                            <span>{{ $user->all_services_count }}</span>
                                            <i class="fas fa-chevron-down text-[10px] transition-transform" data-toggle-icon></i>
                                        </button>
                                    @else
                                        <span class="text-slate-300 text-xs">-</span>
                                    @endif
                                </td>

                                <td class="p-4 text-center">
                                    @if($user->is_active)
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-100">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Activ
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-rose-50 text-rose-700 border border-rose-100">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Blocat
                                        </span>
                                    @endif
                                </td>

                                <td class="p-4 text-xs text-slate-500">
                                    {{ $user->created_at->format('d M Y') }}
                                </td>

                                <td class="p-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        @if($user->id !== auth()->id())
                                            <button type="button"
                                                    onclick="toggleUser({{ $user->id }})"
                                                    class="p-2 border rounded-lg transition shadow-sm {{ $user->is_active ? 'text-amber-500 hover:bg-amber-50 hover:border-amber-200' : 'text-emerald-600 hover:bg-emerald-50 hover:border-emerald-200' }}"
                                                    title="{{ $user->is_active ? 'Blocheaza accesul' : 'Deblocheaza accesul' }}">
                                                <i class="fas {{ $user->is_active ? 'fa-ban' : 'fa-check' }}"></i>
                                            </button>

                                            <button type="button"
                                                    onclick="deleteUser({{ $user->id }})"
                                                    class="p-2 border border-red-200 text-red-600 rounded-lg hover:bg-red-50 transition shadow-sm"
                                                    title="Sterge contul">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>

                            @if($user->all_services_count > 0)
                                <tr id="userServicesRow-{{ $user->id }}" class="hidden bg-slate-50/80">
                                    <td colspan="7" class="p-0">
                                        <div class="px-4 sm:px-6 py-4 border-t border-slate-100">
                                            <div class="mb-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1">
                                                <h3 class="text-sm font-bold text-slate-700">Anunturile utilizatorului {{ $user->name }}</h3>
                                                <span class="text-xs text-slate-400">Total: {{ $user->all_services_count }}</span>
                                            </div>

                                            <div class="space-y-3">
                                                @foreach($user->services as $service)
                                                    @php
                                                        $images = $service->images;
                                                        if (is_string($images)) $images = json_decode($images, true) ?: [];
                                                        $imageCount = is_array($images) ? count(array_filter($images)) : 0;
                                                    @endphp

                                                    <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                                                        <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-4">
                                                            <div class="min-w-0">
                                                                <div class="flex flex-wrap items-center gap-2">
                                                                    <a href="{{ $service->public_url }}" target="_blank"
                                                                       class="text-sm font-bold text-slate-800 hover:text-blue-600 {{ $service->trashed() ? 'line-through text-slate-400' : '' }}">
                                                                        {{ $service->title }}
                                                                    </a>

                                                                    @if($service->trashed())
                                                                        <span class="px-2 py-1 rounded text-[10px] font-bold bg-red-100 text-red-700 border border-red-200">STERS</span>
                                                                    @elseif($service->status === 'active')
                                                                        <span class="px-2 py-1 rounded text-[10px] font-bold bg-green-100 text-green-700 border border-green-200">ACTIV</span>
                                                                    @else
                                                                        <span class="px-2 py-1 rounded text-[10px] font-bold bg-slate-100 text-slate-500 border border-slate-200">{{ strtoupper($service->status ?? 'PENDING') }}</span>
                                                                    @endif
                                                                </div>

                                                                <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-2 text-xs text-slate-500">
                                                                    <div><span class="font-bold text-slate-600">Categorie:</span> {{ $service->category->name ?? '-' }}</div>
                                                                    <div><span class="font-bold text-slate-600">Judet:</span> {{ $service->county->name ?? '-' }}</div>
                                                                    <div><span class="font-bold text-slate-600">Poze:</span> {{ $imageCount }}</div>
                                                                    <div><span class="font-bold text-slate-600">Publicat:</span> {{ $service->published_at ? $service->published_at->format('d.m.Y H:i') : '-' }}</div>
                                                                    <div><span class="font-bold text-slate-600">Actualizat:</span> {{ $service->updated_at ? $service->updated_at->format('d.m.Y H:i') : '-' }}</div>
                                                                </div>
                                                            </div>

                                                            <div class="flex items-center justify-start xl:justify-end gap-2 shrink-0">
                                                                <a href="{{ route('admin.services.edit', $service->id) }}"
                                                                   class="p-2 border border-blue-200 text-blue-600 rounded-lg hover:bg-blue-50"
                                                                   title="Editeaza anunt">
                                                                    <i class="fas fa-pen"></i>
                                                                </a>

                                                                @if(!$service->trashed())
                                                                    <button type="button"
                                                                            onclick="toggleAdminService({{ $service->id }})"
                                                                            class="p-2 border rounded-lg hover:bg-slate-50 text-slate-500"
                                                                            title="Activeaza / dezactiveaza">
                                                                        <i class="fas {{ $service->status === 'active' ? 'fa-pause' : 'fa-play' }}"></i>
                                                                    </button>

                                                                    <button type="button"
                                                                            onclick="softDeleteAdminService({{ $service->id }})"
                                                                            class="p-2 border border-orange-200 text-orange-500 rounded-lg hover:bg-orange-50"
                                                                            title="Muta in cos">
                                                                        <i class="fas fa-archive"></i>
                                                                    </button>
                                                                @endif

                                                                <button type="button"
                                                                        onclick="forceDeleteAdminService({{ $service->id }})"
                                                                        class="p-2 border border-red-200 text-red-600 rounded-lg hover:bg-red-50"
                                                                        title="Sterge definitiv">
                                                                    <i class="fas fa-trash-alt"></i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endif
                        @empty
                            <tr>
                                <td colspan="7" class="p-12 text-center text-slate-400">Nu exista utilizatori.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($users->hasPages())
                <div class="p-4 border-t border-slate-100 bg-slate-50 flex justify-center">
                    {{ $users->links() }}
                </div>
            @endif
        </form>
    </div>
</div>

<form id="toggleForm" action="" method="POST" style="display: none;"> @csrf </form>
<form id="deleteForm" action="" method="POST" style="display: none;"> @csrf @method('DELETE') </form>
<form id="serviceToggleForm" action="" method="POST" style="display: none;"> @csrf </form>
<form id="serviceSoftDeleteForm" action="" method="POST" style="display: none;"> @csrf @method('DELETE') </form>
<form id="serviceForceDeleteForm" action="" method="POST" style="display: none;"> @csrf @method('DELETE') <input type="hidden" name="force" value="1"> </form>

<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/js/all.min.js"></script>
<script>
    document.getElementById('selectAll').addEventListener('change', function() {
        document.querySelectorAll('.rowCheck').forEach(cb => cb.checked = this.checked);
    });

    document.querySelectorAll('[data-user-services-toggle]').forEach(button => {
        button.addEventListener('click', function() {
            const userId = this.getAttribute('data-user-services-toggle');
            const row = document.getElementById('userServicesRow-' + userId);
            if (!row) return;

            row.classList.toggle('hidden');
            const icon = this.querySelector('[data-toggle-icon]');
            if (icon) icon.classList.toggle('rotate-180', !row.classList.contains('hidden'));
        });
    });

    const toggleUrlTemplate = '{{ route("admin.users.toggle", ":id") }}';
    const deleteUrlTemplate = '{{ route("admin.users.destroy", ":id") }}';
    const serviceToggleUrlTemplate = '{{ route("admin.services.toggle", ":id") }}';
    const serviceDeleteUrlTemplate = '{{ route("admin.services.destroy", ":id") }}';

    function toggleUser(id) {
        const form = document.getElementById('toggleForm');
        form.action = toggleUrlTemplate.replace(':id', id);
        form.submit();
    }

    function deleteUser(id) {
        if (confirm('Stergi acest utilizator? Anunturile lui vor fi sterse automat.')) {
            const form = document.getElementById('deleteForm');
            form.action = deleteUrlTemplate.replace(':id', id);
            form.submit();
        }
    }

    function toggleAdminService(id) {
        const form = document.getElementById('serviceToggleForm');
        form.action = serviceToggleUrlTemplate.replace(':id', id);
        form.submit();
    }

    function softDeleteAdminService(id) {
        if (confirm('Muti acest anunt in cos?')) {
            const form = document.getElementById('serviceSoftDeleteForm');
            form.action = serviceDeleteUrlTemplate.replace(':id', id);
            form.submit();
        }
    }

    function forceDeleteAdminService(id) {
        if (confirm('Stergi definitiv acest anunt? Actiunea nu se mai poate recupera.')) {
            const form = document.getElementById('serviceForceDeleteForm');
            form.action = serviceDeleteUrlTemplate.replace(':id', id);
            form.submit();
        }
    }

    const usersIndexUrl = '{{ route("admin.users.index") }}';
    const userSearchInput = document.getElementById('userSearchInput');

    function applyUserSearch() {
        const params = new URLSearchParams(window.location.search);
        const value = userSearchInput ? userSearchInput.value.trim() : '';

        params.delete('page');
        value ? params.set('search', value) : params.delete('search');

        const query = params.toString();
        window.location.href = usersIndexUrl + (query ? '?' + query : '');
    }

    if (userSearchInput) {
        userSearchInput.addEventListener('keydown', function(event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                applyUserSearch();
            }
        });
    }

    function submitBulk(e) {
        e.preventDefault();

        const form = document.getElementById('bulkForm');
        const action = document.getElementById('bulkActionSelect').value;
        const selected = Array.from(document.querySelectorAll('.rowCheck:checked')).map(cb => cb.value);

        if (selected.length === 0) { alert('Selecteaza cel putin un utilizator.'); return false; }
        if (!action) { alert('Alege o actiune.'); return false; }

        if (action === 'delete' && !confirm('Stergi definitiv ' + selected.length + ' utilizatori?')) {
            return false;
        }

        document.getElementById('bulkIds').value = selected.join(',');
        form.submit();
        return true;
    }
</script>
@endsection
