<?php

namespace App\Http\Controllers;


use App\Models\Product as ProductModel;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class Product extends Controller
{
    public function create(){
        ProductModel::factory(10)->create();
        echo 'Product Created Successfully';
    }
}
