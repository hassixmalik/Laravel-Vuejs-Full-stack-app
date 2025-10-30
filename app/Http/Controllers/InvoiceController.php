<?php

namespace App\Http\Controllers;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\InvoiceMail;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class InvoiceController extends Controller
{
    public function index()
    {
        $invoices = Invoice::query()
            ->with([
                'order:id',                    // for order number
                'customer:id,name',            // for "Customer"
                'createdBy:id,name',           // for "Created By"
            ])
            ->select([
                'id',
                'order_id',
                'created_for_cust',
                'created_by_employe',
                'status',
                'subtotal',
                'discount',
                'total',
                'created_at',
            ])
            ->latest('id')
            ->paginate(10)
            ->through(function (Invoice $inv) {
                return [
                    'invoice_no' => sprintf('INV-%04d', $inv->id), // "INV-0001"
                    'order_no'   => $inv->order_id,                 // "#1"
                    'customer'   => $inv->customer?->name ?? '—',
                    'created_by' => $inv->createdBy?->name ?? '—',
                    'status'     => $inv->status,                  // 'draft' | 'issued' | 'void'
                    'subtotal'   => number_format((float)$inv->subtotal, 3, '.', ''),
                    'discount'   => number_format((float)$inv->discount, 3, '.', ''),
                    'total'      => number_format((float)$inv->total,    3, '.', ''),
                    'created_at' => $inv->created_at->format('Y-m-d H:i'),
                    // keep raw ids too if you plan to link to show/edit
                    'id'         => $inv->id,
                ];
            });

        return Inertia::render('invoice/Index', [
            'invoices' => $invoices,
        ]);
    }

    public function create()
    {
        // Load orders with related names and items count
        $orders = Order::query()
            ->with(['customer:id,name', 'placedBy:id,name'])
            ->withCount('items')
            ->latest()
            ->get();

        // Select options for the dropdown
        $orderOptions = $orders->map(fn($o) => [
            'label' => "Order #{$o->id} — {$o->customer->name}",
            'value' => $o->id,
        ]);

        // Lightweight summaries keyed by order id (for the preview panel)
        $orderSummaries = $orders->mapWithKeys(fn($o) => [
            $o->id => [
                'order_id'        => $o->id,
                'customer_name'   => $o->customer->name,
                'placed_by_name'  => $o->placedBy?->name ?? 'Unknown',
                'total'           => number_format((float)$o->total, 3, '.', ''),
                'items_count'     => $o->items_count,
            ],
        ]);

        return Inertia::render('invoice/Create', [
            'orderOptions'    => $orderOptions,
            'orderSummaries'  => $orderSummaries, // { [id]: {customer_name, placed_by_name, total, items_count} }
            'statusOptions'   => [
                ['label' => 'draft',  'value' => 'draft'],
                ['label' => 'issued', 'value' => 'issued'],
                ['label' => 'void',   'value' => 'void'],
            ],
            // VAT option kept simple as yes/no toggle (boolean)
            'vatOptions'      => [
                ['label' => 'No VAT', 'value' => false],
                ['label' => 'Apply VAT', 'value' => true],
            ],
            'createdById'     => auth()->id(),
            'createdByName'   => optional(auth()->user())->name,
        ]);
    }
    
    public function store(Request $request)
    {
        // Adjust if you store VAT elsewhere; SA default 15%
        $VAT_RATE = 0.15;

        $data = $request->validate([
            'order_id'    => ['required', 'exists:orders,id'],
            'discount'    => ['required', 'numeric', 'min:0'],
            'vat_enabled' => ['required', 'boolean'],
            'status'      => ['required', 'in:draft,issued,void'],
        ]);

        // Prevent duplicate invoice for an order (since unique index is commented out)
        if (Invoice::where('order_id', $data['order_id'])->exists()) {
            return back()->withErrors(['order_id' => 'An invoice already exists for this order.'])->withInput();
        }

        $invoice = DB::transaction(function () use ($data, $VAT_RATE) {
            // Load order with required context
            $order = Order::with(['customer:id,name', 'placedBy:id,name'])
                ->findOrFail($data['order_id']);

            // Pull order items once
            $orderItems = OrderItem::where('order_id', $order->id)->get([
                'product_id', 'qty', 'unit_total'
            ]);

            if ($orderItems->isEmpty()) {
                abort(422, 'Order has no items to invoice.');
            }

            // Compute monetary fields (3dp)
            $subtotal = (float) $orderItems->sum('unit_total');         // sum of line totals
            $discount = (float) $data['discount'];
            $discounted = max($subtotal - $discount, 0.0);
            $vatAmount = $data['vat_enabled'] ? $discounted * $VAT_RATE : 0.0;
            $total = $discounted + $vatAmount;

            // Normalize to 3 decimals
            $subtotal = (float) number_format($subtotal, 3, '.', '');
            $discount = (float) number_format($discount, 3, '.', '');
            $total    = (float) number_format($total,    3, '.', '');

            // Create invoice header
            $invoice = Invoice::create([
                'order_id'           => $order->id,
                'created_for_cust'   => $order->customer_id,              // from order
                'created_by_employe' => auth()->id(),                     // current user (matches your column name)
                'status'             => $data['status'],
                'subtotal'           => $subtotal,
                'discount'           => $discount,
                'total'              => $total,
                'vat_enabled'        => (bool) $data['vat_enabled'],
            ]);

            // Build product info map (both fields)
            $productMap = Product::whereIn('id', $orderItems->pluck('product_id')->unique())
                ->get(['id','description','name'])
                ->keyBy('id'); // [id => Product]

            $lines = $orderItems->map(function ($it) use ($invoice, $productMap) {
                $p = $productMap->get($it->product_id);
                $desc = $p ? ($p->description ?: $p->name ?: '') : '';
                return [
                    'invoice_id' => $invoice->id,
                    'product_id' => $it->product_id,
                    'description'=> (string) $desc,
                    'qty'        => (int) $it->qty,
                    'unit_total' => (float) number_format((float) $it->unit_total, 3, '.', ''),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })->all();


            InvoiceItem::insert($lines);

            return $invoice;
        });

        return redirect()
            ->route('invoice.show', $invoice->id)
            ->with('message', "Invoice #{$invoice->id} created.");
    }

    public function show(Invoice $invoice)
    {
        // Eager-load relations
        $invoice->load([
            'customer:id,name,email,phone,address',
            'createdBy:id,name',
            'order:id',
            'items.product:id,name', // product is only for fallback to name if description missing
        ]);

        // Money math (3 dp)
        $net       = max((float)$invoice->subtotal - (float)$invoice->discount, 0.0);
        $vatAmount = max((float)$invoice->total - $net, 0.0);
        $vatRate   = $net > 0 ? round($vatAmount / $net, 2) : 0.0; // e.g., 0.15

        // Shape items for the view
        $items = $invoice->items->map(function ($it) {
            $unitPrice = $it->qty > 0 ? (float)$it->unit_total / (int)$it->qty : 0.0;
            return [
                'id'          => $it->id,
                'description' => (string)($it->description ?: ($it->product->name ?? '')),
                'quantity'    => (int)$it->qty,
                'price'       => (float)number_format($unitPrice, 3, '.', ''),   // unit price (derived)
                'total'       => (float)number_format((float)$it->unit_total, 3, '.', ''), // line total
            ];
        })->values();

        // Pass a clean payload the page can render directly
        return Inertia::render('invoice/Show', [
            'invoice' => [
                'id'        => $invoice->id,
                'date'      => $invoice->created_at?->format('Y-m-d'),
                'due_date'  => optional($invoice->created_at)->addDays(10)->format('Y-m-d'),
                'status'    => $invoice->status, // 'draft'|'issued'|'void'
                'customer'  => [
                    'name'    => $invoice->customer?->name ?? '—',
                    'email'   => $invoice->customer?->email ?? '—',
                    'phone'   => $invoice->customer?->phone ?? '—',
                    'address' => $invoice->customer?->address ?? '—',
                ],
                'items'       => $items,
                'subtotal'    => (float)number_format((float)$invoice->subtotal, 3, '.', ''),
                'discount'    => (float)number_format((float)$invoice->discount, 3, '.', ''),
                'total'       => (float)number_format((float)$invoice->total,    3, '.', ''),
                'vat_enabled' => (bool)$invoice->vat_enabled,
                'vat_rate'    => $vatRate, // fraction (e.g., 0.15)
                'created_by'  => $invoice->createdBy?->name ?? '—',
            ],
        ]);
    }

    public function edit(Invoice $invoice)
    {
        // load relations for display
        $invoice->load([
            'customer:id,name,email,phone,address',
            'createdBy:id,name',
            'order:id,customer_id,placed_by',
            'order.customer:id,name',
            'order.placedBy:id,name',
            'items:id,invoice_id,product_id,description,qty,unit_total',
        ]);

        // summarize for UI
        $items = $invoice->items->map(function ($it) {
            $unitPrice = $it->qty > 0 ? (float)$it->unit_total / (int)$it->qty : 0.0;
            return [
                'id'          => $it->id,
                'description' => (string)$it->description,
                'quantity'    => (int)$it->qty,
                'price'       => (float)number_format($unitPrice, 3, '.', ''),
                'total'       => (float)number_format((float)$it->unit_total, 3, '.', ''),
            ];
        })->values();

        $subtotal = (float) number_format((float) $invoice->items->sum('unit_total'), 3, '.', '');
        $discount = (float) number_format((float) $invoice->discount, 3, '.', '');
        $net      = max($subtotal - $discount, 0.0);
        $vatAmt   = (float) number_format((float) $invoice->total - $net, 3, '.', '');
        $vatRate  = $net > 0 ? round($vatAmt / $net, 2) : 0.0;

        return Inertia::render('invoice/Edit', [
            'invoice' => [
                'id'         => $invoice->id,
                'order_id'   => $invoice->order_id,
                'order_label'=> "Order #{$invoice->order_id} — " . ($invoice->order?->customer?->name ?? 'Customer'),
                'status'     => $invoice->status,
                'vat_enabled'=> (bool)$invoice->vat_enabled,
                'discount'   => $discount,
                'subtotal'   => $subtotal,
                'total'      => (float) number_format((float)$invoice->total, 3, '.', ''),
                'vat_rate'   => $vatRate,
                'customer'   => [
                    'name'    => $invoice->customer?->name ?? '—',
                    'email'   => $invoice->customer?->email ?? '—',
                    'phone'   => $invoice->customer?->phone ?? '—',
                    'address' => $invoice->customer?->address ?? '—',
                ],
                'placed_by_name' => $invoice->order?->placedBy?->name ?? '—',
                'items'      => $items,
            ],
            'statusOptions' => [
                ['label' => 'draft',  'value' => 'draft'],
                ['label' => 'issued', 'value' => 'issued'],
                ['label' => 'void',   'value' => 'void'],
            ],
            'vatOptions' => [
                ['label' => 'No VAT',   'value' => false],
                ['label' => 'Apply VAT','value' => true],
            ],
        ]);
    }

    public function update(Request $request, Invoice $invoice)
    {
        // Keep VAT constant with create() for consistency
        $VAT_RATE = 0.15;

        $data = $request->validate([
            'discount'    => ['required','numeric','min:0'],
            'vat_enabled' => ['required','boolean'],
            'status'      => ['required','in:draft,issued,void'],
        ]);

        DB::transaction(function () use ($invoice, $data, $VAT_RATE) {
            // Recompute subtotal from current invoice items (source of truth)
            $subtotal   = (float) $invoice->items()->sum('unit_total');
            $discount   = (float) $data['discount'];
            $afterDisc  = max($subtotal - $discount, 0.0);
            $vatAmount  = $data['vat_enabled'] ? $afterDisc * $VAT_RATE : 0.0;
            $total      = $afterDisc + $vatAmount;

            $invoice->update([
                'status'      => $data['status'],
                'vat_enabled' => (bool)$data['vat_enabled'],
                'discount'    => (float) number_format($discount, 3, '.', ''),
                'subtotal'    => (float) number_format($subtotal, 3, '.', ''),
                'total'       => (float) number_format($total,    3, '.', ''),
            ]);
        });

        return redirect()
            ->route('invoice.show', $invoice->id)
            ->with('message', "Invoice #{$invoice->id} updated.");
    }

    public function destroy(Invoice $invoice)
    {
        $id = $invoice->id;

        //first checking if invoice was issued or not
        if ($invoice->status === 'issued') {
            return back()->withErrors(['invoice' => 'Issued invoices cannot be deleted.']);
        }

        DB::transaction(function () use ($invoice) {
            $invoice->delete();
            // No need to manually delete invoice_items because FK is cascadeOnDelete
            // If your FK wasn't cascading, then:
            // InvoiceItem::where('invoice_id', $invoice->id)->delete();
        });

        return redirect()
            ->route('invoice.index')
            ->with('message', "Invoice #{$id} deleted.");
    }

    public function convertFromOrder(Order $order)
    {
        // Hard-fill choices
        $VAT_RATE    = 0.15;    // SA default
        $DISCOUNT    = 0.000;   // hard-filled
        $VAT_ENABLED = true;    // hard-filled
        $STATUS      = 'draft'; // or 'draft' if you prefer

        // Prevent dup invoices for same order
        if (Invoice::where('order_id', $order->id)->exists()) {
            return redirect()
                ->route('invoice.index')
                ->with('message', "An invoice already exists for Order #{$order->id}.");
        }

        $invoice = DB::transaction(function () use ($order, $DISCOUNT, $VAT_ENABLED, $VAT_RATE, $STATUS) {
            // Load minimal context
            $order->load(['customer:id', 'placedBy:id']);
            $order->update(['status' => 'placed']);

            // Get order items
            $orderItems = OrderItem::where('order_id', $order->id)->get(['product_id','qty','unit_total']);
            if ($orderItems->isEmpty()) {
                abort(422, 'Order has no items to invoice.');
            }

            // Totals (3dp)
            $subtotal   = (float) $orderItems->sum('unit_total');
            $discount   = (float) $DISCOUNT;
            $afterDisc  = max($subtotal - $discount, 0.0);
            $vatAmount  = $VAT_ENABLED ? $afterDisc * $VAT_RATE : 0.0;
            $total      = $afterDisc + $vatAmount;

            // Normalize
            $subtotal = (float) number_format($subtotal, 3, '.', '');
            $discount = (float) number_format($discount, 3, '.', '');
            $total    = (float) number_format($total,    3, '.', '');

            // Create invoice header
            $invoice = Invoice::create([
                'order_id'           => $order->id,
                'created_for_cust'   => $order->customer_id,
                'created_by_employe' => auth()->id(),
                'status'             => $STATUS,
                'subtotal'           => $subtotal,
                'discount'           => $discount,
                'total'              => $total,
                'vat_enabled'        => (bool)$VAT_ENABLED,
            ]);

            // Build product info map (for description snapshot)
            $productMap = Product::whereIn('id', $orderItems->pluck('product_id')->unique())
                ->get(['id','description','name'])
                ->keyBy('id');

            $lines = $orderItems->map(function ($it) use ($invoice, $productMap) {
                $p = $productMap->get($it->product_id);
                $desc = $p ? ($p->description ?: $p->name ?: '') : '';
                return [
                    'invoice_id' => $invoice->id,
                    'product_id' => $it->product_id,
                    'description'=> (string) $desc,
                    'qty'        => (int) $it->qty,
                    'unit_total' => (float) number_format((float) $it->unit_total, 3, '.', ''),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })->all();



            InvoiceItem::insert($lines);

            return $invoice;
        });

        return redirect()
            ->route('invoice.show', $invoice->id)
            ->with('message', "Invoice #{$invoice->id} created from Order #{$order->id}.");
    }

    public function detail(){
        return Inertia::render('invoice/Detail');
    }

    public function email(Invoice $invoice)
    {
        $invoice->load([
            'customer:id,name,email,phone,address',
            'createdBy:id,name',
            'items.product:id,name',
        ]);

        $invoice->update(['status' => 'issued']); // marking email as issued

        $items = $invoice->items->map(function ($it) {
            $unitPrice = $it->qty > 0 ? (float)$it->unit_total / (int)$it->qty : 0.0;
            return [
                'id'          => $it->id,
                'description' => (string)($it->description ?: ($it->product->name ?? '')),
                'quantity'    => (int)$it->qty,
                'price'       => (float)number_format($unitPrice, 3, '.', ''),
                'total'       => (float)number_format((float)$it->unit_total, 3, '.', ''),
            ];
        })->values()->all();

        $payload = [
            'id'         => $invoice->id,
            'date'       => optional($invoice->created_at)->format('Y-m-d'),
            'status'     => $invoice->status,
            'customer'   => [
                'name'    => $invoice->customer?->name ?? '—',
                'email'   => $invoice->customer?->email ?? '',
                'phone'   => $invoice->customer?->phone ?? '—',
                'address' => $invoice->customer?->address ?? '—',
            ],
            'items'      => $items,
            'subtotal'   => (float)number_format((float)$invoice->subtotal, 3, '.', ''),
            'discount'   => (float)number_format((float)$invoice->discount, 3, '.', ''),
            'total'      => (float)number_format((float)$invoice->total,    3, '.', ''),
        ];

        $to = $payload['customer']['email'] ?: null;
        if (!$to) {
            return back()->withErrors(['email' => 'Customer email not available.']);
        }

        Mail::to($to)->send(new InvoiceMail($payload));

        return back()->with('message', "Invoice #{$invoice->id} emailed to {$to}.");
    }
}
