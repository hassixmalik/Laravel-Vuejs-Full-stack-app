<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    protected $fillable = ['product_id','qty','type','reference_type','reference_id','note'];
    public function product(){ 
        return $this->belongsTo(Product::class);
    }
    public function reference(){
        return $this->morphTo(); 
    }
}