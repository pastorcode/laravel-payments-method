<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

class PaymentNotification extends Controller
{
    public function paystack(){

        // only a post with paystack signature header gets our attention (UNCOMMENT IN PRODUCTION SERVER)
//        if ((strtoupper($_SERVER['REQUEST_METHOD']) != 'POST' ) || !array_key_exists('x-paystack-signature', $_SERVER) )
//            exit();


        // Retrieve the request's body and parse it as JSON
        $paymentDetails = @file_get_contents("php://input");

        // validate event do all at once to avoid timing attack (UNCOMMENT IN PRODUCTION SERVER)
//        if($_SERVER['HTTP_X_PAYSTACK_SIGNATURE'] !== hash_hmac('sha512', $paymentDetails, env('PAYSTACK_SECRET_KEY')))
//            echo 'not validated';
//            exit();


        $paymentDetails = json_decode($paymentDetails);

        if($paymentDetails != null && $paymentDetails->event == "charge.success" && $paymentDetails->data->status == "success"){
            $paymentId = $paymentDetails->data->reference;

            //check if payment is already success at your end
            $payment = Payment::where('paymentId', $paymentId)->first();

            if($payment == null){
                echo 'payment id does not exist';
                exit(0);
            }

            if($payment->status == 'success'){
                echo 'payment is already marked success';
                exit(0);
            }


            //at this point confirm from transaction from paystack again
            $response = json_decode(Http::withToken(env('PAYSTACK_SECRET_KEY'))->get('https://api.paystack.co/transaction/verify/'.$paymentId)->body());

            if($response == null){
                echo 'payment confirm from paystack is null';
                exit();
            }

            //payment is not yet success
            if($response->data->status != 'success'){
                echo 'payment is not success from paystack yet';
                exit();
            }


            //if payment is not successful make it successful
            Payment::where('paymentId', $paymentId)->update([
                'status' => 'success',
                'statusDate' => Carbon::now()
            ]);
            echo 'payment marked success';
        }


        http_response_code(200);
    }

    public function flutterwave(){

        // Retrieve the request's body
        $paymentDetails = @file_get_contents("php://input");


        //refer to flutter doc to see how to secure webhook
        //https://developer.flutterwave.com/reference/webhook

        $paymentDetails = json_decode($paymentDetails);
        if($paymentDetails == null){
            echo 'payment details is null';
            exit();
        }

        if ($paymentDetails->status == 'successful') {
            $paymentId = $paymentDetails->data->tx_ref;
            //check your db to confirm is payment is already successful
            $payment = Payment::where('paymentId', $paymentId)->first();
            if($payment == null){
                echo 'payment id does not exist';
                exit(0);
            }

            if($payment->status == 'success'){
                echo 'payment is already marked success';
                exit(0);
            }

            //confirm payment
            new \Flutterwave\Rave(getenv('FLUTTER_SECRET_KEY'));
            $transactions = new \Flutterwave\Transactions();
            $response = $transactions->verifyTransaction(['id' => $paymentId]);
            if ($response['data']['status'] === "successful" && $response['data']['amount'] === round($payment->price) && $response['data']['currency'] === 'NGN') {
                //payment is successfully here
                Payment::where('paymentId', $paymentId)->update([
                    'status' => 'success',
                    'statusDate' => Carbon::now()
                ]);
                http_response_code(200);
            }else{
                echo 'payment not successful';
            }
            exit();
        }
        http_response_code(200);
        exit();
    }

    public function monnify(){
//        Securing monnify webhook
//            - Transaction Hash Validation: https://teamapt.atlassian.net/wiki/pages/resumedraft.action?draftId=212008918
//            -IP Whitelisting: 35.242.133.146

        $paymentDetails = json_decode(@file_get_contents("php://input"));

        if($paymentDetails == null){
            echo 'payment notification JSON is empty';
            exit();
        }

        $transactionReference = $paymentDetails->eventData->transactionReference;
        $paymentId = $paymentDetails->eventData->paymentReference;

        //check your database to see if it is already confirmed
        $payment = Payment::where('paymentId', $paymentId)->first();

        if($payment == null){
            echo 'payment id was not found in my database';
            exit();
        }

        if($payment->status == 'success'){
            echo 'payment os already confirmed here';
            exit();
        }

        //now go ahead and confirm notification details
        $token = base64_encode(env('MONNIFY_PUBLIC_KEY').':'.env('MONNIFY_SECRET_KEY'));

        $loginResponse = json_decode(
            Http::withHeaders(array(
                'Authorization' => 'Basic '.$token
            ))
                ->post('https://sandbox.monnify.com/api/v1/auth/login')
                ->body()
        );


        if(!$loginResponse->requestSuccessful && $loginResponse->responseMessage != 'success'){
            echo 'could not generate access token';
            exit();
        }


        //this access token will be used to authorize transaction verification request
        $accessToken = $loginResponse->responseBody->accessToken;
        $response = json_decode(
            Http::withToken($accessToken)->get('https://sandbox.monnify.com/api/v2/transactions/'.$transactionReference)->body()
        );

        if(!$response->requestSuccessful && $response->responseMessage != 'success'){
            echo 'payment was not success after confirming it';
            exit();
        }

        //if payment is not successful make it successful
        Payment::where('paymentId', $paymentId)->update([
            'status' => 'success',
            'statusDate' => Carbon::now()
        ]);

        echo 'payment marked success';
        http_response_code(200);
    }
}
