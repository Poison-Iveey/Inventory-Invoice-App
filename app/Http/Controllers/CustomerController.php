<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Customer;
use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $search = request('search');
        $query = Customer::query();
        if ($search) {
            $query->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%");
        }

        $customers = $query->orderBy('id','desc')->paginate(10)->withQueryString();

        return Inertia::render('Customers/Index', ['customers' => $customers, 'filters' => ['search' => $search]]);
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
        $user = $request->user();
        if (! ($user && $user->isAdmin())) {
            abort(403);
        }

        Customer::create($request->validated());
        return redirect()->route('customers.index')->with('success','Customer created');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $customer = Customer::findOrFail($id);
        return Inertia::render('Customers/Show', ['customer' => $customer]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $customer = Customer::findOrFail($id);
        return Inertia::render('Customers/Edit', ['customer' => $customer]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCustomerRequest $request, string $id)
    {
        $user = $request->user();
        if (! ($user && $user->isAdmin())) {
            abort(403);
        }

        $customer = Customer::findOrFail($id);
        $customer->update($request->validated());
        return redirect()->route('customers.index')->with('success','Customer updated');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = request()->user();
        if (! ($user && $user->isAdmin())) {
            abort(403);
        }

        $customer = Customer::findOrFail($id);
        $customer->delete();
        return redirect()->route('customers.index')->with('success','Customer deleted');
    }
}
