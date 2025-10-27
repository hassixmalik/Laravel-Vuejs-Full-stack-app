<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    protected $fillable = ['invoice_id','product_id','description','qty', 'unit_total'];

    public function product()  { return $this->belongsTo(Product::class); }
}
