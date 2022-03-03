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
    <body>
        <div class="font-sans text-gray-900 antialiased">
            {{ $slot }}
        </div>

{{--        include jquery--}}
        <script src="{{ asset('js/jquery.min.js') }}"></script>

{{--        this js will handle pupulation of states and cities--}}
    <script>
        $(document).ready(function (){

            //this events fetches states on change of country select option
            $("#country").on('change', () => {
                const countryId = $("#country").val()
                $("#state").html(``).prop('disabled', true)
                $("#city").html(``).prop('disabled', false)

               if(countryId !== ""){
                   //i used fetch method because it seems after than ajax call in my opinion
                   fetch(`http://localhost:8001/states/${countryId}`)
                       .then((response) => {
                           return response.json();
                       })
                       .then((myJson) => {
                           if(myJson.length <= 0){
                               let statesOptionSelect = `<option>No states were found for this country</option>`
                               $("#state").html(statesOptionSelect).prop('disabled', false)
                               let cityOptionSelect = `<option>No cities were found for this state</option>`
                               $("#city").html(cityOptionSelect).prop('disabled', false)
                           }else{
                               let statesOptionSelect = `<option value="">Choose...</option>`
                               let i;
                               for(i in myJson){
                                   statesOptionSelect += `<option value="${myJson[i].id}">${myJson[i].name}</option>`
                               }
                               $("#state").html(statesOptionSelect).prop('disabled', false)
                           }

                       });
               }else {
                   $("#state").html(``).prop('disabled', true)
                   $("#city").html(``).prop('disabled', false)
               }
             });

            //this events fetches country on change of state select option
            $("#state").on('change', () => {
                const stateId = $("#state").val()
                $("#city").html(``).prop('disabled', false)

                if(stateId !== ""){
                    fetch(`http://localhost:8001/city/${stateId}`)
                        .then((response) => {
                            return response.json();
                        })
                        .then((myJson) => {
                            if(myJson.length <= 0){
                                let cityOptionSelect = `<option>No cities were found for this state</option>`
                                $("#city").html(cityOptionSelect).prop('disabled', false)
                            }else{
                                let cityOptionSelect = `<option value="">Choose...</option>`
                                let i;
                                for(i in myJson){
                                    cityOptionSelect += `<option value="${myJson[i].id}">${myJson[i].name}</option>`
                                }
                                $("#city").html(cityOptionSelect).prop('disabled', false)
                            }

                        });
                }else {
                    $("#city").html(``).prop('disabled', true)
                }
            });


        })
    </script>
    </body>
</html>
