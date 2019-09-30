<?php

namespace App\Api\V1\Controllers;


use Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\DataProduct;
use App\Ussd;
use App\GeneratedPin;
use App\UsedPin;
use Carbon\Carbon;


class UssdController extends Controller
{
    public function index()
    {
      
    }


    public function get()
    {
      
    }


    public function create(Request $request)
    {
        $this->validateParameter('amount', $request->amount, STRING);
        $this->validateParameter('phone', $request->phone, STRING);
        $this->validateParameter('pin', $request->pin, STRING);
        $case = 'airtime';
        if($request->bundle_code){
          $case = 'data';
        }
        $user = Auth::User()->id;

        if ($case === 'airtime') {
            $ref = $this->generateKey(13);

            $ussd = new Ussd;
            $ussd->user_id = $user;
            $ussd->reference = $ref;
            $ussd->amount = $request->amount;
            $ussd->phone = $request->phone;
            $ussd->save();

            $data = [  
                "ref_code" => $ref,
                "ussd_code" => "*456*1*2*".$request->amount.'*'.$request->phone.'*2323#',
                "access_code" => "j7pdkl"
            ];


          
            $toPost = json_encode($data);
        
        
            $curl = curl_init();
            curl_setopt_array($curl, array(
            CURLOPT_URL => "https://dev.telehost.ng/api/post-ussd",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $toPost,
            CURLOPT_HTTPHEADER => array(
                "Accept: */*",
                "Authorization: 2tdgkdt46fz03y7k8fcl1bqd4ea2v9lcvfseioa0f8nlxx9xfqjpmyq56kj8v3qe92t02i5riywo4l4fnx0hcagplkgaclz42gqyrve4bskzctkny6q1v5i3lutko1jtr2ju4tiyq01k96dl4oyhol33dj2djkua0ys9iqubutyq57jnv0oc33itu9b3u9j97mnc3jcbe327u0ohd12i8p7vxpxr87svalc8t1sc48bg29c4gqkw0ybfk4",
                "Content-Type: application/json"
                ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);

            curl_close($curl);

            if ($err) {
            //  echo "cURL Error #:" . $err;
            } else {
            echo $response;
            }


        }else{

            $getBundle = DataProduct::where('data_bundle',$request->bundle_code)->first();
            if(!$getBundle){
                 return response()->json([
                     'status' => 'error',
                     'msg' => 'Bundle code given is not valid'
                 ]);
            }

            // return $getBundle;

            $ref = $this->generateKey(13);

            $ussd = new Ussd;
            $ussd->user_id = $user;
            $ussd->reference = $ref;
            $ussd->amount = $request->amount;
            $ussd->phone = $request->phone;
            $ussd->bundle_code = $request->bundle_code;
            $ussd->save();

            $data = [  
                "ref_code" => $ref,
                "ussd_code" => $getBundle->ussd_string.$request->phone.'*2323#',
                "access_code" => "j7pdkl"
            ];


          
            $toPost = json_encode($data);

            return $toPost;
        
        
            $curl = curl_init();
            curl_setopt_array($curl, array(
            CURLOPT_URL => "https://dev.telehost.ng/api/post-ussd",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $toPost,
            CURLOPT_HTTPHEADER => array(
                "Accept: */*",
                "Authorization: 2tdgkdt46fz03y7k8fcl1bqd4ea2v9lcvfseioa0f8nlxx9xfqjpmyq56kj8v3qe92t02i5riywo4l4fnx0hcagplkgaclz42gqyrve4bskzctkny6q1v5i3lutko1jtr2ju4tiyq01k96dl4oyhol33dj2djkua0ys9iqubutyq57jnv0oc33itu9b3u9j97mnc3jcbe327u0ohd12i8p7vxpxr87svalc8t1sc48bg29c4gqkw0ybfk4",
                "Content-Type: application/json"
                ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);

            curl_close($curl);

            if ($err) {
            //  echo "cURL Error #:" . $err;
            } else {
            echo $response;
            }


        }

      
      
        
    }

    public function recharge(Request $request)
    {
        
        $this->validateParameter('phone', $request->phone, STRING);
        $this->validateParameter('pin', $request->pin, STRING);

        $checkPin = GeneratedPin::where('pin_number',$request->pin)->where('status',0)->first();

        if(!$checkPin){
            return response()->json([
                'status' => 'error',
                'message' => 'Pin provided is not valid'
             ]);
        }

        GeneratedPin::where('pin_number',$request->pin)->update(['status' => 1]);
        $used = new UsedPin;
        $used->serial_number = $checkPin->serial_number;
        $used->pin_number = $checkPin->pin_number;
        $used->value = $checkPin->value;
        $used->phone = $request->phone;
        $used->time_used = Carbon::now();
        $used->save();

        $ref = $this->generateKey(13);

        $data = [  
            "ref_code" => $ref,
            "ussd_code" => "*456*1*2*".$checkPin->value.'*'.$request->phone.'*1551#',
            "access_code" => "j7pdkl"
        ];


      
        $toPost = json_encode($data);

        // return $toPost;
    
    
        $curl = curl_init();
        curl_setopt_array($curl, array(
        CURLOPT_URL => "https://dev.telehost.ng/api/post-ussd",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => $toPost,
        CURLOPT_HTTPHEADER => array(
            "Accept: */*",
            "Authorization: 2tdgkdt46fz03y7k8fcl1bqd4ea2v9lcvfseioa0f8nlxx9xfqjpmyq56kj8v3qe92t02i5riywo4l4fnx0hcagplkgaclz42gqyrve4bskzctkny6q1v5i3lutko1jtr2ju4tiyq01k96dl4oyhol33dj2djkua0ys9iqubutyq57jnv0oc33itu9b3u9j97mnc3jcbe327u0ohd12i8p7vxpxr87svalc8t1sc48bg29c4gqkw0ybfk4",
            "Content-Type: application/json"
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
        //  echo "cURL Error #:" . $err;
        } else {
        echo $response;
        }









        

    }


    public function patch()
    {
      
    }

    public function destroy()
    {
      
    }

}