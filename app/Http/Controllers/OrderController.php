<?php

namespace App\Http\Controllers;
use Inertia\Inertia;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(){
        return Inertia::render('orders/Index');
    }
    public function create(){
        return Inertia::render('orders/Create');
    }
}
