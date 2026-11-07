<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class AdminCustomerController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Customer::with('orders');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            if ($status !== 'all') {
                $query->where('status', $status);
            }
        }

        $customers = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        return Inertia::render('admin/customers/index', [
            'customers' => [
                'data' => collect($customers->items())->map(function ($c) {
                    return [
                        'id' => (string) $c->id,
                        'name' => $c->name,
                        'email' => $c->email,
                        'phone' => $c->phone,
                        'address' => $c->address,
                        'status' => $c->status,
                        'orders_count' => $c->orders->count(),
                        'total_spent' => round((float) $c->orders->where('order_status', 'completed')->sum('total'), 2),
                        'orders' => OrderResource::collection($c->orders->take(5))->resolve(),
                        'created_at' => $c->created_at?->toIso8601String(),
                    ];
                }),
                'current_page' => $customers->currentPage(),
                'last_page' => $customers->lastPage(),
                'per_page' => $customers->perPage(),
                'total' => $customers->total(),
            ],
            'filters' => [
                'search' => $request->input('search', ''),
                'status' => $request->input('status', 'all'),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:customers,email'],
            'password' => ['required', 'string', 'min:6'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:500'],
            'status' => ['required', 'string', 'in:active,inactive,suspended'],
        ]);

        $validated['password'] = Hash::make($validated['password']);

        Customer::create($validated);

        return back()->with('success', "Customer '{$validated['name']}' created successfully.");
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $customer = Customer::findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:500'],
            'status' => ['required', 'string', 'in:active,inactive,suspended'],
        ]);

        $customer->update($validated);

        return back()->with('success', "Customer '{$customer->name}' updated successfully.");
    }

    public function destroy(string $id): RedirectResponse
    {
        $customer = Customer::findOrFail($id);
        $name = $customer->name;
        $customer->delete();

        return back()->with('success', "Customer '{$name}' deleted successfully.");
    }
}
