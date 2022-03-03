<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class Dashboard extends Controller
{
    public function index(){
        $data['products'] = Product::all();
        return view('dashboard')->with($data);
    }
}
