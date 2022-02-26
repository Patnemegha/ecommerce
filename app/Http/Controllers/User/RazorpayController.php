<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Gloudemans\Shoppingcart\Facades\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Auth;
use Carbon\Carbon; 

use Illuminate\Support\Facades\Mail;
use App\Mail\OrderMail;
use Razorpay\Api\Api;
use Exception;

class RazorpayController extends Controller
{
    public function RazorpayOrder(Request $request){    
    
        if (Session::has('coupon')) {
            $total_amount = Session::get('coupon')['total_amount'];
        }else{
            $total_amount = round(Cart::total());
        }
        $input = $request->all();
        // razorpay code start
        $api = new Api(env('RAZORPAY_KEY'), env('RAZORPAY_SECRET'));
  
        $payment = $api->payment->fetch($input['razorpay_payment_id']);

        if(count($input)  && !empty($input['razorpay_payment_id'])) {
			DB::beginTransaction();
            try {
                $response = $api->payment->fetch($input['razorpay_payment_id'])->capture(array('amount'=>$payment['amount'])); 
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
                         	'payment_method' => $payment->description,
                         	'payment_type' => $payment->description,
                         	'transaction_id' => $input['razorpay_payment_id'],
							'razorpay_payment_id' => $input['razorpay_payment_id'],
                         	'currency' => $payment->currency,
                            'amount'=> $total_amount,
                         	'order_number' => $payment->id,
                    
                         	'invoice_no' => 'EOS'.mt_rand(10000000,99999999),
                         	'order_date' => Carbon::now()->format('d F Y'),
                         	'order_month' => Carbon::now()->format('F'),
                         	'order_year' => Carbon::now()->format('Y'),
                         	'status' => 'pending',
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
                    
                         	Mail::to($request->email)->send(new OrderMail($data));
                    
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
                    
                    
                         if (Session::has('coupon')) {
                         	Session::forget('coupon');
                         }
                    
                         Cart::destroy();
						 DB::commit();
                         $notification = array(
                    			'message' => 'Your Order Place Successfully',
                    			'alert-type' => 'success'
                    		);
                    
                    		return redirect()->route('dashboard')->with($notification);
          
            } catch (Exception $e) {
				DB::rollback();
                return  $e->getMessage();
                Session::put('error',$e->getMessage());
                return redirect()->back();
            }
        }
          
        Session::put('success', 'Payment successful');
        return redirect()->back();
    }

}
 