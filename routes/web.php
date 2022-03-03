<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Country;
use App\Http\Controllers\Dashboard;
use App\Http\Controllers\Payment;
use App\Http\Controllers\Product;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

//Auth routes generated form laravel breeze
require __DIR__.'/auth.php';

//this routes will fetch states and country as route name implies
Route::get('/states/{countryId}', [Country::class, 'state'])->name('fetch.states');
Route::get('/city/{stateId}', [Country::class, 'city'])->name('fetch.cities');


Route::middleware('auth')->group(function (){

    Route::get('/dashboard', [Dashboard::class, 'index'])->name('dashboard');

    //this route will be used for creating a payment record
    Route::post('create/payment', [Payment::class, 'create'])->name('payment.create');

    //confirmed payment routes
    Route::get('confirm/payment/paystack/{paymentId}', [Payment::class, 'paystack'])->name('payment.paystack');
    Route::get('confirm/payment/flutterwave/{paymentId}', [Payment::class, 'flutterwave'])->name('payment.flutterwave');
    Route::get('confirm/payment/monnify/{transactionReference}', [Payment::class, 'monnify'])->name('payment.monnify');

    //product creation route
    Route::get('create/product', [Product::class, 'create'])->name('product.create');



});




