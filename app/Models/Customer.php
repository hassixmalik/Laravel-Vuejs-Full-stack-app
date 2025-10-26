<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = ['name','email','phone','city','address','is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
