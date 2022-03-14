<?php

namespace App\Http\Controllers\Store;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB;
use Session;
use Auth;
use Illuminate\Support\Facades\Storage;

class PromotionalBannerController extends Controller
{
    public function __construct(){
        $storage =  DB::table('image_space')
                    ->first();

        if($storage->aws == 1){
            $this->storage_space = "s3.aws";
        }
        else if($storage->digital_ocean == 1){
            $this->storage_space = "s3.digitalocean";
        }else{
            $this->storage_space ="same_server";
        }

    }
    public function bannerlist(Request $request)
    {
        $title = "Banner";
         $email=Auth::guard('store')->user()->email;
    	 $store= DB::table('store')
    	 		   ->where('email',$email)
    	 		   ->first();
    	  $logo = DB::table('tbl_web_setting')
                ->where('set_id', '1')
                ->first();
        
        $city = DB::table('promo_banner')
               ->where('promo_banner.store_id', $store->id)
                ->get();
                
         if($this->storage_space != "same_server"){
           $url_aws =  rtrim(Storage::disk($this->storage_space)->url('/'),"/");
        }          
        else{
            $url_aws=url('/');
        }        
                
        return view('store.banner.promo.bannerlist', compact('title','city','store','logo','email','url_aws'));    
        
        
    }
    public function banner(Request $request)
    {
        $title = "Add Promotional Banner";
         $email=Auth::guard('store')->user()->email;
    	 $store= DB::table('store')
    	 		   ->where('email',$email)
    	 		   ->first();
    	  $logo = DB::table('tbl_web_setting')
                ->where('set_id', '1')
                ->first();
        
        $city = DB::table('promo_banner')
        ->where('store_id', $store->id)
        ->get();
                         
        return view('store.banner.promo.addbanner', compact('title','city','store','logo','email'));    
        
        
    }
    public function banneradd(Request $request)
    {
        $title = "Home";
        
        $banner = $request->banner;
        $image = $request->image;
        $type = $request->type;
         $date=date('d-m-Y');
        $email=Auth::guard('store')->user()->email;
    	 $store= DB::table('store')
    	 		   ->where('email',$email)
    	 		   ->first();
        
        $this->validate(
            $request,
                [
                    
                    'banner'=>'required',
                    'image'=>'required|mimes:jpeg,png,jpg|max:2048',
                ],
                [
                    
                    'banner.required'=>'Banner Name Required',
                    'image.required'=>'Image Required',

                ]
        );



              if ($request->hasFile('image')) {
           $image = $request->image;
            $fileName = $image->getClientOriginalName();
            $fileName = str_replace(" ", "-", $fileName);
           

           if($this->storage_space != "same_server"){
                $image_name = $image->getClientOriginalName();
                $image = $request->file('image');
                $filePath = '/banner/'.$image_name;
                Storage::disk($this->storage_space)->put($filePath, fopen($request->file('image'), 'r+'), 'public');
            }
            else{
           
           $image->move('images/banner/'.$date.'/', $fileName);
            $filePath = '/images/banner/'.$date.'/'.$fileName;
        
            }


        } else {
            $filePath = 'N/A';
        }
        
        
    	 $insert = DB::table('promo_banner')
                    ->insert([
                        'banner_name'=>$banner,
                        'banner_image'=>$filePath,
                        'store_id'=>$store->id
                        ]);
     if($insert){
         return redirect()->back()->withSuccess(trans('keywords.Added Successfully'));
     }else{
         return redirect()->back()->withErrors(trans('keywords.Something Wents Wrong'));
     }

    }
    
    public function banneredit(Request $request)
    {
         $title = "Edit Promotional Banner";
          $email=Auth::guard('store')->user()->email;
    	 $store= DB::table('store')
    	 		   ->where('email',$email)
    	 		   ->first();
    	  $logo = DB::table('tbl_web_setting')
                ->where('set_id', '1')
                ->first();
        $banner_id = $request->banner_id;
        
        $city = DB::table('promo_banner')
                ->where('promo_banner.banner_id',$banner_id)
                ->where('promo_banner.store_id', $store->id)
                ->first();
          

         if($this->storage_space != "same_server"){
           $url_aws =  rtrim(Storage::disk($this->storage_space)->url('/'),"/");
        }          
        else{
            $url_aws=url('/');
        }      


        return view('store.banner.promo.banneredit', compact('title','city','store','email','logo','url_aws'));    
        
        
    }
    
    public function bannerupdate(Request $request)
    {
        $title = "Home";
          $email=Auth::guard('store')->user()->email;
    	 $store= DB::table('store')
    	 		   ->where('email',$email)
    	 		   ->first();
        $banner_id = $request->banner_id;
        $banner = $request->banner;
       $old_reward_image=$request->old_image;
        $type = $request->type;
       $date=date('d-m-Y'); 
        
         $this->validate(
            $request,
                [
                    
                    'banner'=>'required',
                ],
                [
                    
                    'banner.required'=>'Banner Name Required',

                ]
        );
        
        $getBanner = DB::table('promo_banner')
                        ->where('banner_id', $banner_id)
                        ->first();
        if($getBanner->banner_image != NULL){
        $image = $getBanner->banner_image;
        }else{
          $image = 'N/A'; 
        }
        if($request->hasFile('image')){
               $this->validate(
            $request,
                [
                    'image' => 'required|mimes:jpeg,png,jpg|max:2048',
                ],
                [
                    'image.required' => 'Choose Banner image.',
                ]
              );
            if(file_exists($image)){
                unlink($image);
            }
             $image = $request->image;
            $fileName = $image->getClientOriginalName();
            $fileName = str_replace(" ", "-", $fileName);
           

           if($this->storage_space != "same_server"){
                $image_name = $image->getClientOriginalName();
                $image = $request->file('image');
                $filePath = '/banner/'.$image_name;
                Storage::disk($this->storage_space)->put($filePath, fopen($request->file('image'), 'r+'), 'public');
            }
            else{
           
           $image->move('images/banner/'.$date.'/', $fileName);
            $filePath = '/images/banner/'.$date.'/'.$fileName;
        
            }


        }
        else{
            $filePath = $getBanner->banner_image;
        }

        
    	 $insert = DB::table('promo_banner')
    	            ->where('banner_id',$banner_id)
                    ->update([
                        'banner_name'=>$banner,
                        'banner_image'=>$filePath,
                         'store_id'=>$store->id
                        ]);
     
   if($insert){
         return redirect()->back()->withSuccess(trans('keywords.Updated Successfully'));
     }else{
         return redirect()->back()->withErrors(trans('keywords.Something Wents Wrong'));
     }

    }
    
    public function bannerdelete(Request $request)
    {
        $banner_id = $request->banner_id;

    	$delete=DB::table('promo_banner')->where('banner_id',$banner_id)->delete();
        if($delete){
             return redirect()->back()->withSuccess(trans('keywords.Deleted Successfully'));
         }else{
             return redirect()->back()->withErrors(trans('keywords.Something Wents Wrong'));
         }
    }
}