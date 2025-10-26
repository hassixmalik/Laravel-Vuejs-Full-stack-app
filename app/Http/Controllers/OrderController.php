<?php

namespace App\Http\Controllers;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(){
        return Inertia::render('orders/Index');
    }
        
    public function create()
    {
        $customerOptions = Customer::select('id','name')
            ->orderBy('name')->get()
            ->map(fn($c) => ['label' => $c->name, 'value' => $c->id]);

        $productOptions = Product::select('id','name')
            ->orderBy('name')->get()
            ->map(fn($p) => ['label' => $p->name, 'value' => $p->id]);

        return Inertia::render('orders/Create', [
            'customerOptions' => $customerOptions,
            'productOptions'  => $productOptions,
            'statusOptions'   => [
                ['label'=>'Draft','value'=>'draft'],
                ['label'=>'Placed','value'=>'placed'],
                ['label'=>'Cancelled','value'=>'cancelled'],
                ['label'=>'Fulfilled','value'=>'fulfilled'],
            ],
            'placedBy'        => auth()->id(),
            'placedByName'    => optional(auth()->user())->name,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_id'       => ['required','exists:customers,id'],
            'placed_by'         => ['required','exists:users,id'],
            'status'            => ['required','in:draft,placed,cancelled,fulfilled'],
            'items'             => ['required','array','min:1'],
            'items.*.product_id'=> ['required','exists:products,id'],
            'items.*.qty'       => ['required','integer','min:1'],
            // Accept client-provided line totals for now (10,3). Server will normalize to 3 dp.
            'items.*.unit_total'=> ['required','numeric','min:0'],
        ]);

        $order = DB::transaction(function () use ($data) {
            $order = Order::create([
                'customer_id' => $data['customer_id'],
                'placed_by'   => $data['placed_by'],
                'status'      => $data['status'],
                'total'       => 0, // will update below
            ]);

            // Normalize decimals to 3 dp and attach order_id
            $lines = collect($data['items'])->map(function ($it) use ($order) {
                return [
                    'order_id'   => $order->id,
                    'product_id' => $it['product_id'],
                    'qty'        => $it['qty'],
                    'unit_total' => number_format((float)$it['unit_total'], 3, '.', ''), // keep 3 dp
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })->all();

            // Bulk insert for performance
            OrderItem::insert($lines);

            // Sum total (server truth)
            $sum = OrderItem::where('order_id', $order->id)->sum('unit_total');
            $order->update(['total' => number_format((float)$sum, 3, '.', '')]);

            return $order;
        });

        return redirect()
            ->route('orders.create')
            ->with('message', "Order #{$order->id} created with ". $order->items()->count() ." item(s).");
    }
}
