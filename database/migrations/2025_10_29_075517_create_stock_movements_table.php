<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnUpdate()->restrictOnDelete();
            $table->integer('qty'); // positive or negative
            $table->enum('type', ['receive','fulfill','adjust_in','adjust_out','correction']);
            $table->nullableMorphs('reference'); // reference_type, reference_id
            $table->string('note')->nullable();
            $table->timestamps();

            $table->index(['product_id','type']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('stock_movements');
    }
};
