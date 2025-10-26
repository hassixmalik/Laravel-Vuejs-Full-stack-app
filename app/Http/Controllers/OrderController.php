<?php

namespace App\Http\Controllers;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Order;
use Inertia\Inertia;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(){
        return Inertia::render('orders/Index');
    }
        
    public function create(){
        $customerOptions = Customer::query()
            ->select('id','name')
            ->orderBy('name')
            ->get()
            ->map(fn($c) => ['label' => $c->name, 'value' => $c->id]);

        $productOptions = Product::query()
            ->select('id','name') // keep minimal for now
            ->orderBy('name')
            ->get()
            ->map(fn($p) => ['label' => $p->name, 'value' => $p->id]);

        return Inertia::render('orders/Create', [
            'customerOptions' => $customerOptions,
            'productOptions'  => $productOptions, // we won’t POST items yet; just show UI
            'statusOptions'   => [
                ['label'=>'Draft','value'=>'draft'],
                ['label'=>'Placed','value'=>'placed'],
                ['label'=>'Cancelled','value'=>'cancelled'],
                ['label'=>'Fulfilled','value'=>'fulfilled'],
            ],
            'placedBy'=> auth()->id(),
            'placedByName'    => optional(auth()->user())->name,
        ]);
    }

    // Minimal store: create the order only (items come next step)
    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_id' => ['required','exists:customers,id'],
            'status'      => ['required','in:draft,placed,cancelled,fulfilled'],
            'placed_by'   => ['required','exists:users,id'],
        ]);

        // Start simple: total = 0 for now. We'll add order_items in the next step.
        $order = Order::create([
            'customer_id' => $data['customer_id'],
            'placed_by'   => $data['placed_by'],
            'status'      => $data['status'],
            'total'       => 0.000, // DECIMAL(10,3)
        ]);

        return redirect()->route('orders.create')->with('message', "Order #{$order->id} created. You can add items next.");
    }
}
