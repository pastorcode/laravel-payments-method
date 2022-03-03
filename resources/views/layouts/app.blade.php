<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap">

    <!-- Styles -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}" defer></script>
</head>
<body class="font-sans antialiased">
<div class="min-h-screen bg-gray-100">
@include('layouts.navigation')

<!-- Page Heading -->
    <header class="bg-white shadow">
        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            {{ $header }}
        </div>
    </header>

    <!-- Page Content -->
    <main>
        {{ $slot }}
    </main>
</div>

{{--        include jquery and payment SDK's--}}
<script src="{{ asset('js/jquery.min.js') }}"></script>
<script type="text/javascript" src="https://sdk.monnify.com/plugin/monnify.js"></script>
<script src="https://checkout.flutterwave.com/v3.js"></script>
<script src="https://js.paystack.co/v1/inline.js"></script>



<script>

    $("#paymentOption").on('change', () => {

        let paymentOption = $("#paymentOption").val();

        if (paymentOption === `paystack`) {
            $("#paymentButton").html(`Pay with Paysatck`)
        }

        if (paymentOption === `flutterwave`) {
            $("#paymentButton").html(`Pay with Flutterwave`)
        }

        if (paymentOption === `monnify`) {
            $("#paymentButton").html(`Pay with Monnify`)
        }

    });

    $("#checkoutForm").on('submit', function (event){
        event.preventDefault();

        $("#paymentButton").html(`Loading...`).prop('disabled', true)

        let paymentOption = $("#paymentOption").val();
        let productId = $("#product").val();
        let _token = '{{ csrf_token() }}'

        $.ajax({
            method: 'post',
            url: `http://localhost:8001/create/payment`,
            data: {paymentOption, productId, _token},
            success: (response) => {
                console.log(response)
                if(response.status === 1){
                    let data = response.data;
                    if (paymentOption === `paystack`) {
                        $("#paymentButton").html(`Pay with Paysatck`).prop('disabled', false)
                        payWithPaystack(data.key, data.email, data.amount, data.paymentId)
                    }

                    if (paymentOption === `flutterwave`) {
                        $("#paymentButton").html(`Pay with Flutterwave`).prop('disabled', false)
                        payWithFlutterwave(data.key, data.paymentId, data.amount, data.name, data.email, data.phone)
                    }

                    if (paymentOption === `monnify`) {
                        $("#paymentButton").html(`Pay with Monnify`).prop('disabled', false)
                        payWithMonnify(data.amount, data.paymentId, data.name, data.email, data.key, data.contract)
                    }
                }else{
                    setTimeout(() => {
                        if (paymentOption === `paystack`) {
                            $("#paymentButton").html(`Pay with Paysatck`).prop('disabled', false)
                        }

                        if (paymentOption === `flutterwave`) {
                            $("#paymentButton").html(`Pay with Flutterwave`).prop('disabled', false)
                        }

                        if (paymentOption === `monnify`) {
                            $("#paymentButton").html(`Pay with Monnify`).prop('disabled', false)
                        }
                    }, 1500);

                }
            },
            error: (err) => {
                console.log(err.responseText)
                setTimeout(() => {
                    if (paymentOption === `paystack`) {
                        $("#paymentButton").html(`Pay with Paysatck`).prop('disabled', false)
                    }

                    if (paymentOption === `flutterwave`) {
                        $("#paymentButton").html(`Pay with Flutterwave`).prop('disabled', false)
                    }

                    if (paymentOption === `monnify`) {
                        $("#paymentButton").html(`Pay with Monnify`).prop('disabled', false)
                    }
                }, 1500);
            }
        });
    });

    function payWithPaystack(key, email, amount, ref) {
        PaystackPop.setup({
            key, email, amount, ref,
            onClose: function(){
                alert('No payment has been made.');
            },
            callback: function(response){
                window.location.replace(`http://localhost:8001/confirm/payment/paystack/${ref}`)
            }
        }).openIframe();
    }

    function payWithFlutterwave(public_key, tx_ref, amount, name, email, phone_number) {
        FlutterwaveCheckout({
            public_key, tx_ref, amount, currency: "NGN",
            payment_options: "card, mobilemoneyghana, ussd",
            customer: {
                email, phone_number, name,
            },
            customizations: {
                title: "Makinde Store",
                description: "Test Pay",
                logo: "https://www.logolynx.com/images/logolynx/22/2239ca38f5505fbfce7e55bbc0604386.jpeg",
            },
            callback: (payment) => {
                window.location.replace(``);
            },
            onclose: function(incomplete) {
                alert('No payment has been made.')
            }
        });
    }

    function payWithMonnify(amount, reference, customerName, customerEmail, apiKey, contractCode) {
        MonnifySDK.initialize({
            amount, currency: "NGN", reference, customerName, customerEmail, apiKey, contractCode,
            paymentDescription: "Test Pay",
            isTestMode: true,
            paymentMethods: ["CARD", "ACCOUNT_TRANSFER"],
            onComplete: function (response) {
                window.location.replace(`http://localhost:8001/confirm/payment/monnify/${response.transactionReference}`)
            },
            onClose: function (data) {
                alert('No payment has been made.');
            }
        });
    }
</script>

</body>
</html>
