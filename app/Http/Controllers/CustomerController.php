<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Http\Resources\CustomerResource;
use App\Models\Customer;
use Inertia\Inertia;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('viewAny', Customer::class);

        $search = request('search');
        $query = Customer::query();
        if ($search) {
            $query->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%");
        }

        $customers = $query->orderBy('id', 'desc')->paginate(10)->withQueryString();

        return Inertia::render('Customers/Index', [
            'customers' => CustomerResource::collection($customers),
            'filters' => ['search' => $search],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', Customer::class);

        return Inertia::render('Customers/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCustomerRequest $request)
    {
        $this->authorize('create', Customer::class);

        // Customers are just business-contact records — no login is ever
        // created here. If a customer-role login is ever needed, an admin
        // creates it explicitly via Users management, same as any other user.
        Customer::create($request->validated());

        return redirect()->route('customers.index')->with('success', 'Customer created.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $customer = Customer::findOrFail($id);
        $this->authorize('view', $customer);

        return Inertia::render('Customers/Show', ['customer' => (new CustomerResource($customer))->resolve()]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $customer = Customer::findOrFail($id);
        $this->authorize('update', $customer);

        return Inertia::render('Customers/Edit', ['customer' => (new CustomerResource($customer))->resolve()]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCustomerRequest $request, string $id)
    {
        $customer = Customer::findOrFail($id);
        $this->authorize('update', $customer);

        $customer->update($request->validated());

        if ($customer->user_id && $customer->wasChanged('email')) {
            $customer->user()->update(['email' => $customer->email]);
        }

        return redirect()->route('customers.index')->with('success', 'Customer updated');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $customer = Customer::findOrFail($id);
        $this->authorize('delete', $customer);

        // deleting the customer cascades to their invoices (see invoices migration);
        // also remove their login since it has no purpose without a customer profile
        $customer->user?->delete();
        $customer->delete();

        return redirect()->route('customers.index')->with('success', 'Customer deleted');
    }
}
