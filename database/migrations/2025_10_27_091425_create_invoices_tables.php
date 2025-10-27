<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Invoices (one invoice per order)
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();

            // order (one-to-one with invoice)
            $table->foreignId('order_id')
                ->constrained('orders')
                ->cascadeOnUpdate()
                ->restrictOnDelete(); // don't allow deleting orders that have an invoice

            // created_for_cust (from order->customer_id)
            $table->foreignId('created_for_cust')
                ->constrained('customers')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            // created_by_employe (the user generating the invoice now)
            $table->foreignId('created_by_employe')
                ->constrained('users')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            // status
            $table->enum('status', ['draft', 'issued', 'void'])->default('draft');

            // money fields (10,3)
            $table->decimal('subtotal', 10, 3)->default(0);
            $table->decimal('discount', 10, 3)->default(0);
            $table->decimal('total',    10, 3)->default(0);

            $table->timestamps();

            // // Enforce one invoice per order
            // $table->unique('order_id');

            // Helpful indexes
            $table->index(['created_for_cust', 'status']);
            $table->index('created_by_employe');
        });

        // Invoice items (copied from order items at generation time)
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('invoice_id')
                ->constrained('invoices')
                ->cascadeOnUpdate()
                ->cascadeOnDelete(); // delete items when invoice is deleted

            $table->foreignId('product_id')
                ->constrained('products')
                ->cascadeOnUpdate()
                ->restrictOnDelete(); // keep product history; prevent deleting a product with invoiced lines

            // Optional description snapshot (e.g., product name at time of invoicing)
            $table->string('description')->nullable();

            $table->unsignedInteger('qty');
            $table->decimal('unit_total', 10, 3); // line total for this row

            $table->timestamps();

            // // Avoid duplicates of same product in the same invoice
            // $table->unique(['invoice_id', 'product_id']);

            $table->index('product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('invoices');
    }
};
