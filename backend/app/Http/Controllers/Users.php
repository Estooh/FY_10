<?php

namespace App\Http\Controllers;

use App\Models\Repayment;
use App\Models\User;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;


class Users extends Controller
{

    public function insertuser()
    {
  return User::get();
    }


    public function userinsert(Request $request)
    {
        $user = new User();
        $user->name = $request->name;
        $user->email= $request->email;
        $user->password = Hash::make($request->password);

        try {
            if($user->save())
            {
                return $message =[
                    "status"=>"okay",
                    "message"=>"registered successfully",

                ];
            }
            else{
                return $message =[
                    "status"=>"notokay",
                    "message"=>" not registered something is wrong",

                ];
            }
        } catch (\Exception $e) {
            // Handle the exception (e.g., redirect back with an error message)
            return $message =[
                "status"=>"notokay",
                "message"=>$e,

            ];
        }

    }


    public function checkPayment(Request $request){
        $subject_id = $request->subject_id;
        $user_id = $request->user_id;
        $check = Repayment::where('user_id',$user_id)->where('type',$subject_id)->first();
        if(!empty($check)){
            return response()->json(['status'=>true,'message'=>'Payment has been made']);
        }else {
            return response()->json(['status'=>false,'message'=>'Payment has not been made']);
        }
    }

}
