<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('viewAny', Product::class);

        if (request()->user()->isCustomer()) {
            return $this->catalog();
        }

        $search = request('search');
        $status = request('status');

        $query = Product::query();
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            });
        }
        if (in_array($status, ['active', 'inactive'], true)) {
            $query->where('status', $status);
        }

        $products = $query->orderBy('id', 'desc')->paginate(10)->withQueryString();

        return Inertia::render('Products/Index', [
            'products' => ProductResource::collection($products),
            'filters' => ['search' => $search, 'status' => $status],
        ]);
    }

    /**
     * Read-only product browsing for customers — no SKU, no exact stock count,
     * only active products.
     */
    private function catalog()
    {
        $search = request('search');

        $query = Product::query()->where('status', 'active');
        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        $products = $query->orderBy('name')->paginate(12)->withQueryString();

        $products->getCollection()->transform(fn (Product $product) => [
            'id' => $product->id,
            'name' => $product->name,
            'description' => $product->description,
            'price' => (float) $product->price,
            'image_url' => $product->image ? Storage::disk('public')->url($product->image) : null,
            'in_stock' => $product->stock > 0,
        ]);

        return Inertia::render('Products/Catalog', [
            'products' => $products,
            'filters' => ['search' => $search],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', Product::class);

        return Inertia::render('Products/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductRequest $request)
    {
        $this->authorize('create', Product::class);

        $data = $request->validated();
        $data['sku'] = $this->generateSku();

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('products', 'public');
            $data['image'] = $path;
        }

        Product::create($data);

        return redirect()->route('products.index')->with('success', 'Product created');
    }

    /**
     * Generate a unique, system-assigned SKU so admins never have to invent one.
     */
    private function generateSku(): string
    {
        do {
            $sku = 'SKU-'.strtoupper(Str::random(6));
        } while (Product::where('sku', $sku)->exists());

        return $sku;
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $product = Product::findOrFail($id);
        $this->authorize('view', $product);

        return Inertia::render('Products/Show', ['product' => (new ProductResource($product))->resolve()]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $product = Product::findOrFail($id);
        $this->authorize('update', $product);

        return Inertia::render('Products/Edit', ['product' => (new ProductResource($product))->resolve()]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductRequest $request, string $id)
    {
        $product = Product::findOrFail($id);
        $this->authorize('update', $product);

        $data = $request->validated();

        if ($request->hasFile('image')) {
            // remove old image when replacing
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }

            $path = $request->file('image')->store('products', 'public');
            $data['image'] = $path;
        }

        $product->update($data);

        return redirect()->route('products.index')->with('success', 'Product updated');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $product = Product::findOrFail($id);
        $this->authorize('delete', $product);

        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();

        return redirect()->route('products.index')->with('success', 'Product deleted');
    }
}
