<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use DB;

class ProductsController extends Controller
{
    public function index(Request $request) {
		if($request->session()->has('id')) {
			$id = $request->session()->get('id');

	    	$users = DB::select('select * from users where id = ?', [$id]);
            foreach($users as $user) {
				$current_user = $user;
			}
		} else {
	    	DB::insert('insert into users (cash) values(?)',[100]);

	    	$users = DB::select('select * from users order by id desc limit 1');
            foreach($users as $user) {
				$request->session()->put('id', $user->id);
				$current_user = $user;
			}
		}

    	$products = DB::select('select distinct rates.user_id, rates.product_id, avg(rates.vote) as rate_vote, products.id, products.title, products.price from rates, products where rates.product_id = products.id group by rates.product_id, products.id');

    	$carts = DB::select('select * from carts where user_id = ?', [$current_user->id]);

    	$rates = DB::select('select * from rates where user_id = ?', [$current_user->id]);
    	$json_rates = json_encode($rates);

		return view('products', ['current_user' => $current_user, 'products' => $products, 'carts' => $carts, 'json_rates' => $json_rates]);
	}

    public function vote(Request $request) {
    	$user_id = $request->input('user_id');
    	$vote_id = $request->input('vote_id');
    	$product_id = $request->input('product_id');

    	$rates = DB::select('select * from rates where user_id = ? and product_id = ?', [$user_id, $product_id]);
    	$rate_l = count($rates);
		if($rate_l > 0) {
			DB::update('update rates set vote = ? where user_id = ? and product_id = ?', [$vote_id, $user_id, $product_id]);
		} else {
	    	DB::insert('insert into rates (user_id, product_id, vote) values(?, ?, ?)', [$user_id, $product_id, $vote_id]);
		}

    	$rates = DB::select('select distinct rates.user_id, rates.product_id, avg(rates.vote) as rate_vote, products.id, products.title, products.price from rates, products where rates.product_id = ? and rates.product_id = products.id group by rates.product_id, products.id', [$product_id]);
    	$json_rates = json_encode($rates);

    	return response()->json(['user_id' => $user_id, 'vote_id' => $vote_id, 'product_id' => $product_id, 'json_rates' => $json_rates]);
	}
}
