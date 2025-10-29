<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = ['name', 'price', 'qty', 'description', 'reserved'];

    public function getAvailableAttribute(): int
    {
        return max((int)$this->qty - (int)$this->reserved, 0);
    }
}
