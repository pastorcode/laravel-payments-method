<link href="https://unpkg.com/tailwindcss@^2/dist/tailwind.min.css" rel="stylesheet">

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    @if(\Illuminate\Support\Facades\Session::has('success'))
        <br>
        <p class="mx-auto text-green-500">{{ \Illuminate\Support\Facades\Session::get('success') }}</p>
    @endif

    @if(\Illuminate\Support\Facades\Session::has('pending'))
        <br>
        <p class="tmx-auto ext-yellow-500">{{ \Illuminate\Support\Facades\Session::get('pending') }}</p>
    @endif

    @if(\Illuminate\Support\Facades\Session::has('error'))
        <br>
        <p class="mx-auto text-red-500">{{ \Illuminate\Support\Facades\Session::get('error') }}</p>
    @endif

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    You're logged in!
                </div>
            </div>
        </div>
    </div>

    <div class="w-full mx-auto max-w-lg">

        <form id="checkoutForm" class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="password">
                    Select Product
                </label>
                <select required id="product" class="block appearance-none w-full bg-white border border-gray-400 hover:border-gray-500 px-4 py-2 pr-8 rounded shadow leading-tight focus:outline-none focus:shadow-outline">
                    <option value="">Choose...</option>
                    @foreach($products as $product)
                        <option value="{{ $product->productId }}">{{ $product->name }} ==> &#8358;{{ number_format($product->price, 2) }}</option>
                    @endforeach
                </select>
                {{--                <p class="text-red-500 text-xs italic">Please choose a password.</p>--}}
            </div>

            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="password">
                    Select Payment Option
                </label>
                <select id="paymentOption" class="block appearance-none w-full bg-white border border-gray-400 hover:border-gray-500 px-4 py-2 pr-8 rounded shadow leading-tight focus:outline-none focus:shadow-outline">
                    <option value="paystack">Paystack</option>
                    <option value="flutterwave">Flutterwave</option>
                    <option value="monnify">Monnify</option>
                </select>
{{--                <p class="text-red-500 text-xs italic">Please choose a password.</p>--}}
            </div>
            <div class="flex items-center justify-between">
                <button id="paymentButton" class="mx-auto bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded" style="background-color: darkblue" type="submit">
                    Pay with Paystack
                </button>
            </div>
        </form>
        <p class="text-center text-gray-500 text-xs">
            &copy;{{ date('Y') }} Makinde Corp. All rights reserved.
        </p>
    </div>
</x-app-layout>
