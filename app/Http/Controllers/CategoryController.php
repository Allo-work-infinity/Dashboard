<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    /**
     * Display a listing of categories.
     */
    public function index()
    {
        return view('categories.index'); // Toujours la page, jamais du JSON ici
    }

    public function data()
    {
        $categories = Category::latest()->get();

        $payload = $categories->map(function ($c) {
            return [
                'id'         => $c->id,
                'name'       => (string) $c->name,
                'slug'       => (string) $c->slug,
                'created_at' => optional($c->created_at)->toDateString(),
            ];
        });

        return response()->json($payload);
    }

    /**
     * Show the form for creating a new category.
     */
    
    /** 
     * Show the form for creating a new category.
     */
    public function create()
    {
        // Parents list for the dropdown
        $categories = Category::orderBy('name')->get(['id', 'name']);

        return view('categories.create', compact('categories'));
    }


    /**
     * Store a newly created category in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255', 'unique:categories,name'],
            'description' => ['nullable', 'string'],
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        Category::create($validated);

        return redirect()->route('categories.index')->with('success', 'Category created successfully.');
    }

    /**
     * Display the specified category.
     */
    public function show(Category $category)
    {
        return view('categories.show', compact('category'));
    }

    /**
     * Show the form for editing the specified category.
     */
    public function edit(Category $category)
    {
        return view('categories.edit', compact('category'));
    }

    /**
     * Update the specified category in storage.
     */
    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255', 'unique:categories,name,' . $category->id],
            'description' => ['nullable', 'string'],
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        $category->update($validated);

        return redirect()->route('categories.index')->with('success', 'Category updated successfully.');
    }

    /**
     * Remove the specified category from storage.
     */
    public function destroy(Category $category)
    {
        $category->delete();

        return redirect()->route('categories.index')->with('success', 'Category deleted successfully.');
    }
}
