<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Gloudemans\Shoppingcart\Facades\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Facades\Session;
use Auth;
use Carbon\Carbon; 

use Illuminate\Support\Facades\Mail;
use App\Mail\OrderMail;
use DB;
use Exception;

class CashController extends Controller
{
     public function CashOrder(Request $request){


    	if (Session::has('coupon')) {
    		$total_amount = Session::get('coupon')['total_amount'];
    	}else{
    		$total_amount = round(Cart::total());
    	}
 
	 

		DB::beginTransaction();
		try {
     $order_id = Order::insertGetId([
     	'user_id' => Auth::id(),
     	'division_id' => $request->division_id,
     	'district_id' => $request->district_id,
     	'state_id' => $request->state_id,
     	'name' => $request->name,
     	'email' => $request->email,
     	'phone' => $request->phone,
     	'post_code' => $request->post_code,
     	'notes' => $request->notes,

     	'payment_type' => 'Cash On Delivery',
     	'payment_method' => 'Cash On Delivery',
     	 
     	'currency' =>  'Usd',
     	'amount' => $total_amount,
     	 

     	'invoice_no' => 'EOS'.mt_rand(10000000,99999999),
     	'order_date' => Carbon::now()->format('d F Y'),
     	'order_month' => Carbon::now()->format('F'),
     	'order_year' => Carbon::now()->format('Y'),
     	'status' => 'Pending',
     	'created_at' => Carbon::now(),	 

     ]);

     // Start Send Email 
     $invoice = Order::findOrFail($order_id);
     	$data = [
     		'invoice_no' => $invoice->invoice_no,
     		'amount' => $total_amount,
     		'name' => $invoice->name,
     	    'email' => $invoice->email,
     	];


     // End Send Email 


     $carts = Cart::content();
     foreach ($carts as $cart) {
     	OrderItem::insert([
     		'order_id' => $order_id, 
     		'product_id' => $cart->id,
     		'color' => $cart->options->color,
     		'size' => $cart->options->size,
     		'qty' => $cart->qty,
     		'price' => $cart->price,
     		'created_at' => Carbon::now(),

     	]);
     }
	 foreach ($carts as $cart) {
		//get product old quantity
		$old_product_qty = DB::table('products')->where('id','=',$cart->id)->select('product_qty')->first();
	
		$remaining_product_quanty=($old_product_qty->product_qty - $cart->qty);
		
		//update product table
		$product = Product::find($cart->id);
		$product->product_qty = $remaining_product_quanty;
		$product->updated_at = Carbon::now();
		$product->update();
		
 }
 
      if (Session::has('coupon')) {
     	Session::forget('coupon');
     }
	 
     Cart::destroy();
	 DB::commit();
	 Mail::to($request->email)->send(new OrderMail($data));

	 

	 $notification = array(
		'message' => 'Your Order Place Successfully',
		'alert-type' => 'success'
	);

	return redirect()->route('dashboard')->with($notification);
	
	}catch (Exception $e) {
		DB::rollback();
		return  $e->getMessage();
		Session::put('error',$e->getMessage());
		return redirect()->back();
	}
	 

   
 

    } // end method 





}
 