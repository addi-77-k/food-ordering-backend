<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class AdminCategoryController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Category::query();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $categories = $query->orderBy('name')->get()->map(function ($c) {
            return [
                'id' => (string) $c->id,
                'name' => $c->name,
                'slug' => $c->slug,
                'description' => $c->description,
                'image_url' => $c->image_url,
                'status' => $c->status,
                'menu_items_count' => $c->menuItems()->count(),
                'created_at' => $c->created_at?->toIso8601String(),
            ];
        });

        return Inertia::render('admin/categories/index', [
            'categories' => $categories,
            'filters' => [
                'search' => $request->input('search', ''),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
            'image_url' => ['nullable', 'url'],
            'status' => ['required', 'string', 'in:active,inactive'],
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        Category::create($validated);

        return back()->with('success', "Category '{$validated['name']}' created successfully.");
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $category = Category::findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
            'image_url' => ['nullable', 'url'],
            'status' => ['required', 'string', 'in:active,inactive'],
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $category->update($validated);

        return back()->with('success', "Category '{$category->name}' updated successfully.");
    }

    public function toggleStatus(string $id): RedirectResponse
    {
        $category = Category::findOrFail($id);
        $newStatus = $category->status === 'active' ? 'inactive' : 'active';
        $category->update(['status' => $newStatus]);

        return back()->with('success', "Category '{$category->name}' marked as {$newStatus}.");
    }

    public function destroy(string $id): RedirectResponse
    {
        $category = Category::findOrFail($id);
        $name = $category->name;
        $category->delete();

        return back()->with('success', "Category '{$name}' deleted successfully.");
    }
}
