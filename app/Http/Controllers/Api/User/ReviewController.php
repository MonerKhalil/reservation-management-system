<?php

namespace App\Http\Controllers\Api\User;

use App\Class_Public\Paginate;
use App\Http\Controllers\Controller;
use App\Models\facilities;
use App\Models\Profile;
use App\Models\review;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ReviewController extends Controller
{
    use Paginate;
    public function __construct()
    {
        $this->middleware(["auth:userapi","multi.auth:0"]);
    }

    /**
     * @throws \Throwable
     */

    public function ShowReviewAll(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $validate = Validator::make($request->all(),[
                "id_facility"=>["required","numeric",Rule::exists("facilities","id")],
            ]);
            if($validate->fails()){
                return \response()->json([
                    "Error" => $validate->errors()
                ],401);
            }
            $reviews = review::where("reviews.id_facility",$request->id_facility)
                ->orderBy("reviews.id")
                ->paginate($this->NumberOfValues($request));
            $reviews = $this->Paginate("reviews",$reviews);

            foreach ($reviews["reviews"] as $item){
                $item->user = [
                    "name"=>User::where("users.id",$item->id_user)->first()->name,
                    "path_photo"=>Profile::where("id_user",$item->id_user)->first()->path_photo
                    ];
            }
            return \response()->json($reviews);
        }catch (\Exception $exception){
            return \response()->json([
                "Error" => $exception->getMessage()
            ],401);
        }
    }

    public function CreateReviewRating(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            DB::beginTransaction();
            $user = auth()->user();
            $validate = Validator::make($request->all(),[
                "id_facility"=>["required","numeric",Rule::exists("facilities","id")],
                "rate" => ["required","numeric","min:1","max:5"]
            ]);
            if($validate->fails()){
                return \response()->json([
                    "Error" => $validate->errors()
                ],401);
            }
            if($this->CheckCanReview($user,$request->id_facility)===true){
                $review = review::updateOrCreate([
                    "id_facility"=>$request->id_facility,
                    "id_user"=>$user->id
                ],[
                    "id_facility"=>$request->id_facility,
                    "id_user"=>$user->id,
                    "rate"=>$request->rate
                ]);
                $this->UpdateRateFacility($request->id_facility);
                DB::commit();
                return \response()->json([
                    "review" => $review
                ]);
            }
            else{
                Throw new \Exception("It is not possible to make an evaluation due to not making a reservation in advance");
            }
        }catch (\Exception $exception){
            DB::rollBack();
            return \response()->json([
                "Error" => $exception->getMessage()
            ],401);
        }
    }

    public function CreateReviewComment(Request $request): \Illuminate\Http\JsonResponse{
        try {
            DB::beginTransaction();
            $user = auth()->user();
            $validate = Validator::make($request->all(),[
                "id_facility"=>["required","numeric",Rule::exists("facilities","id")],
                "comment" => ["required","string"]
            ]);
            if($validate->fails()){
                return \response()->json([
                    "Error" => $validate->errors()
                ],401);
            }
            $review = auth()->user()->reviews()->where("id_facility",$request->id_facility)->first();
            if(!is_null($review)){
                $review = review::updateOrCreate([
                    "id_facility"=>$request->id_facility,
                    "id_user"=>$user->id
                ],[
                    "comment"=>$request->comment
                ]);
                $this->UpdateRateFacility($request->id_facility);
                DB::commit();
                return \response()->json([
                    "review" => $review
                ]);
            }
            else{
                Throw new \Exception("You Dont make Rating");
            }
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
