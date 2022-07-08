<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\facilities;
use App\Models\review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ReviewController extends Controller
{
    public function __construct()
    {
        $this->middleware("auth:userapi");
    }

    /**
     * @throws \Throwable
     */
    public function CreateReviewRating(Request $request){
        try {
            DB::beginTransaction();
            $validate = Validator::make($request->all(),[
                "id_facility"=>["required","numeric",Rule::exists("facilities","id")],
                "rate" => ["required","numeric","min:1","max:5"]
            ]);
            if($validate->fails()){
                return \response()->json([
                    "Error" => $validate->errors()
                ],401);
            }

            $this->UpdateRateFacility($request->id_facility);
        }catch (\Exception $exception){
            DB::rollBack();
            return \response()->json([
                "Error" => $exception->getMessage()
            ],401);
        }
    }

    private function UpdateRateFacility($id_fac){
        $facility = facilities::where("id",$id_fac)->first();
        $avg = review::where("id_facility",$facility->id)->avg("rate");
        $facility->update([
            "rate" => $avg
        ]);
    }

    private function CheckCanReview($user,$id_fac):bool{
        $temp = $user->bookings()->where("id_facility",$id_fac)->first();
        return !is_null($temp);
    }
}
