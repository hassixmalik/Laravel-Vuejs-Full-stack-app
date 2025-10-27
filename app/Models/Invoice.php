<?php

namespace App\Models;
use App\Models\Customer;
use App\Models\User;
use App\Models\Product;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    // app/Models/Invoice.php
    protected $fillable = ['order_id','created_for_cust','created_by_employe','status', 'subtotal', 'discount', 'total', 'created_at', 'updated_at'];
    protected $casts = [
        'vat_enabled' => 'boolean',
        'subtotal'    => 'decimal:3',
        'discount'    => 'decimal:3',
        'total'       => 'decimal:3',
    ];
    public function order()    { return $this->belongsTo(Order::class); }
    public function customer() { return $this->belongsTo(Customer::class, 'created_for_cust'); }
    public function createdBy(){ return $this->belongsTo(User::class, 'created_by_employe'); }
    public function items()    { return $this->hasMany(InvoiceItem::class); }
}
