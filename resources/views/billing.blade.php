<!doctype html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Your Billing</title>

        <link href="{{ url('/css/app.css') }}" rel="stylesheet" type="text/css">

        <script src="{{ url('/js/jquery.js') }}"></script>
        <script src="{{ url('/js/app.js') }}"></script>
    </head>
    <body>
        <div class="flex-center position-ref full-height">
            <div class="content">
                <div class="info">
                    <p class="success"></p>
                    <p class="close">X</p>
                </div>

                <div class="title m-b-md">Your Billing</div>

                <ul class="menu">
                    <li><a href="{{ url('/products') }}">Products</a></li>
                    <li><a href="{{ url('/carts') }}" class="cart">Cart (0)</a></li>
                    <li><a href="javascript:;" class="account">Your Balance ({{ $current_user->cash }}$)</a></li>
                </ul>

                <ul class="links">
                    <li class="title"><p>Product</p> <p>Price</p> <p>Quantity</p></li>
                    @foreach ($products as $product)
                        <li id="{{ $product->product_id }}"><p>{{ $product->product_title }}</p> <p class="red-text">{{ $product->product_price }}$</p> <p>{{ $product->quantity }}</p></li>
                        <li class="icon"><img src="{{ url('/images/') }}/{{ $product->product_title }}.png" /></li>
                    @endforeach
                    <li><p>{{ $transport_title }}</p> <p class="red-text">{{ $transport_price }}$</p></li>
                    <li class="icon"><img src="{{ url('/images/') }}/{{ $transport_title }}.png" /></li>
                    <li class="total"><p>Total</p> <p class="red-text">{{ $total }}$</p></li>
                </ul>
            </div>
        </div>
    </body>
</html>
