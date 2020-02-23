<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use DB;

class CartsController extends Controller
{
    public function index(Request $request) {
		if($request->session()->has('id')) {
			$id = $request->session()->get('id');
	  
	    	$users = DB::select('select * from users where id = ?', [$id]);
            foreach($users as $user) {
				$current_user = $user;
			}
		} else {
	    	DB::insert('insert into users (cash) values(?)', [100]);
	    
	    	$users = DB::select('select * from users order by id desc limit 1');
            foreach($users as $user) {
				$request->session()->put('id', $user->id);
				$current_user = $user;
			}
		}

    	$products = DB::select('select carts.user_id, carts.product_id, carts.quantity, products.id, products.title, products.price from carts, products where carts.user_id = ? and carts.product_id = products.id', [$current_user->id]);

    	$total = 0;
        foreach($products as $product) {
	    	$total += $product->price * $product->quantity;
		}

    	$carts = DB::select('select * from carts where user_id = ?', [$current_user->id]);

    	$transports = DB::select('select * from transports');

		return view('carts', ['current_user' => $current_user, 'products' => $products, 'total' => $total, 'carts' => $carts, 'transports' => $transports]);
	}

    public function update(Request $request) {
    	$user_id = $request->input('user_id');
    	$product_id = $request->input('product_id');
    	$product_qty = $request->input('product_qty');

    	$carts = DB::select('select * from carts where user_id = ? and product_id = ?', [$user_id, $product_id]);
    	$cart_l = count($carts);
		if($cart_l > 0) {
			DB::update('update carts set quantity = ? where user_id = ? and product_id = ?', [$product_qty, $user_id, $product_id]);
		} else {
	    	DB::insert('insert into carts (user_id, product_id, quantity) values(?, ?, ?)', [$user_id, $product_id, $product_qty]);
		}

    	$products = DB::select('select carts.user_id, carts.product_id, carts.quantity, products.id, products.title, products.price from carts, products where carts.user_id = ? and carts.product_id = products.id', [$user_id]);
    	$product_l = count($products);

    	$total = 0;
        foreach($products as $product) {
	    	$total += $product->price * $product->quantity;
		}

    	return response()->json(['user_id' => $user_id, 'product_id' => $product_id, 'product_qty' => $product_qty, 'product_l' => $product_l, 'total' => $total]);
	}

    public function destroy(Request $request) {
    	$user_id = $request->input('user_id');
    	$product_id = $request->input('product_id');

    	$carts = DB::select('select * from carts where user_id = ? and product_id = ?', [$user_id, $product_id]);
    	$cart_l = count($carts);
		if($cart_l > 0) {
	    	DB::delete('delete from carts where user_id = ? and product_id = ?', [$user_id, $product_id]);
		}

    	$products = DB::select('select carts.user_id, carts.product_id, carts.quantity, products.id, products.title, products.price from carts, products where carts.user_id = ? and carts.product_id = products.id', [$user_id]);
    	$product_l = count($products);

    	$total = 0;
        foreach($products as $product) {
	    	$total += $product->price * $product->quantity;
		}

    	return response()->json(['user_id' => $user_id, 'product_id' => $product_id, 'product_l' => $product_l, 'total' => $total]);
	}

    public function pay(Request $request) {
    	$user_id = $request->input('user_id');
    	$transport_id = $request->input('transport_id');

    	$users = DB::select('select * from users where id = ?', [$user_id]);
        foreach($users as $user) {
			$current_user = $user;
		}

    	$products = DB::select('select carts.user_id, carts.product_id, carts.quantity, products.id, products.title, products.price from carts, products where carts.user_id = ? and carts.product_id = products.id', [$user_id]);
    	$product_l = count($products);

    	$total = 0;
        foreach($products as $product) {
	    	$total += $product->price * $product->quantity;
		}

    	$transports = DB::select('select * from transports where id = ?', [$transport_id]);

        foreach($transports as $transport) {
	    	$total += $transport->price;
	    	$status = 0;
			if($current_user->cash >= $total) {
				DB::update('update carts set transport_id = ? where user_id = ?', [$transport->id, $user_id]);
		    	$status = 1;
			}
		}

    	return response()->json(['user_id' => $user_id, 'transport_id' => $transport_id, 'product_l' => $product_l, 'status' => $status]);
	}

    public function billing(Request $request) {
		if($request->session()->has('id')) {
			$id = $request->session()->get('id');
	  
	    	$users = DB::select('select * from users where id = ?', [$id]);
            foreach($users as $user) {
				$current_user = $user;
			}
		}

    	$products = DB::select('select carts.user_id, carts.product_id, carts.quantity, products.id as product_id, products.title as product_title, products.price as product_price, transports.id as transport_id, transports.title as transport_title, transports.price as transport_price from carts, products, transports where carts.user_id = ? and carts.product_id = products.id and carts.transport_id = transports.id', [$current_user->id]);

    	$total = 0;
        foreach($products as $product) {
	    	$total += $product->product_price * $product->quantity;
	    	$transport_price = $product->transport_price;
	    	$transport_title = $product->transport_title;
		}
    	$total += $transport_price;

		DB::update('update users set cash = ? where id = ?', [$current_user->cash - $total, $current_user->id]);
	  
    	$users = DB::select('select * from users where id = ?', [$current_user->id]);
        foreach($users as $user) {
			$current_user = $user;
		}

    	$carts = DB::select('select * from carts where user_id = ?', [$current_user->id]);
    	$cart_l = count($carts);
		if($cart_l > 0) {
	    	DB::delete('delete from carts where user_id = ?', [$current_user->id]);
		}

    	$transports = DB::select('select * from transports');

		return view('billing', ['current_user' => $current_user, 'products' => $products, 'total' => $total, 'carts' => $carts, 'transports' => $transports, 'transport_price' => $transport_price, 'transport_title' => $transport_title]);
	}
}
