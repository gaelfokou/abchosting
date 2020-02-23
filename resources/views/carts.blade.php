<!doctype html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Your Cart</title>

        <link href="{{ url('/css/app.css') }}" rel="stylesheet" type="text/css">

        <script src="{{ url('/js/jquery.js') }}"></script>
        <script src="{{ url('/js/app.js') }}"></script>

        <script type="text/javascript">
            $(document).ready(function() {
                $('input[name="update"]').click(function(event) {
                    event.preventDefault();
                    var user_id = '{{ $current_user->id }}';
                    var product_id = $(this).attr('product-id');
                    var product_title = $(this).attr('product-title');
                    var product_qty = $('#product-' + product_id).val();
                    var is_sure = confirm('You want to put "' + product_qty + ' ' + product_title + '" in your cart?');
                    if(is_sure) {
                        $.ajax({
                            url: '{{ url('/carts/update') }}',
                            type: 'get',
                            data: {user_id: user_id, product_id: product_id, product_qty: product_qty},
                            dataType: 'JSON',
                            headers: {
                                'Content-Type': 'application/json',
                                'x-client-id': '0000',
                                'x-access-token': '<?php echo csrf_token(); ?>'
                            }
                        })
                        .done(function(data, textStatus, jqXHR) {
                            console.log(data);
                            $('.cart').html('Cart (' + data.product_l + ')');
                            $('.total .red-text').html(data.total + '$');
                            $('.success').html('Your cart is updated successfully');
                            $('.info').fadeIn(250);
                            setTimeout(function() {
                                $('.info').fadeOut(500);
                            }, 1500);
                        })
                        .fail(function(jqXHR, textStatus, errorThrown) {
                            var errors = $.parseJSON(jqXHR.responseText).errors;
                            console.log(errors);
                        });
                    }
                });

                $('input[name="remove"]').click(function(event) {
                    event.preventDefault();
                    var user_id = '{{ $current_user->id }}';
                    var product_id = $(this).attr('product-id');
                    var product_title = $(this).attr('product-title');
                    var is_sure = confirm('You want to delete "' + product_title + '" in your cart?');
                    if(is_sure) {
                        $.ajax({
                            url: '{{ url('/carts/destroy') }}',
                            type: 'get',
                            data: {user_id: user_id, product_id: product_id},
                            dataType: 'JSON',
                            headers: {
                                'Content-Type': 'application/json',
                                'x-client-id': '0000',
                                'x-access-token': '<?php echo csrf_token(); ?>'
                            }
                        })
                        .done(function(data, textStatus, jqXHR) {
                            console.log(data);
                            $('#' + data.product_id).remove();
                            $('#icon-' + data.product_id).remove();
                            $('.cart').html('Cart (' + data.product_l + ')');
                            $('.total .red-text').html(data.total + '$');
                            $('.success').html('Your cart is updated successfully');
                            $('.info').fadeIn(250);
                            setTimeout(function() {
                                $('.info').fadeOut(500);
                            }, 1500);
                        })
                        .fail(function(jqXHR, textStatus, errorThrown) {
                            var errors = $.parseJSON(jqXHR.responseText).errors;
                            console.log(errors);
                        });
                    }
                });

                $('input[name="pay"]').click(function(event) {
                    event.preventDefault();
                    var pay = $(this);
                    var user_id = '{{ $current_user->id }}';
                    var transport_id = $('select[name="transport"]').val();
                    if(transport_id != '') {
                        var is_sure = confirm('You want to pay your order?');
                        if(is_sure) {
                            $.ajax({
                                url: '{{ url('/carts/pay') }}',
                                type: 'get',
                                data: {user_id: user_id, transport_id: transport_id},
                                dataType: 'JSON',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'x-client-id': '0000',
                                    'x-access-token': '<?php echo csrf_token(); ?>'
                                }
                            })
                            .done(function(data, textStatus, jqXHR) {
                                console.log(data);
                                if(data.product_l > 0) {
                                    if(data.status == 1) {
                                        pay.attr("disabled", "disabled");
                                        $('.success').html('Your order is paid successfully');
                                        $('.info').fadeIn(250);
                                        setTimeout(function() {
                                            $('.info').fadeOut(500);
                                            setTimeout(function() {
                                                window.location.href = '{{ url("/carts/billing") }}';
                                            }, 1500);
                                        }, 1500);
                                    } else {
                                        $('.success').html('Your balance is insufficient');
                                        $('.info').fadeIn(250);
                                        setTimeout(function() {
                                            $('.info').fadeOut(500);
                                        }, 1500);
                                    }
                                } else {
                                    $('.success').html('Your cart is empty');
                                    $('.info').fadeIn(250);
                                    setTimeout(function() {
                                        $('.info').fadeOut(500);
                                    }, 1500);
                                }
                            })
                            .fail(function(jqXHR, textStatus, errorThrown) {
                                var errors = $.parseJSON(jqXHR.responseText).errors;
                                console.log(errors);
                            });
                        }
                    } else {
                        $('.success').html('Select Transport Type');
                        $('.info').fadeIn(250);
                        setTimeout(function() {
                            $('.info').fadeOut(500);
                        }, 1500);
                    }
                });

                $('.close').click(function() {
                    $('.info').fadeOut(500);
                });
            });
        </script>
    </head>
    <body>
        <div class="flex-center position-ref full-height">
            <div class="content">
                <div class="info">
                    <p class="success"></p>
                    <p class="close">X</p>
                </div>

                <div class="title m-b-md">Your Cart</div>

                <ul class="menu">
                    <li><a href="{{ url('/products') }}">Products</a></li>
                    <li><a href="{{ url('/carts') }}" class="cart">Cart ({{ count($carts) }})</a></li>
                    <li><a href="javascript:;" class="account">Your Balance ({{ $current_user->cash }}$)</a></li>
                </ul>

                <ul class="links">
                    <li class="title"><p>Product</p> <p>Price</p></li>
                    @foreach ($products as $product)
                        <li id="{{ $product->id }}"><p>{{ $product->title }}</p> <p class="red-text">{{ $product->price }}$</p> <input type="button" name="remove" value="Remove" product-id="{{ $product->id }}" product-title="{{ $product->title }}" /> <input type="button" name="update" value="Update" product-id="{{ $product->id }}" product-title="{{ $product->title }}" /> <input type="number" name="quantity" value="{{ $product->quantity }}" id="product-{{ $product->id }}" /></li>
                        <li id="icon-{{ $product->id }}" class="icon"><img src="{{ url('/images/') }}/{{ $product->title }}.png" /></li>
                    @endforeach
                    <li class="total"><p>Total</p> <p class="red-text">{{ $total }}$</p> <input type="button" name="pay" value="Pay" />
                        <select name="transport">
                            <option value="">Select Transport Type</option>
                            <?php $transport_id = 0; foreach($carts as $cart) { $transport_id = $cart->transport_id; } ?>
                            @foreach ($transports as $transport)
                                @if($transport->id == $transport_id)
                                    <option value="{{ $transport->id }}" selected>{{ $transport->title }} ({{ $transport->price }}$)</option>
                                @else
                                    <option value="{{ $transport->id }}">{{ $transport->title }} ({{ $transport->price }}$)</option>
                                @endif
                            @endforeach
                        </select>
                    </li>
                </ul>
            </div>
        </div>
    </body>
</html>
