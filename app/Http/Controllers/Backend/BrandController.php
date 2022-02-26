<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Brand;
use Image;
use Carbon\Carbon;

class BrandController extends Controller
{
    public function BrandView(){
		try{
    	$brands = Brand::latest()->get();
    	return view('backend.brand.brand_view',compact('brands'));
	}catch (Exception $e) {
		return  $e->getMessage();
		Session::put('error',$e->getMessage());
		return redirect()->back();
	    }
    }

    public function BrandStore(Request $request){

		try{
			
    	$request->validate([
    		'brand_name_en' => 'required',
    		'brand_name_hin' => 'required',
    		'brand_image' => 'required',
    	],[
    		'brand_name_en.required' => 'Input Brand English Name',
    		'brand_name_hin.required' => 'Input Brand Hindi Name',
    	]);

    	$image = $request->file('brand_image');
    	$name_gen = hexdec(uniqid()).'.'.$image->getClientOriginalExtension();
    	Image::make($image)->resize(300,300)->save('upload/brand/'.$name_gen);
    	$save_url = 'upload/brand/'.$name_gen;

     	Brand::insert([
		'brand_name_en' => $request->brand_name_en,
		'brand_name_hin' => $request->brand_name_hin,
		'brand_slug_en' => strtolower(str_replace(' ', '-',$request->brand_name_en)),
		'brand_slug_hin' => str_replace(' ', '-',$request->brand_name_hin),
		'brand_image' => $save_url,
		'created_at' => Carbon::now(), 
    	]);

	    $notification = array(
			'message' => 'Brand Inserted Successfully',
			'alert-type' => 'success'
		);

		return redirect()->back()->with($notification);
	}catch (Exception $e) {
		return  $e->getMessage();
		Session::put('error',$e->getMessage());
		return redirect()->back();
	    }
    } // end method 


	public function BrandEdit($id){
		try{
    	$brand = Brand::findOrFail($id);
    	return view('backend.brand.brand_edit',compact('brand'));
	}catch (Exception $e) {
		return  $e->getMessage();
		Session::put('error',$e->getMessage());
		return redirect()->back();
	    }
    }

 
    public function BrandUpdate(Request $request){
    	try{
    	$brand_id = $request->id;
    	$old_img = $request->old_image;

    	if ($request->file('brand_image')) {

    	unlink($old_img);
    	$image = $request->file('brand_image');
    	$name_gen = hexdec(uniqid()).'.'.$image->getClientOriginalExtension();
    	Image::make($image)->resize(300,300)->save('upload/brand/'.$name_gen);
    	$save_url = 'upload/brand/'.$name_gen;

	    Brand::findOrFail($brand_id)->update([
		'brand_name_en' => $request->brand_name_en,
		'brand_name_hin' => $request->brand_name_hin,
		'brand_slug_en' => strtolower(str_replace(' ', '-',$request->brand_name_en)),
		'brand_slug_hin' => str_replace(' ', '-',$request->brand_name_hin),
		'brand_image' => $save_url,
		'updated_at' => Carbon::now(), 

    	]);

	    $notification = array(
			'message' => 'Brand Updated Successfully',
			'alert-type' => 'info'
		);

		return redirect()->route('all.brand')->with($notification);

    	}else{

    	Brand::findOrFail($brand_id)->update([
		'brand_name_en' => $request->brand_name_en,
		'brand_name_hin' => $request->brand_name_hin,
		'brand_slug_en' => strtolower(str_replace(' ', '-',$request->brand_name_en)),
		'brand_slug_hin' => str_replace(' ', '-',$request->brand_name_hin),
		'updated_at' => Carbon::now(),   
	
    	]);

	    $notification = array(
			'message' => 'Brand Updated Successfully',
			'alert-type' => 'info'
		);

		return redirect()->route('all.brand')->with($notification);

    	} // end else 
	}catch (Exception $e) {
		return  $e->getMessage();
		Session::put('error',$e->getMessage());
		return redirect()->back();
	    }
    } // end method 



    public function BrandDelete($id){
    try{
    	$brand = Brand::findOrFail($id);
    	$img = $brand->brand_image;
    	unlink($img);

    	Brand::findOrFail($id)->delete();

    	 $notification = array(
			'message' => 'Brand Deleted Successfully',
			'alert-type' => 'info'
		);

		return redirect()->back()->with($notification);
	}catch (Exception $e) {
		return  $e->getMessage();
		Session::put('error',$e->getMessage());
		return redirect()->back();
	    }

    } // end method 


}
