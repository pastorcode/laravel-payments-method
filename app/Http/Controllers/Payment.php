<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Payment as PaymentModel;
use App\Models\User;
use Flutterwave\Rave;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;


class Payment extends Controller
{
    public function create(Request $request){

        $product = Product::where('productId', $request->get('productId'))->first();
        $user = User::find(Auth::user()->id);

        if(!$product){
            return response()->json(['status' => -1, 'message' => 'Product not found']);
        }

        $paymentId =  Str::uuid();

        PaymentModel::create([
            'paymentId' => $paymentId,
            'userId' => Auth::user()->userId,
            'productId' => $request->get('productId'),
            'price' => $product->price,
            'paymentMethod' => $request->get('paymentOption'),
            'status' => 'pending',
            'statusDate' => Carbon::now(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        $amount = $key = $contract = null;

        if($request->get('paymentOption') == 'paystack'){
            $amount = round($product->price).'00';
            $key = env('PAYSTACK_PUBLIC_KEY');
        }

         if($request->get('paymentOption') == 'flutterwave'){
             $amount = round($product->price);
             $key = env('FLUTTER_PUBLIC_KEY');
         }

          if($request->get('paymentOption') == 'monnify'){
              $amount = round($product->price);
              $key = env('MONNIFY_PUBLIC_KEY');
              $contract = env('CONTRACT_KEY');
          }

          $data = [
              'amount' => $amount,
              'key' => $key,
              'contract' => $contract,
              'email' => Auth::user()->email,
              'name' => Auth::user()->name,
              'paymentId' => $paymentId,
              'phone' => '0000000000',
          ];

          return response()->json(['status' => 1, 'data' => $data, 'message' => 'Go!']);

    }

    public function paystack($paymentId){

        //check if payment is already success at your end
        $payment = PaymentModel::where('paymentId', $paymentId)->first();

        if($payment->status == 'success'){
            Session::flash('pending', 'Payment confirmed successfully');
            return redirect()->back();
        }

        //now if not success check paystack to confirm.
        $response = json_decode(Http::withToken(env('PAYSTACK_SECRET_KEY'))->get('https://api.paystack.co/transaction/verify/'.$paymentId)->body());

        //if payment is not confirmed yet
        if($response == null){
            Session::flash('pending', 'Payment will be processed shortly');
            return redirect()->back();
        }

        //payment is not yet success
        if($response->data->status != 'success'){
            Session::flash('pending', 'Payment will be processed shortly');
            return redirect()->back();
        }

        //payment is successfully here
        PaymentModel::where('paymentId', $paymentId)->update([
            'status' => 'success',
            'statusDate' => Carbon::now()
        ]);

        Session::flash('success', 'Payment is successful');
        return redirect()->back();
    }

    public function flutterwave($paymentId){
        // Install with:  composer require flutterwavedev/flutterwave-v3:dev-master

        $payment = PaymentModel::where('paymentId', $paymentId)->first();

        if($payment->status == 'success'){
            Session::flash('pending', 'Payment processed successfully');
            return redirect()->back();
        }

        //set secret key
        new \Flutterwave\Rave(getenv('FLUTTER_SECRET_KEY'));
        $transactions = new \Flutterwave\Transactions();
        $response = $transactions->verifyTransaction(['id' => $paymentId]);
        if ($response['data']['status'] === "successful" && $response['data']['amount'] === round($payment->price) && $response['data']['currency'] === 'NGN') {
            //payment is successfully here
            PaymentModel::where('paymentId', $paymentId)->update([
                'status' => 'success',
                'statusDate' => Carbon::now()
            ]);
            Session::flash('success', 'Payment is successful');

        } else {
            Session::flash('pending', 'Payment will be processed shortly');
        }
        return redirect()->back();
    }

    public function monnify($transactionReference){
        //login to monnify api
        $token = base64_encode(env('MONNIFY_PUBLIC_KEY').':'.env('MONNIFY_SECRET_KEY'));
        $loginResponse = json_decode(
            Http::withHeaders(array(
                'Authorization' => 'Basic '.$token
            ))
                ->post('https://sandbox.monnify.com/api/v1/auth/login')
                ->body()
        );


        if(!$loginResponse->requestSuccessful && $loginResponse->responseMessage != 'success'){
            Session::flash('pending', 'Payment will be confirmed shortly');
            return redirect()->back();
        }


        //this access token will be used to authorize transaction verification request
        $accessToken = $loginResponse->responseBody->accessToken;
        $response = json_decode(
            Http::withToken($accessToken)->get('https://sandbox.monnify.com/api/v2/transactions/'.$transactionReference)->body()
        );

        if(!$response->requestSuccessful && $response->responseMessage != 'success'){
            Session::flash('pending', 'Payment will be confirmed shortly');
            return redirect()->back();
        }

        //payment is successfully here
        $paymentId = $response->responseBody->paymentReference;

        //check if payment is already success at your end
        $payment = PaymentModel::where('paymentId', $paymentId)->first();

        if($payment->status == 'success'){
            Session::flash('pending', 'Payment confirmed successfully');
            return redirect()->back();
        }

        //if not mark it as successful
        PaymentModel::where('paymentId', $paymentId)->update([
            'status' => 'success',
            'statusDate' => Carbon::now()
        ]);

        Session::flash('success', 'Payment is successfully');
        return redirect()->back();

    }


}
