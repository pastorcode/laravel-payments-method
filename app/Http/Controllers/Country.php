<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Country extends Controller
{
    public function state($countryId){
        $states = DB::table('states')->where('country_id', $countryId)->orderBy('name', 'ASC')->get();
        return response()->json($states);
    }

    public function city($stateId){
        $cities = DB::table('cities')->where('state_id', $stateId)->orderBy('name', 'ASC')->get();
        return response()->json($cities);
    }
}
