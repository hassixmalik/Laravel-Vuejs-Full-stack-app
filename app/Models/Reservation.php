<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    protected $fillable = ['order_id','product_id','qty'];
    public function order(){ 
        return $this->belongsTo(Order::class); 
    }
    public function product(){ 
        return $this->belongsTo(Product::class); 
    }
}

