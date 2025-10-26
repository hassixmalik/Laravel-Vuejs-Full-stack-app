<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        
        // Orders table
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            // FKs
            $table->foreignId('customer_id')
                ->constrained('customers')            // references id on customers
                ->cascadeOnUpdate()
                ->restrictOnDelete();                 // prevent deleting customers with orders

            $table->foreignId('placed_by')            // user who placed the order
                ->constrained('users')                // references id on users
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            // Status enum
            $table->enum('status', ['draft', 'placed', 'cancelled', 'fulfilled'])
                  ->default('draft');

            // Money
            $table->decimal('total', 10, 3)->default(0);

            $table->timestamps();

            // Helpful indexes
            $table->index(['customer_id', 'status']);
            $table->index('placed_by');
        });

        // Order items
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')
                ->constrained('orders')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();                  // delete lines when order is deleted

            $table->foreignId('product_id')
                ->constrained('products')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->unsignedInteger('qty');          
            $table->decimal('unit_total', 10, 3);    

            $table->timestamps();

            // // One product per order line (prevents duplicate lines of same product)
            // $table->unique(['order_id', 'product_id']);

            $table->index('product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
        Schema::dropIfExists('order_items');
    }
};
