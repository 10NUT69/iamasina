@extends('admin.layout')

@section('content')
<div class="max-w-[1536px] mx-auto py-8 px-4 sm:px-6 lg:px-8 bg-[#F8FAFC] min-h-screen font-sans text-slate-600">

    {{-- HEADER --}}
    <div class="flex flex-col md:flex-row justify-between items-end mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Administrare Anunțuri</h1>
            <p class="text-sm text-slate-500 mt-1">Gestionează anunțurile (Total: {{ $services->total() }})</p>
        </div>
    </div>

    {{-- SUCCESS MESSAGE --}}
    @if(session('success'))
        <div class="mb-6 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-lg flex items-center shadow-sm">
            <i class="fas fa-check-circle mr-3 text-xl"></i>
            <span class="font-bold">{{ session('success') }}</span>
        </div>
    @endif

    {{-- ERROR MESSAGE --}}
    @if(session('error'))
        <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg flex items-center shadow-sm">
            <i class="fas fa-exclamation-circle mr-3 text-xl"></i>
            <span class="font-bold">{{ session('error') }}</span>
        </div>
    @endif

    {{-- CARD PRINCIPAL --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden">

        {{-- FORMULAR BULK START --}}
        <form action="{{ route('admin.services.bulk') }}" method="POST" id="bulkForm" onsubmit="return submitBulk(event)">
            @csrf
            <input type="hidden" name="ids" id="bulkIds">

            {{-- TOOLBAR --}}
            <div class="p-4 border-b border-slate-100 bg-slate-50/50 flex flex-col sm:flex-row justify-between items-center gap-4">
                
                {{-- ACTIONS DROPDOWN --}}
                <div class="flex items-center gap-2 w-full sm:w-auto">
                    <div class="relative">
                        <select name="action" id="bulkActionSelect" class="pl-3 pr-8 py-2 bg-white border border-slate-200 text-slate-700 text-sm rounded-lg focus:ring-2 focus:ring-blue-500 w-full sm:w-64 shadow-sm cursor-pointer font-medium">
                            <option value="" disabled selected>-- Acțiuni în Masă --</option>
                            
                            <option value="activate" class="text-green-600 font-bold">✅ Activează</option>
                            <option value="deactivate" class="text-gray-600 font-bold">⏸ Dezactivează</option>
                            
                            <option disabled>──────────</option>
                            
                            <option value="soft_delete" class="text-orange-600 font-bold">📦 Mută în Coș (Soft)</option>
                            <option value="force_delete" class="text-red-600 font-black">🗑️ Șterge DEFINITIV</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="bg-slate-800 hover:bg-slate-900 text-white px-4 py-2 rounded-lg text-sm font-medium shadow-md transition-all">
                        Aplică
                    </button>
                </div>

                {{-- SEARCH & FILTER --}}
                <div class="flex gap-2 w-full sm:w-auto">
                    <select onchange="window.location.href=this.value" class="py-2 pl-3 pr-8 bg-white border border-slate-200 rounded-lg text-sm shadow-sm cursor-pointer">
                        <option value="{{ route('admin.services.index') }}">Toate</option>
                        <option value="{{ route('admin.services.index', ['status' => 'active']) }}" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="{{ route('admin.services.index', ['status' => 'inactive']) }}" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        <option value="{{ route('admin.services.index', ['status' => 'trashed']) }}" {{ request('status') == 'trashed' ? 'selected' : '' }}>Coș de Gunoi</option>
                    </select>

                    <div class="relative">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Caută..." 
                               class="pl-3 pr-10 py-2 bg-white border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 shadow-sm w-40 sm:w-64">
                        <button type="button" onclick="window.location.href='{{ route('admin.services.index') }}?search='+this.previousElementSibling.value" 
                                class="absolute inset-y-0 right-0 px-3 flex items-center text-slate-400 hover:text-blue-600">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
            </div>

            {{-- TABEL --}}
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/80 text-slate-500 text-xs uppercase tracking-wider border-b border-slate-200 font-semibold">
                            <th class="p-4 w-10 text-center"><input type="checkbox" id="selectAll" class="rounded border-slate-300 text-blue-600 w-4 h-4 cursor-pointer"></th>
                            <th class="p-4">Anunț</th>
                            <th class="p-4">Date</th>
                            <th class="p-4">Status</th>
                            <th class="p-4 text-right">Acțiuni Individuale</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse($services as $service)
                            <tr class="group hover:bg-slate-50/80 transition-colors {{ $service->trashed() ? 'bg-red-50/60' : '' }}">
                                
                                <td class="p-4 text-center">
                                    <input type="checkbox" class="rowCheck rounded border-slate-300 text-blue-600 w-4 h-4 cursor-pointer" value="{{ $service->id }}">
                                </td>

                                <td class="p-4">
                                    <div class="flex items-start gap-3">
                                        <div class="w-10 h-10 rounded-lg bg-slate-100 overflow-hidden border border-slate-200 shrink-0">
                                            <img src="{{ $service->main_image_url }}" class="w-full h-full object-cover {{ $service->trashed() ? 'grayscale' : '' }}">
                                        </div>
                                        <div>
                                            <a href="{{ $service->public_url }}" target="_blank" class="text-sm font-bold text-slate-800 hover:text-blue-600 block leading-tight {{ $service->trashed() ? 'line-through text-slate-400' : '' }}">
                                                {{ $service->title }}
                                            </a>
                                            <span class="text-xs text-slate-500">{{ $service->category->name ?? '-' }} | {{ $service->county->name ?? '-' }}</span>
                                        </div>
                                    </div>
                                </td>

                                <td class="p-4 text-xs text-slate-500 whitespace-nowrap">
                                    <div><span class="font-bold text-slate-600">Publicat:</span> {{ $service->published_at ? $service->published_at->format('d.m.Y H:i') : '-' }}</div>
                                    <div class="mt-1"><span class="font-bold text-slate-600">Actualizat:</span> {{ $service->updated_at ? $service->updated_at->format('d.m.Y H:i') : '-' }}</div>
                                </td>

                                <td class="p-4">
                                    @if($service->trashed())
                                        <span class="px-2 py-1 rounded text-[10px] font-bold bg-red-100 text-red-700 border border-red-200">ȘTERS</span>
                                    @elseif($service->status === 'active')
                                        <span class="px-2 py-1 rounded text-[10px] font-bold bg-green-100 text-green-700 border border-green-200">ACTIV</span>
                                    @else
                                        <span class="px-2 py-1 rounded text-[10px] font-bold bg-slate-100 text-slate-500 border border-slate-200">INACTIV</span>
                                    @endif
                                </td>

                                <td class="p-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        
                                        <a href="{{ route('admin.services.edit', $service->id) }}"
                                           class="p-2 border border-blue-200 text-blue-600 rounded-lg hover:bg-blue-50"
                                           title="Editeaza anunt">
                                            <i class="fas fa-pen"></i>
                                        </a>

                                        @if(!$service->trashed())
                                            {{-- Toggle --}}
                                            <button type="button" onclick="toggleService({{ $service->id }})" 
                                                    class="p-2 border rounded-lg hover:bg-slate-50 text-slate-500" title="Activează/Dezactivează">
                                                <i class="fas {{ $service->status === 'active' ? 'fa-pause' : 'fa-play' }}"></i>
                                            </button>

                                            {{-- Soft Delete (Coș) --}}
                                            <button type="button" onclick="deleteSoft({{ $service->id }})"
                                                    class="p-2 border border-orange-200 text-orange-500 rounded-lg hover:bg-orange-50" title="Mută în Coș">
                                                <i class="fas fa-archive"></i>
                                            </button>
                                        @endif

                                        {{-- Force Delete (Definitiv) --}}
                                        <button type="button" onclick="deleteForce({{ $service->id }})"
                                                class="p-2 border border-red-200 text-red-600 rounded-lg hover:bg-red-50" title="Șterge DEFINITIV">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>

                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="p-8 text-center text-slate-400">Nu există date.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="p-4">{{ $services->links() }}</div>
        </form>
    </div>
</div>

{{-- HIDDEN FORMS FOR INDIVIDUAL ACTIONS --}}
<form id="toggleForm" method="POST" style="display:none"> @csrf </form>
<form id="softDeleteForm" method="POST" style="display:none"> @csrf @method('DELETE') </form>
<form id="forceDeleteForm" method="POST" style="display:none"> @csrf @method('DELETE') <input type="hidden" name="force" value="1"> </form>

<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/js/all.min.js"></script>
<script>
    // 1. SELECT ALL
    document.getElementById('selectAll').addEventListener('change', function() {
        document.querySelectorAll('.rowCheck').forEach(cb => cb.checked = this.checked);
    });

    // 2. SUBMIT BULK
    function submitBulk(e) {
        e.preventDefault();
        const form = document.getElementById('bulkForm');
        const action = document.getElementById('bulkActionSelect').value;
        
        // Colectare ID-uri
        let selected = [];
        document.querySelectorAll('.rowCheck:checked').forEach(cb => selected.push(cb.value));

        if(selected.length === 0) { alert('Selectează cel puțin un anunț!'); return false; }
        if(!action) { alert('Alege o acțiune!'); return false; }

        if(action === 'force_delete') {
            if(!confirm('⚠️ ATENȚIE!\nȘtergerea este DEFINITIVĂ.\nContinuăm?')) return false;
        }

        document.getElementById('bulkIds').value = selected.join(',');
        form.submit();
    }

    // 3. INDIVIDUAL ACTIONS
    const toggleUrl = '{{ route("admin.services.toggle", ":id") }}';
    const deleteUrl = '{{ route("admin.services.destroy", ":id") }}';

    function toggleService(id) {
        const f = document.getElementById('toggleForm');
        f.action = toggleUrl.replace(':id', id);
        f.submit();
    }

    function deleteSoft(id) {
        if(confirm('Muți în coș (Soft Delete)?')) {
            const f = document.getElementById('softDeleteForm');
            f.action = deleteUrl.replace(':id', id);
            f.submit();
        }
    }

    function deleteForce(id) {
        if(confirm('⚠️ Ștergi DEFINITIV? Nu se mai poate recupera!')) {
            const f = document.getElementById('forceDeleteForm');
            f.action = deleteUrl.replace(':id', id);
            f.submit();
        }
    }
</script>
@endsection
