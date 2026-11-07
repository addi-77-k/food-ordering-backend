<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\MenuItemResource;
use App\Models\Category;
use App\Models\MenuItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminMenuItemController extends Controller
{
    public function index(Request $request): Response
    {
        $categories = Category::orderBy('name')->get();
        $query = MenuItem::with('category');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($categoryId = $request->input('category_id')) {
            if ($categoryId !== 'all') {
                $query->where('category_id', $categoryId);
            }
        }

        if ($request->has('availability') && $request->input('availability') !== 'all') {
            $available = filter_var($request->input('availability'), FILTER_VALIDATE_BOOLEAN);
            $query->where('availability', $available);
        }

        $items = $query->orderBy('name')->paginate(20)->withQueryString();

        return Inertia::render('admin/menu-items/index', [
            'items' => [
                'data' => MenuItemResource::collection($items->items())->resolve(),
                'current_page' => $items->currentPage(),
                'last_page' => $items->lastPage(),
                'per_page' => $items->perPage(),
                'total' => $items->total(),
            ],
            'categories' => $categories->map(fn ($c) => [
                'id' => (string) $c->id,
                'name' => $c->name,
                'slug' => $c->slug,
            ]),
            'filters' => [
                'search' => $request->input('search', ''),
                'category_id' => $request->input('category_id', 'all'),
                'availability' => $request->input('availability', 'all'),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'category_id' => ['required', 'string'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'price' => ['required', 'numeric', 'min:0.01'],
            'image_url' => ['nullable', 'url'],
            'availability' => ['boolean'],
            'preparation_time' => ['required', 'integer', 'min:1', 'max:180'],
        ]);

        MenuItem::create($validated);

        return back()->with('success', "Menu item '{$validated['name']}' created successfully.");
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $item = MenuItem::findOrFail($id);

        $validated = $request->validate([
            'category_id' => ['required', 'string'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'price' => ['required', 'numeric', 'min:0.01'],
            'image_url' => ['nullable', 'url'],
            'availability' => ['boolean'],
            'preparation_time' => ['required', 'integer', 'min:1', 'max:180'],
        ]);

        $item->update($validated);

        return back()->with('success', "Menu item '{$item->name}' updated successfully.");
    }

    public function toggleAvailability(string $id): RedirectResponse
    {
        $item = MenuItem::findOrFail($id);
        $newStatus = ! $item->availability;
        $item->update(['availability' => $newStatus]);

        $label = $newStatus ? 'In Stock' : 'Out of Stock';

        return back()->with('success', "'{$item->name}' marked as {$label}.");
    }

    public function destroy(string $id): RedirectResponse
    {
        $item = MenuItem::findOrFail($id);
        $name = $item->name;
        $item->delete();

        return back()->with('success', "Menu item '{$name}' deleted successfully.");
    }
}
