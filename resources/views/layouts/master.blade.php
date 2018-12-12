<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>@yield('title', 'TicketBeast')</title>
        <link rel="stylesheet" href="{{ mix('css/app.css') }}" />
        <link rel="stylesheet" href="{{ mix('css/main.css') }}" />
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <script>
            window.App = {
                stripePublicKey: "{{ config('services.stripe.key') }}",
            }
        </script>
    </head>
    <body class="bg-dark">
        <div id="app">@yield('body')</div>

        <script src="https://checkout.stripe.com/checkout.js"></script>
        <script src="{{ mix('js/app.js') }}"></script>
    </body>
</html>
