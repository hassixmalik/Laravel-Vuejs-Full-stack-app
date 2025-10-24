<?php

namespace App\Http\Controllers;
use Inertia\Inertia;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function index(){
        return Inertia::render('invoice/Index');
    }
    public function create(){
        return Inertia::render('invoice/Create');
    }
    public function detail(){
        return Inertia::render('invoice/Detail');
    }
    public function store(Request $request){
        dd($request);
    }
}
