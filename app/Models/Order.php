<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = ['customer_id','placed_by','status','total'];

    protected $casts = [
        'total' => 'decimal:3',
    ];

    public function customer() { return $this->belongsTo(Customer::class); }
    public function placedBy() { return $this->belongsTo(User::class, 'placed_by'); }
    public function items()    { return $this->hasMany(OrderItem::class); }
}

