<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = ['order_id','product_id','qty','unit_total'];

    protected $casts = [
        'unit_total' => 'decimal:3',
    ];

    public function order()   { return $this->belongsTo(Order::class); }
    public function product() { return $this->belongsTo(Product::class); }
}
