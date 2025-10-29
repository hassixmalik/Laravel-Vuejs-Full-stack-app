<?php

namespace App\Http\Controllers;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Reservation;
use App\Models\StockMovement;
use Illuminate\Validation\ValidationException;

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
            ->where('is_active', true)
            ->whereRaw('(qty - reserved) > 0')   // only products with real availability
            ->orderBy('name')
            ->get(['id','name','price','qty','reserved'])
            ->map(fn($p) => [
                'label'     => $p->name,
                'value'     => $p->id,
                'price'     => (float) $p->price,
                'available' => max((int)$p->qty - (int)$p->reserved, 0),
            ]);


        return Inertia::render('orders/Create', [
            'customerOptions' => $customerOptions,
            'productOptions'  => $productOptions,
            'statusOptions'   => [
                ['label'=>'Draft','value'=>'draft'],
                ['label'=>'Placed','value'=>'placed'],
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
            'status'            => ['required','in:draft,placed,fulfilled'],
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

            // Build need per product (sum of qty)
            $needByProduct = collect($data['items'])
                ->groupBy('product_id')
                ->map(fn($rows) => (int)$rows->sum('qty'));

            foreach ($needByProduct as $productId => $needQty) {
                $product = Product::whereKey($productId)->lockForUpdate()->firstOrFail();

                // available = on_hand - reserved
                $available = $product->qty - $product->reserved;
                if ($available < $needQty) {
                    $idx = collect($data['items'])->search(fn($row) => $row['product_id'] == $productId);
                    throw ValidationException::withMessages([
                        "items.$idx.qty" => "Quantity exceeds available stock ({$available}).",
                    ]);
                }

                // reserve (do NOT touch qty)
                $product->reserved += $needQty;
                $product->save();

                Reservation::updateOrCreate(
                    ['order_id' => $order->id, 'product_id' => $productId],
                    ['qty' => $needQty]
                );
            }

            // If fulfilled at creation: consume immediately
            if ($order->status === 'fulfilled') {
                $res = Reservation::where('order_id', $order->id)->get();
                foreach ($res as $r) {
                    $p = Product::whereKey($r->product_id)->lockForUpdate()->firstOrFail();
                    $p->reserved = max(0, $p->reserved - $r->qty);
                    $p->qty      = max(0, $p->qty - $r->qty);
                    $p->save();

                    StockMovement::create([
                        'product_id'     => $p->id,
                        'qty'            => -$r->qty,
                        'type'           => 'fulfill',
                        'reference_type' => Order::class,
                        'reference_id'   => $order->id,
                        'note'           => 'Order fulfillment',
                    ]);
                }
                Reservation::where('order_id', $order->id)->delete();
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
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id','name','price','qty','reserved'])
            ->map(fn($p) => [
                'label'     => $p->name,
                'value'     => $p->id,
                'price'     => (float)$p->price,
                'available' => max((int)$p->qty - (int)$p->reserved, 0),
            ]);

        $order->load(['items:id,order_id,product_id,qty,unit_total', 'customer:id,name', 'placedBy:id,name']);

        $oldQtyMap = $order->items
            ->groupBy('product_id')
            ->map(fn($rows) => (int)$rows->sum('qty'))
            ->toArray();

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
            'oldQtyMap'      => $oldQtyMap,
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


            // stock adjustment logic
            // == reservations-based stock adjustment ==

            // collect new requested per product
            $newNeed = collect($data['items'])
                ->groupBy('product_id')
                ->map(fn($rows) => (int)$rows->sum('qty'));

            //  load old reservations for this order
            $oldRes = Reservation::where('order_id', $order->id)->get()->keyBy('product_id'); // pid => Reservation

            // apply deltas to products.reserved
            foreach ($newNeed as $pid => $newQty) {
                $oldQty = (int)($oldRes[$pid]->qty ?? 0);
                $delta  = $newQty - $oldQty;

                if ($delta !== 0) {
                    $product   = Product::whereKey($pid)->lockForUpdate()->firstOrFail();
                    $available = $product->qty - $product->reserved;

                    if ($delta > 0 && $available < $delta) {
                        throw ValidationException::withMessages([
                            'items' => ["Insufficient available stock for product #{$pid} (available: {$available})."],
                        ]);
                    }

                    $product->reserved = max(0, $product->reserved + $delta);
                    $product->save();
                }

                Reservation::updateOrCreate(
                    ['order_id' => $order->id, 'product_id' => $pid],
                    ['qty' => $newQty]
                );
            }

            // remove reservations for products no longer present
            $nowPids  = $newNeed->keys();
            $toRemove = $oldRes->keys()->diff($nowPids);
            if ($toRemove->isNotEmpty()) {
                $rem = Reservation::where('order_id', $order->id)->whereIn('product_id', $toRemove)->get();
                foreach ($rem as $r) {
                    $p = Product::whereKey($r->product_id)->lockForUpdate()->firstOrFail();
                    $p->reserved = max(0, $p->reserved - $r->qty);
                    $p->save();
                }
                Reservation::where('order_id', $order->id)->whereIn('product_id', $toRemove)->delete();
            }

            //  if status switched to cancelled then release all reservations
            if ($data['status'] === 'cancelled') {
                $res = Reservation::where('order_id', $order->id)->get();
                foreach ($res as $r) {
                    $p = Product::whereKey($r->product_id)->lockForUpdate()->firstOrFail();
                    $p->reserved = max(0, $p->reserved - $r->qty);
                    $p->save();
                }
                Reservation::where('order_id', $order->id)->delete();
            }

            //  if status switched to fulfilled then consume reserved into on_hand and log movement
            if ($data['status'] === 'fulfilled') {
                $res = Reservation::where('order_id', $order->id)->get();
                foreach ($res as $r) {
                    $p = Product::whereKey($r->product_id)->lockForUpdate()->firstOrFail();
                    $p->reserved = max(0, $p->reserved - $r->qty);
                    $p->qty      = max(0, $p->qty - $r->qty);
                    $p->save();

                    StockMovement::create([
                        'product_id'     => $p->id,
                        'qty'            => -$r->qty,
                        'type'           => 'fulfill',
                        'reference_type' => Order::class,
                        'reference_id'   => $order->id,
                        'note'           => 'Order fulfillment',
                    ]);
                }
                Reservation::where('order_id', $order->id)->delete();
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
