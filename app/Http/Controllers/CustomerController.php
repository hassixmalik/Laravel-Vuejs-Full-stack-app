<?php

namespace App\Http\Controllers;
use Inertia\Inertia;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CustomerController extends Controller
{
    public function index(){
        $customers = Customer::latest()->get();
        return Inertia::render('customer/Index', compact('customers'));
    }

    public function create(){
        return Inertia::render('customer/Create');
    }

    public function store(Request $request){
        $data = $request->validate([
            'name'      => ['required','string','max:255'],
            'email'     => ['required','email','max:255', Rule::unique('customers', 'email')],
            'phone'     => ['required','string','max:32', Rule::unique('customers', 'phone')], // keep string for leading zeros
            'is_active' => ['required','boolean'],
            'city'      => ['nullable','string'],
            'address'   => ['nullable','string','max:1000'],
        ]);
        Customer::create($data);
        return redirect()->route('customer.index')->with('message', 'Customer added successfully');
    }

    public function edit(Customer $customer){
        return Inertia::render('customer/Edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer){
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|max:255',
            'phone' => 'required|string|min:0',
            'is_active' => 'required|boolean',
            'city' => 'nullable|string',
            'address' => 'nullable|string|max:1000',
        ]);

        $customer->update([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'phone' => $request->input('phone'),
            'is_active' => $request->input('is_active'),
            'city' => $request->input('city'),
            'address' => $request->input('address'),
        ]);
        return redirect()->route('customer.index')->with('message', 'Customer updated successfully');
    }
}
