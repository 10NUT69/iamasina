<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;

class AdminCategoryController extends Controller
{
    // LISTA CATEGORIILOR
    public function index()
    {
        $categories = Category::withCount('services')
            ->orderBy('sort_order', 'asc')
            ->paginate(20);

        return view('admin.categories.index', compact('categories'));
    }

    // FORMULAR CREARE
    public function create()
    {
        return view('admin.categories.create');
    }

    // SALVARE CATEGORIE NOUĂ
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'sort_order' => 'nullable|integer'
        ]);

        $category = new Category();
        $category->name = $request->name;
        $category->slug = str()->slug($request->name);
        $category->sort_order = $request->sort_order ?? 0;
        $category->save();

        return redirect()->route('admin.categories.index')
            ->with('success', 'Categoria a fost adăugată.');
    }

    // FORMULAR EDITARE
    public function edit($id)
    {
        $category = Category::findOrFail($id);
        return view('admin.categories.edit', compact('category'));
    }

    // UPDATE CATEGORIE
    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'sort_order' => 'nullable|integer'
        ]);

        $category->name = $request->name;
        $category->slug = str()->slug($request->name);
        $category->sort_order = $request->sort_order ?? 0;
        $category->save();

        return redirect()->route('admin.categories.index')
            ->with('success', 'Categoria a fost actualizată.');
    }

    // ȘTERGERE CATEGORIE
    public function destroy($id)
    {
        $category = Category::findOrFail($id);

        // optional: poți bloca ștergerea dacă are anunțuri
        // if ($category->services()->count() > 0) {
        //     return back()->with('error', 'Nu poți șterge o categorie care are anunțuri.');
        // }

        $category->delete();

        return back()->with('success', 'Categoria a fost ștearsă.');
    }
}
