<?php

namespace App\Http\Controllers;
use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Models\Product;

class InventoryController extends Controller
{
    public function index(){

        $products = Product::where('is_active', true)  // getting only active products
        ->latest()
        ->get();
        return Inertia::render('inventory/Index', compact('products'));
    }
    
    public function create(){
        return Inertia::render('inventory/Create');
    }
    
    public function store(Request $request){
        $data = $request->validate([
            'name' => 'required|string',
            'price' => 'required|numeric|min:0',
            'qty' => 'required|integer|min:0',
            'description' => 'nullable|string'
        ]);
        Product::create($data);
        return redirect()->route('inventory.index')->with('message', 'Product added successfully');
    }

    public function edit(Product $product){
        return Inertia::render('inventory/Edit', compact('product'));
    }

    public function update(Request $request, Product $product){
        $data = $request->validate([
            'name' => 'required|string',
            'price' => 'required|numeric|min:0',
            'qty' => 'required|integer|min:0',
            'description' => 'nullable|string'
        ]);

        $product->update([
            'name' => $request->input('name'),
            'price' => $request->input('price'),
            'qty' => $request->input('qty'),
            'description' => $request->input('description'),
        ]);
        return redirect()->route('inventory.index')->with('message', 'Product updated successfully');
    }

    public function destroy(Product $product)
    {
        //better to add check if product is already inactive
        if (!$product->is_active) {
            return back()->with('message', 'Product is already inactive.');
        }

        $product->update(['is_active' => false]);

        return redirect()
            ->route('inventory.index')
            ->with('message', 'Product disabled successfully');
    }

}