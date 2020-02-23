<!doctype html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Internet Shop</title>

        <link href="{{ url('/css/rating.css') }}" rel="stylesheet" type="text/css">
        <link href="{{ url('/css/app.css') }}" rel="stylesheet" type="text/css">

        <script src="{{ url('/js/jquery.js') }}"></script>
        <script src="{{ url('/js/rating.js') }}"></script>
        <script src="{{ url('/js/app.js') }}"></script>

        <script type="text/javascript">
            $(document).ready(function() {
                $('input[name="add"]').click(function(event) {
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

                $('.close').click(function() {
                    $('.info').fadeOut(500);
                });

                $('.ratings').rating(function(vote, event){
                    console.log(vote);
                    var user_id = '{{ $current_user->id }}';
                    var rating = vote.split('-');
                    var vote_id = rating[0];
                    var product_id = rating[1];

                    $('.ratings[product="' + product_id + '"] .stars').unbind();
                    $('.ratings[product="' + product_id + '"] .stars .star').unbind();

                    $.ajax({
                        url: '{{ url('/products/vote') }}',
                        type: 'get',
                        data: {user_id: user_id, vote_id: vote_id, product_id: product_id},
                        dataType: 'JSON',
                        headers: {
                            'Content-Type': 'application/json',
                            'x-client-id': '0000',
                            'x-access-token': '<?php echo csrf_token(); ?>'
                        }
                    })
                    .done(function(data, textStatus, jqXHR) {
                        console.log(data);

                        var json_rates = JSON.parse(data.json_rates);
                        var product_id = data.product_id;

                        $('.avg-ratings[product="' + product_id + '"]').each(function (event) {
                            var product = $(this).attr('product');
                            var exist = false;
                            var rating = 0;
                            for(var i = 0; (i < json_rates.length); i++) {
                                if(json_rates[i].product_id == product) {
                                    rating = json_rates[i].rate_vote;
                                    exist = true;
                                    break;
                                }
                            }
                            if(exist) {
                                for(var i = 0; (i < rating); i++) {
                                    var val = $(this).find('input.rating').eq(i).val();
                                    $(this).find('a[title="' + val + '"]').addClass('filled');
                                }

                                $('.avg-ratings[product="' + product + '"] .stars').unbind();
                                $('.avg-ratings[product="' + product + '"] .stars .star').unbind();
                            }
                        });
                    })
                    .fail(function(jqXHR, textStatus, errorThrown) {
                        var errors = $.parseJSON(jqXHR.responseText).errors;
                        console.log(errors);
                    });
                });

                var json_rates = JSON.parse('<?= $json_rates; ?>');

                $('.ratings').each(function (event) {
                    var product = $(this).attr('product');
                    var exist = false;
                    var rating = 0;
                    for(var i = 0; (i < json_rates.length); i++) {
                        if(json_rates[i].product_id == product) {
                            rating = json_rates[i].vote;
                            exist = true;
                            break;
                        }
                    }
                    if(exist) {
                        for(var i = 0; (i < rating); i++) {
                            var val = $(this).find('input.rating').eq(i).val();
                            $(this).find('a[title="' + val + '"]').addClass('filled');
                        }

                        $('.ratings[product="' + product + '"] .stars').unbind();
                        $('.ratings[product="' + product + '"] .stars .star').unbind();
                    }
                });

                $('.avg-ratings').rating();

                $('.avg-ratings').each(function (event) {
                    var rating = $(this).attr('rating');
                    for(var i = 0; (i < rating); i++) {
                        var val = $(this).find('input.rating').eq(i).val();
                        $(this).find('a[title="' + val + '"]').addClass('filled');
                    }
                });

                $('.avg-ratings .stars').unbind();
                $('.avg-ratings .stars .star').unbind();
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

                <div class="title m-b-md">Internet Shop</div>

                <ul class="menu">
                    <li><a href="{{ url('/products') }}">Products</a></li>
                    <li><a href="{{ url('/carts') }}" class="cart">Cart ({{ count($carts) }})</a></li>
                    <li><a href="javascript:;" class="account">Your Balance ({{ $current_user->cash }}$)</a></li>
                </ul>

                <ul class="links">
                        <li class="title"><p>Product</p> <p>Price</p></li>
                    @foreach ($products as $product)
                        <li><p>{{ $product->title }}</p> <p class="red-text">{{ $product->price }}$</p> <input type="button" name="add" value="Add to Cart" product-id="{{ $product->id }}" product-title="{{ $product->title }}" /> <input type="number" name="quantity" value="0" id="product-{{ $product->id }}" />
                            <div class="ratings" product="{{ $product->id }}">
                                <input type="radio" name="rating" class="rating" value="1-{{ $product->id }}" />
                                <input type="radio" name="rating" class="rating" value="2-{{ $product->id }}" />
                                <input type="radio" name="rating" class="rating" value="3-{{ $product->id }}" />
                                <input type="radio" name="rating" class="rating" value="4-{{ $product->id }}" />
                                <input type="radio" name="rating" class="rating" value="5-{{ $product->id }}" />
                            </div>
                            <div class="avg-ratings" product="{{ $product->id }}" rating="{{ $product->rate_vote }}">
                                <input type="radio" name="rating" class="rating" value="1-{{ $product->id }}" />
                                <input type="radio" name="rating" class="rating" value="2-{{ $product->id }}" />
                                <input type="radio" name="rating" class="rating" value="3-{{ $product->id }}" />
                                <input type="radio" name="rating" class="rating" value="4-{{ $product->id }}" />
                                <input type="radio" name="rating" class="rating" value="5-{{ $product->id }}" />
                            </div>
                            <!-- <p class="rating">{{ $product->rate_vote }}</p> -->
                        </li>
                        <li class="icon product"><img src="{{ url('/images/') }}/{{ $product->title }}.png" /></li>
                    @endforeach
                </ul>
            </div>
        </div>
    </body>
</html>
