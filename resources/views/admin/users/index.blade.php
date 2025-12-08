@extends('admin.layout')

@section('content')
<div class="max-w-[1600px] mx-auto py-8 px-4 sm:px-6 lg:px-8 bg-[#F8FAFC] min-h-screen font-sans text-slate-600">

    <div class="flex flex-col md:flex-row justify-between items-end mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Utilizatori</h1>
            <p class="text-sm text-slate-500 mt-1">Gestionează conturile înregistrate pe platformă.</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="bg-white border border-slate-200 px-3 py-1 rounded-md text-xs font-medium text-slate-500 shadow-sm">
                Total: <strong class="text-slate-800">{{ $users->total() }}</strong>
            </span>
        </div>
    </div>

    {{-- MESAJE FLASH --}}
    @if(session('success'))
        <div class="mb-6 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-lg flex items-center shadow-sm relative animate-fade-in-down">
            <i class="fas fa-check-circle mr-3 text-xl"></i>
            <div>
                <span class="font-bold">Succes!</span> {{ session('success') }}
            </div>
            <button onclick="this.parentElement.remove()" class="absolute top-3 right-3 text-emerald-400 hover:text-emerald-600"><i class="fas fa-times"></i></button>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg flex items-center shadow-sm relative animate-fade-in-down">
            <i class="fas fa-exclamation-circle mr-3 text-xl"></i>
            <div>
                <span class="font-bold">Eroare!</span> {{ session('error') }}
            </div>
            <button onclick="this.parentElement.remove()" class="absolute top-3 right-3 text-red-400 hover:text-red-600"><i class="fas fa-times"></i></button>
        </div>
    @endif

    {{-- CARD PRINCIPAL --}}
    <div class="bg-white rounded-xl shadow-[0_2px_15px_-3px_rgba(0,0,0,0.07),0_10px_20px_-2px_rgba(0,0,0,0.04)] border border-slate-100 overflow-hidden">

        <form action="{{ Route::has('admin.users.bulk') ? route('admin.users.bulk') : '#' }}" method="POST" id="bulkForm" onsubmit="return submitBulk(event)">
            @csrf
            {{-- INPUT ASCUNS PENTRU ID-URI (Trimis de JS) --}}
            <input type="hidden" name="ids" id="bulkIds"> 

            <div class="p-4 border-b border-slate-100 bg-slate-50/50 flex flex-col sm:flex-row justify-between items-center gap-4">
                
                <div class="flex items-center gap-2 w-full sm:w-auto">
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <i class="fas fa-user-cog text-xs"></i>
                        </div>
                        <select name="action" id="bulkActionSelect" class="pl-9 pr-8 py-2 bg-white border border-slate-200 text-slate-700 text-sm rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all cursor-pointer font-medium hover:border-blue-300 w-full sm:w-48 shadow-sm">
                            <option value="">Acțiuni...</option>
                            <option value="activate" class="text-green-600 font-bold">✅ Deblochează</option>
                            <option value="deactivate" class="text-orange-600 font-bold">🚫 Blochează</option>
                            <option value="delete" class="text-red-600 font-black">🗑️ Șterge</option>
                        </select>
                    </div>
                    <button type="submit" class="bg-slate-800 hover:bg-slate-900 text-white px-4 py-2 rounded-lg text-sm font-medium transition-all shadow-md active:transform active:scale-95">
                        Aplică
                    </button>
                </div>

                <div class="relative w-full sm:w-64">
                    <input type="text" placeholder="Caută utilizator..." class="w-full pl-10 pr-4 py-2 bg-white border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent shadow-sm">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <i class="fas fa-search"></i>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/80 text-slate-500 text-xs uppercase tracking-wider border-b border-slate-200 font-semibold">
                            <th class="p-4 w-10 text-center">
                                <input type="checkbox" id="selectAll" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500 cursor-pointer w-4 h-4">
                            </th>
                            <th class="p-4">Utilizator</th>
                            <th class="p-4">Rol</th>
                            <th class="p-4 text-center">Anunțuri</th>
                            <th class="p-4 text-center">Status</th>
                            <th class="p-4">Înregistrat</th>
                            <th class="p-4 text-right">Acțiuni</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse($users as $user)
                            <tr class="group hover:bg-slate-50/80 transition-colors duration-150">
                                
                                <td class="p-4 text-center">
                                    {{-- Folosim clasa rowCheck și valoarea ID-ului, dar verificăm să nu fie adminul curent --}}
                                    @if($user->id !== auth()->id())
                                        <input type="checkbox" value="{{ $user->id }}" class="rowCheck rounded border-slate-300 text-blue-600 focus:ring-blue-500 cursor-pointer w-4 h-4">
                                    @else
                                        <i class="fas fa-user-shield text-slate-300" title="Tu ești acesta"></i>
                                    @endif
                                </td>

                                {{-- User Info + Avatar --}}
                                <td class="p-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full bg-gradient-to-br {{ $user->is_admin ? 'from-purple-600 to-indigo-700' : 'from-slate-600 to-slate-800' }} text-white flex items-center justify-center text-sm font-bold shadow-md ring-2 ring-white">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </div>
                                        <div class="min-w-0">
                                            <div class="text-sm font-bold text-slate-800 truncate leading-tight">
                                                {{ $user->name }}
                                            </div>
                                            <div class="text-xs text-slate-500 truncate font-mono mt-0.5">
                                                {{ $user->email }}
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                {{-- Rol --}}
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

                                {{-- Contor Anunțuri --}}
                                <td class="p-4 text-center">
                                    @if($user->services_count > 0)
                                        <span class="inline-flex items-center justify-center min-w-[24px] h-6 px-1.5 bg-blue-50 text-blue-600 text-xs font-bold rounded-full border border-blue-100">
                                            {{ $user->services_count }}
                                        </span>
                                    @else
                                        <span class="text-slate-300 text-xs">-</span>
                                    @endif
                                </td>

                                {{-- Status --}}
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

                                {{-- Data --}}
                                <td class="p-4 text-xs text-slate-500">
                                    {{ $user->created_at->format('d M Y') }}
                                </td>

                                {{-- Acțiuni --}}
                                <td class="p-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        @if($user->id !== auth()->id())
                                            
                                            {{-- Toggle --}}
                                            <button type="button" 
                                                    onclick="toggleUser({{ $user->id }})"
                                                    class="p-2 border rounded-lg transition shadow-sm
                                                    {{ $user->is_active ? 'text-amber-500 hover:bg-amber-50 hover:border-amber-200' : 'text-emerald-600 hover:bg-emerald-50 hover:border-emerald-200' }}"
                                                    title="{{ $user->is_active ? 'Blochează accesul' : 'Deblochează accesul' }}">
                                                <i class="fas {{ $user->is_active ? 'fa-ban' : 'fa-check' }}"></i>
                                            </button>

                                            {{-- Delete --}}
                                            <button type="button" 
                                                    onclick="deleteUser({{ $user->id }})"
                                                    class="p-2 border border-red-200 text-red-600 rounded-lg hover:bg-red-50 transition shadow-sm"
                                                    title="Șterge contul">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="p-12 text-center text-slate-400">Nu există utilizatori.</td>
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
        {{-- FORMULAR BULK END --}}
    </div>
</div>

<form id="toggleForm" action="" method="POST" style="display: none;"> @csrf </form>
<form id="deleteForm" action="" method="POST" style="display: none;"> @csrf @method('DELETE') </form>

<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/js/all.min.js"></script>
<script>
    // 1. Select All
    document.getElementById('selectAll').addEventListener('change', function() {
        document.querySelectorAll('.rowCheck').forEach(cb => {
            // Nu bifăm rândul adminului
            if (cb.closest('tr').querySelector('.fa-user-shield')) return;
            cb.checked = this.checked;
        });
    });

    // 2. URL Templates
    const toggleUrlTemplate = '{{ route("admin.users.toggle", ":id") }}';
    const deleteUrlTemplate = '{{ route("admin.users.destroy", ":id") }}';

    // 3. Actions (Individuale)
    function toggleUser(id) {
        const form = document.getElementById('toggleForm');
        form.action = toggleUrlTemplate.replace(':id', id);
        form.submit();
    }

    function deleteUser(id) {
        if (confirm('⚠️ Ești sigur că vrei să ștergi acest utilizator?\n\nToate anunțurile lui vor fi șterse automat.')) {
            const form = document.getElementById('deleteForm');
            form.action = deleteUrlTemplate.replace(':id', id);
            form.submit();
        }
    }

    // 4. SUBMIT BULK (ACȚIUNI ÎN MASĂ)
    function submitBulk(e) {
        // Oprim submit-ul automat al form-ului
        e.preventDefault(); 
        
        const form = document.getElementById('bulkForm');
        const action = document.getElementById('bulkActionSelect').value;
        
        let selected = [];
        // Colectăm ID-urile bifate
        document.querySelectorAll('.rowCheck:checked').forEach(cb => selected.push(cb.value));

        // Validări
        if(selected.length === 0) { alert('Selectează cel puțin un utilizator!'); return false; }
        if(!action) { alert('Alege o acțiune!'); return false; }

        if(action === 'delete') {
            if(!confirm('⚠️ ATENȚIE!\n\nȘtergi definitiv ' + selected.length + ' utilizatori?\nAnunțurile lor vor fi șterse.')) return false;
        }

        // Punem ID-urile în input-ul ascuns (ca string "1,2,3")
        document.getElementById('bulkIds').value = selected.join(',');
        
        // Trimitem formularul
        form.submit();
        return true;
    }
</script>
@endsection