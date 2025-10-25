<?php

namespace App\Http\Controllers;
use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Models\Product;

class InventoryController extends Controller
{
    public function index(){
        return Inertia::render('inventory/Index');
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
}