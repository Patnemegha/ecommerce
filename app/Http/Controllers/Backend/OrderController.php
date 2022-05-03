<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Auth;
use Carbon\Carbon;
use PDF;
use DB;
use Exception;
 

class OrderController extends Controller
{
    
	// Pending Orders 
	public function PendingOrders(){
		$orders = Order::where('status','pending')->orderBy('id','DESC')->get();
		return view('backend.orders.pending_orders',compact('orders'));

	} // end mehtod 


	// Pending Order Details 
	public function PendingOrdersDetails($order_id){

		$order = Order::with('division','district','state','user')->where('id',$order_id)->first();
    	$orderItem = OrderItem::with('product')->where('order_id',$order_id)->orderBy('id','DESC')->get();
    
        return view('backend.orders.pending_orders_details',compact('order','orderItem'));

	} // end method 



	// Confirmed Orders 
	public function ConfirmedOrders(){
		
		$orders = Order::where('status','confirm')->orderBy('id','DESC')->get();
		return view('backend.orders.confirmed_orders',compact('orders'));

	} // end mehtod 


	// Processing Orders 
	public function ProcessingOrders(){
		$orders = Order::where('status','processing')->orderBy('id','DESC')->get();
		return view('backend.orders.processing_orders',compact('orders'));

	} // end mehtod 


		// Picked Orders 
	public function PickedOrders(){
		$orders = Order::where('status','picked')->orderBy('id','DESC')->get();
		return view('backend.orders.picked_orders',compact('orders'));

	} // end mehtod 



			// Shipped Orders 
	public function ShippedOrders(){
		$orders = Order::where('status','shipped')->orderBy('id','DESC')->get();
		return view('backend.orders.shipped_orders',compact('orders'));

	} // end mehtod 


			// Delivered Orders 
	public function DeliveredOrders(){
		$orders = Order::where('status','delivered')->orderBy('id','DESC')->get();
		return view('backend.orders.delivered_orders',compact('orders'));

	} // end mehtod 


				// Cancel Orders 
	public function CancelOrders(){
		$orders = Order::where('status','cancel')->orderBy('id','DESC')->get();
		return view('backend.orders.cancel_orders',compact('orders'));

	} // end mehtod 


	// Return Orders 
	public function ReturnOrders(){
		$orders = Order::where('return_reason','!=',Null)->orderBy('id','DESC')->get();
		
		return view('backend.orders.return_orders',compact('orders'));

	} // end mehtod 
				
	


	public function PendingToConfirm($order_id){
    Order::findOrFail($order_id)->update(['status' => 'confirm','confirmed_date' =>  Carbon::now()->format('Y-m-d H:i:s')]);
	

      $notification = array(
			'message' => 'Order Confirm Successfully',
			'alert-type' => 'success'
		);

		return redirect()->route('pending-orders')->with($notification);


	} // end method

	public function ReturnPendingToConfirm($order_id){
   
   //get order quantity 
   $quanty = DB::table('order_items')->where('order_id','=',$order_id)->select('qty','product_id')->first();

   //get old product quantity
   $old_product_qty = DB::table('products')->where('id','=',$quanty->product_id)->select('product_qty')->first();

   //update product
   $total_product_quanty=($old_product_qty->product_qty + $quanty->qty);
		
   DB::beginTransaction();
		try {
			//update product table
			$product = Product::find($quanty->product_id);
			$product->product_qty = $total_product_quanty;
			$product->updated_at = Carbon::now();
			$product->update();

		Order::findOrFail($order_id)->update(['return_order' => 2]);
		DB::commit();
		$notification = array(
			  'message' => 'Order Confirm Successfully',
			  'alert-type' => 'success'
		  );
  
		  return redirect()->back()->with($notification);
  
		}catch (Exception $e) {
			DB::rollback();
			return  $e->getMessage();
			Session::put('error',$e->getMessage());
			return redirect()->back();
		}
	  } // end method
	


	public function ConfirmToProcessing($order_id){
   
      Order::findOrFail($order_id)->update(['status' => 'processing','processing_date' =>  Carbon::now()->format('Y-m-d H:i:s')]);

      $notification = array(
			'message' => 'Order Processing Successfully',
			'alert-type' => 'success'
		);

		return redirect()->route('confirmed-orders')->with($notification);


	} // end method



		public function ProcessingToPicked($order_id){
   
      Order::findOrFail($order_id)->update(['status' => 'picked','picked_date' =>  Carbon::now()->format('Y-m-d H:i:s')]);

      $notification = array(
			'message' => 'Order Picked Successfully',
			'alert-type' => 'success'
		);

		return redirect()->route('processing-orders')->with($notification);


	} // end method


	 public function PickedToShipped($order_id){
   
      Order::findOrFail($order_id)->update(['status' => 'shipped','shipped_date' =>  Carbon::now()->format('Y-m-d H:i:s')]);

      $notification = array(
			'message' => 'Order Shipped Successfully',
			'alert-type' => 'success'
		);

		return redirect()->route('picked-orders')->with($notification);


	} // end method


	 public function ShippedToDelivered($order_id){

	 $product = OrderItem::where('order_id',$order_id)->get();
	 foreach ($product as $item) {
	 	Product::where('id',$item->product_id)
	 			->update(['product_qty' => DB::raw('product_qty-'.$item->qty)]);
	 } 
 
      Order::findOrFail($order_id)->update(['status' => 'delivered','delivered_date' =>  Carbon::now()->format('Y-m-d H:i:s')]);

      $notification = array(
			'message' => 'Order Delivered Successfully',
			'alert-type' => 'success'
		);

		return redirect()->route('shipped-orders')->with($notification);


	} // end method


	public function AdminInvoiceDownload($order_id){

		$order = Order::with('division','district','state','user')->where('id',$order_id)->first();
    	$orderItem = OrderItem::with('product')->where('order_id',$order_id)->orderBy('id','DESC')->get();
    	 
		$pdf = PDF::loadView('backend.orders.order_invoice',compact('order','orderItem'))->setPaper('a4')->setOptions([
				'tempDir' => public_path(),
				'chroot' => public_path(),
		]);
		return $pdf->download('invoice.pdf');

	} // end method 



}
 