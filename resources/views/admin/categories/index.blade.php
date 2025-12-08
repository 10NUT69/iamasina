@extends('admin.layout')

@section('content')
<div class="max-w-[1600px] mx-auto py-8 px-4 sm:px-6 lg:px-8 bg-[#F8FAFC] min-h-screen font-sans text-slate-600">

    <!-- HEADER -->
    <div class="flex flex-col md:flex-row justify-between items-end mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Categorii</h1>
            <p class="text-sm text-slate-500 mt-1">Gestionează structura categoriilor de servicii.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.categories.create') }}" 
               class="bg-slate-800 hover:bg-slate-900 text-white px-4 py-2 rounded-lg text-sm font-medium transition-all shadow-md active:transform active:scale-95 flex items-center gap-2">
                <i class="fas fa-plus"></i> Adaugă Categorie
            </a>
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

    {{-- CARD PRINCIPAL --}}
    <div class="bg-white rounded-xl shadow-[0_2px_15px_-3px_rgba(0,0,0,0.07),0_10px_20px_-2px_rgba(0,0,0,0.04)] border border-slate-100 overflow-hidden">

        {{-- TOOLBAR --}}
        <div class="p-4 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
            <div class="text-xs text-slate-400 font-medium uppercase tracking-wider">
                Total: <strong class="text-slate-700">{{ $categories->count() }}</strong> categorii
            </div>
            
            {{-- Search Placeholder --}}
            <div class="relative w-full sm:w-64">
                <input type="text" placeholder="Caută categorie..." class="w-full pl-9 pr-4 py-1.5 bg-white border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent shadow-sm">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <i class="fas fa-search text-xs"></i>
                </div>
            </div>
        </div>

        {{-- TABEL --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/80 text-slate-500 text-xs uppercase tracking-wider border-b border-slate-200 font-semibold">
                        <th class="p-4 w-16 text-center">#</th>
                        <th class="p-4">Nume Categorie</th>
                        <th class="p-4">Slug</th>
                        <th class="p-4 text-center">Ordine</th>
                        <th class="p-4 text-center">Anunțuri</th>
                        <th class="p-4 text-right">Acțiuni</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse($categories as $category)
                        <tr class="group hover:bg-slate-50/80 transition-colors duration-150">
                            
                            {{-- ID --}}
                            <td class="p-4 text-center text-xs text-slate-400 font-mono">
                                {{ $category->id }}
                            </td>

                            {{-- Nume --}}
                            <td class="p-4">
                                <div class="flex items-center gap-3">
                                    {{-- Icon Placeholder --}}
                                    <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0">
                                        <span class="text-sm font-bold">{{ substr($category->name, 0, 1) }}</span>
                                    </div>
                                    <span class="font-bold text-slate-800 text-sm">{{ $category->name }}</span>
                                </div>
                            </td>

                            {{-- Slug --}}
                            <td class="p-4">
                                <span class="text-xs font-mono text-slate-500 bg-slate-100 px-2 py-1 rounded border border-slate-200">
                                    {{ $category->slug }}
                                </span>
                            </td>

                            {{-- Ordine --}}
                            <td class="p-4 text-center">
                                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-slate-50 border border-slate-200 text-xs font-bold text-slate-600">
                                    {{ $category->sort_order }}
                                </span>
                            </td>

                            {{-- Anunțuri (Count) --}}
                            <td class="p-4 text-center">
                                @if($category->services_count > 0)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-700 border border-blue-100">
                                        {{ $category->services_count }}
                                    </span>
                                @else
                                    <span class="text-slate-300 text-xs">-</span>
                                @endif
                            </td>

                            {{-- Acțiuni --}}
                            <td class="p-4 text-right">
                                <div class="flex items-center justify-end gap-2 opacity-80 group-hover:opacity-100 transition-opacity">
                                    
                                    {{-- Editare --}}
                                    <a href="{{ route('admin.categories.edit', $category->id) }}" 
                                       class="p-2 bg-white border border-slate-200 text-slate-500 rounded-lg hover:bg-blue-50 hover:text-blue-600 hover:border-blue-200 transition shadow-sm"
                                       title="Editează">
                                        <i class="fas fa-edit"></i>
                                    </a>

                                    {{-- Ștergere --}}
                                    <form action="{{ route('admin.categories.destroy', $category->id) }}" method="POST" class="inline-block" onsubmit="return confirm('⚠️ Ești sigur? Ștergerea unei categorii poate afecta anunțurile asociate.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="p-2 bg-white border border-slate-200 text-red-500 rounded-lg hover:bg-red-50 hover:text-red-600 hover:border-red-200 transition shadow-sm"
                                                title="Șterge">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>

                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-12 text-center">
                                <div class="flex flex-col items-center justify-center text-slate-400">
                                    <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mb-4">
                                        <i class="far fa-folder-open text-3xl"></i>
                                    </div>
                                    <p class="text-lg font-medium text-slate-600">Nu există categorii</p>
                                    <p class="text-sm">Începe prin a adăuga prima categorie.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- PAGINARE --}}
        @if($categories instanceof \Illuminate\Pagination\LengthAwarePaginator && $categories->hasPages())
        <div class="p-4 border-t border-slate-100 bg-slate-50 flex justify-center">
            {{ $categories->links() }} 
        </div>
        @endif

    </div>
</div>

{{-- SCRIPTURI --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/js/all.min.js"></script>
@endsection