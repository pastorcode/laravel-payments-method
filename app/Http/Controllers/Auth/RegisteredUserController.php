<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Product;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;

class RegisteredUserController extends Controller
{

    public function create()
    {
        $data['countries'] = DB::table('countries')->orderBy('name', 'ASC')->get();
        return view('auth.register')->with($data);
    }


    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'country' => 'required',
            'state' => 'required',
            'city' => 'required',
        ]);

        $country = DB::table('countries')->where('id', $request->get('country'))->first();
        $state = DB::table('countries')->where('id', $request->get('country'))->first();
        $city = DB::table('countries')->where('id', $request->get('country'))->first();

        $user = User::create([
            'userId' => Str::uuid(),
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'country' => ( $country != null) ? $country->name : 'No Country',
            'state' => ( $state != null) ? $state->name : 'No State',
            'city' => ( $state != null) ? $city->name : 'No City',
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(RouteServiceProvider::HOME);
    }
}
