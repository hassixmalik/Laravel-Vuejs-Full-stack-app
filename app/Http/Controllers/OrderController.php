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
        $orders = Order::query()
        ->with(['customer:id,name', 'placedBy:id,name'])
        ->withCount('items')
        ->latest()
        ->get(['id','customer_id','placed_by','status','total','created_at']);

        // Shape data for the view
        $rows = $orders->map(function ($o) {
            return [
                'id'          => $o->id,
                'customer'    => optional($o->customer)->name,
                'placed_by'   => optional($o->placedBy)->name,
                'status'      => $o->status,                      
                'total'       => number_format((float)$o->total, 3, '.', ''),
                'created_at'  => $o->created_at->format('Y-m-d H:i'),
                'items_count' => $o->items_count,                 // not shown yet, but might get need of it in future so i am adding it here
            ];
        });

        return Inertia::render('orders/Index', [
            'orders' => $rows,
        ]);
        
    }
        
    public function create()
    {
        
        $customerOptions = Customer::query()
            ->where('is_active', true)        //only active customers      
            ->orderBy('name')
            ->get(['id','name'])
            ->map(fn($c) => ['label' => $c->name, 'value' => $c->id]);


        $productOptions = Product::query()
            ->where('is_active', true)       // only active products
            ->orderBy('name')
            ->get(['id', 'name', 'price', 'qty'])
            ->map(fn($p) => [
                'label' => $p->name,
                'value' => $p->id,
                'price' => (float) $p->price,
                'stock' => (int) $p->qty,
            ]);

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
            'items.*.unit_total'=> ['required','numeric','min:0'],
        ]);

        $order = DB::transaction(function () use ($data) {
            $order = Order::create([
                'customer_id' => $data['customer_id'],
                'placed_by'   => $data['placed_by'],
                'status'      => $data['status'],
                'total'       => 0, // will update below
            ]);

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

            if (in_array($order->status, ['placed','fulfilled', 'draft'], true)) {
                // aggregating qty per product in case the same product appears in multiple lines
                $needByProduct = collect($data['items'])
                    ->groupBy('product_id')
                    ->map(fn($rows) => $rows->sum('qty'));

                foreach ($needByProduct as $productId => $needQty) {
                    // pessimistic locking the product row during check & decrement
                    $product = Product::whereKey($productId)->lockForUpdate()->first();

                    if (!$product) {
                        throw ValidationException::withMessages([
                            'items' => ["Product {$productId} not found."],
                        ]);
                    }

                    if ($product->qty < $needQty) {
                        // surface error tied to the first offending line for better UX
                        $idx = collect($data['items'])->search(
                            fn ($row) => $row['product_id'] == $productId
                        );

                        throw ValidationException::withMessages([
                            "items.$idx.qty" => "Quantity exceeds available stock ({$product->qty}).",
                        ]);
                    }

                    // decrement stock
                    $product->decrement('qty', $needQty);
                }
            }

            // Bulk insert for performance
            OrderItem::insert($lines);

            // Sum total (server truth)
            $sum = OrderItem::where('order_id', $order->id)->sum('unit_total');
            $order->update(['total' => number_format((float)$sum, 3, '.', '')]);

            return $order;
        });

        return redirect()
            ->route('orders.index')
            ->with('message', "Order #{$order->id} created with ". $order->items()->count() ." item(s).");
    }

    public function edit(Order $order)
    {
        // for edit method i just need to send same options/values i sent for create-orders page
        // edit()
        $customerOptions = Customer::query()
            ->where('is_active', true)              // only active customers
            ->orderBy('name')
            ->get(['id','name'])
            ->map(fn($c) => ['label' => $c->name, 'value' => $c->id]);

        $productOptions = Product::query()
            ->where('is_active', true)       // only active products
            ->orderBy('name')
            ->get(['id', 'name', 'price', 'qty'])
            ->map(fn($p) => [
                'label' => $p->name,
                'value' => $p->id,
                'price' => (float) $p->price,
                'stock' => (int) $p->qty,
            ]);


        $order->load(['items:id,order_id,product_id,qty,unit_total', 'customer:id,name', 'placedBy:id,name']);

        // shape order for the page
        $orderPayload = [
            'id'          => $order->id,
            'customer_id' => $order->customer_id,
            'placed_by'   => $order->placed_by,
            'status'      => $order->status,
            'total'       => (float) $order->total,
            'items'       => $order->items->map(fn($it) => [
                'product_id' => $it->product_id,
                'qty'        => (int) $it->qty,
                'unit_total' => (float) $it->unit_total,
            ])->values(),
        ];

        return Inertia::render('orders/Edit', [
            'order'          => $orderPayload,
            'customerOptions'=> $customerOptions,
            'productOptions' => $productOptions,
            'statusOptions'  => [
                ['label'=>'Draft','value'=>'draft'],
                ['label'=>'Placed','value'=>'placed'],
                ['label'=>'Cancelled','value'=>'cancelled'],
                ['label'=>'Fulfilled','value'=>'fulfilled'],
            ],
            'placedBy'       => auth()->id(),
            'placedByName'   => optional(auth()->user())->name,
            'flash' => session()->only(['message']),
        ]);
    }

    // public function update(Request $request, Order $order)
    // {
    //     $data = $request->validate([
    //         'customer_id'       => ['required','exists:customers,id'],
    //         'placed_by'         => ['required','exists:users,id'],
    //         'status'            => ['required','in:draft,placed,cancelled,fulfilled'],
    //         'items'             => ['required','array','min:1'],
    //         'items.*.product_id'=> ['required','exists:products,id'],
    //         'items.*.qty'       => ['required','integer','min:1'],
    //         'items.*.unit_total'=> ['required','numeric','min:0'],
    //     ]);

    //     DB::transaction(function () use ($order, $data) {

    //         // Update main order fields
    //         $order->update([
    //             'customer_id' => $data['customer_id'],
    //             'placed_by'   => $data['placed_by'],
    //             'status'      => $data['status'],
    //         ]);

    //         // Remove existing line items (simpler for now)
    //         $order->items()->delete();

    //         // Insert new line items
    //         $lines = collect($data['items'])->map(function ($it) use ($order) {
    //             return [
    //                 'order_id'   => $order->id,
    //                 'product_id' => $it['product_id'],
    //                 'qty'        => $it['qty'],
    //                 'unit_total' => number_format((float)$it['unit_total'], 3, '.', ''),
    //                 'created_at' => now(),
    //                 'updated_at' => now(),
    //             ];
    //         });

    //         OrderItem::insert($lines->all());

    //         //updating product stock logic
    //         $previousStatus = $order->status;

    //         // Step 1: If the order was "fulfilled", skip updates
    //         if ($previousStatus === 'fulfilled') {
    //             return;
    //         }

    //         // If order is being cancelled
    //         if ($data['status'] === 'cancelled') {
    //             foreach ($order->items as $oldItem) {
    //                 Product::where('id', $oldItem->product_id)
    //                     ->increment('qty', $oldItem->qty); // restock cancelled items
    //             }
    //             return;
    //         }

    //         // Step 3: For placed or draft orders
    //         if (in_array($data['status'], ['placed', 'draft'])) {
    //             $oldItems = $order->items->keyBy('product_id');

    //             foreach ($data['items'] as $newItem) {
    //                 $pid = $newItem['product_id'];
    //                 $newQty = $newItem['qty'];
    //                 $oldQty = $oldItems[$pid]->qty ?? 0;
    //                 $delta = $newQty - $oldQty;

    //                 if ($delta !== 0) {
    //                     // If increased, reduce stock; if decreased, return stock
    //                     Product::where('id', $pid)->decrement('qty', $delta);
    //                 }
    //             }
    //         }
                        
    //         $total = $order->items()->sum('unit_total');
    //         $order->update(['total' => number_format((float)$total, 3, '.', '')]);
    //     });

    //     return redirect()
    //         ->route('orders.index')
    //         ->with('message', "Order #{$order->id} updated successfully.");
    // }

    public function update(Request $request, Order $order)
    {
        $data = $request->validate([
            'customer_id'        => ['required','exists:customers,id'],
            'placed_by'          => ['required','exists:users,id'],
            'status'             => ['required','in:draft,placed,cancelled,fulfilled'],
            'items'              => ['required','array','min:1'],
            'items.*.product_id' => ['required','exists:products,id'],
            'items.*.qty'        => ['required','integer','min:1'],
            'items.*.unit_total' => ['required','numeric','min:0'],
        ]);

        DB::transaction(function () use ($order, $data) {

            $previousStatus = $order->status;
            $oldItems = $order->items()->get()->keyBy('product_id');

            $order->update([
                'customer_id' => $data['customer_id'],
                'placed_by'   => $data['placed_by'],
                'status'      => $data['status'],
            ]);

            $order->items()->delete();

            $lines = collect($data['items'])->map(fn($it) => [
                'order_id'   => $order->id,
                'product_id' => $it['product_id'],
                'qty'        => $it['qty'],
                'unit_total' => number_format((float)$it['unit_total'], 3, '.', ''),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            OrderItem::insert($lines->all());

            // fresh relation to access new items cleanly
            $order->load('items');


            // Stock adjustment logic

            // If order is fulfilled then skip 
            if ($previousStatus === 'fulfilled' || $data['status'] === 'fulfilled') {
                return;
            }

            // If order is cancelled → restock all items
            if ($data['status'] === 'cancelled') {
                foreach ($order->items as $it) {
                    Product::where('id', $it->product_id)->increment('qty', $it->qty);
                }
            }

            // If draft/placed then adjust based on quantity delta
            if (in_array($data['status'], ['placed', 'draft'])) {
                foreach ($data['items'] as $newItem) {
                    $pid = $newItem['product_id'];
                    $newQty = $newItem['qty'];
                    $oldQty = $oldItems[$pid]->qty ?? 0;
                    $delta = $newQty - $oldQty;

                    if ($delta > 0) {
                        Product::where('id', $pid)->decrement('qty', $delta);
                    } elseif ($delta < 0) {
                        Product::where('id', $pid)->increment('qty', abs($delta));
                    }
                }
            }

            // Recompute order total
            $total = $order->items()->sum('unit_total');
            $order->update(['total' => number_format((float)$total, 3, '.', '')]);
        });

        return redirect()
            ->route('orders.index')
            ->with('message', "Order #{$order->id} updated successfully.");
    }

}
