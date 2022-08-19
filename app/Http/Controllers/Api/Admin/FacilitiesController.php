<?php


namespace App\Http\Controllers\Api\Admin;

use App\Class_Public\GeneralTrait;
use App\Http\Controllers\Controller;
use App\Models\bookings;
use App\Models\facilities;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class FacilitiesController extends Controller
{
    use GeneralTrait;

    public function __construct()
    {
        $this->middleware(["auth:userapi","multi.auth:2"]);
    }

    public function AllData(): \Illuminate\Http\JsonResponse
    {
        try {
            $data = new class{};
            $data->avgcost = $this->AvgCostInDayFacilities()->original;
            $data->avgrateNeg = $this->AvgRatingNegative()->original;
            $data->countfacall = $this->CountFacilities()->original;
            $data->countbookings = $this->CountBookingsAll()->original;
            $data->costbookings = $this->CostBookingsInSystem()->original;
            $data->countcancelbooking = $this->CountCancelBookingInSystem()->original;
            $data->countbookings5lastmonth = $this->CountBookings5LastMonth()->original;
            $data->costbookings5lastmonth = $this->CostBookingIn5LastMonth()->original;
            return \response()->json(["data"=>$data]);
        } catch (\Exception $exception){
            return \response()->json([
                "Error" => $exception->getMessage()
            ],401);
        }
    }
    //
    public function CountFacilities(): \Illuminate\Http\JsonResponse
    {
        try {
            return \response()->json(facilities::all()->count());
        } catch (\Exception $exception){
            return \response()->json([
                "Error" => $exception->getMessage()
            ],401);
        }
    }
    //
    public function AvgCostInDayFacilities(): \Illuminate\Http\JsonResponse
    {
        try {
            return \response()->json(facilities::all()->avg("cost"));
        } catch (\Exception $exception){
            return \response()->json([
                "Error" => $exception->getMessage()
            ],401);
        }
    }
    //
    public function AvgRatingNegative(): \Illuminate\Http\JsonResponse
    {
        try {
            return \response()->json((facilities::all()->whereIn("rate",[1,2])->count()/facilities::all()->count())*100);
        } catch (\Exception $exception){
            return \response()->json([
                "Error" => $exception->getMessage()
            ],401);
        }
    }
    //
    public function CountBookingsAll(): \Illuminate\Http\JsonResponse
    {
        try {
            return \response()->json(bookings::all()->count());
        } catch (\Exception $exception){
            return \response()->json([
                "Error" => $exception->getMessage()
            ],401);
        }
    }

    public function CountBookings5LastMonth(): \Illuminate\Http\JsonResponse
    {
        try {
//            DB::table()->whereYear()->pluck()->keys()
            $data = bookings::select(DB::raw("count(*) as count"),DB::raw("month(created_at) as month"))
                ->whereYear("created_at",Carbon::now()->year)
                ->groupBy(DB::raw("month"))
                ->orderBy("month","desc")
                ->take(5)
                ->pluck("count","month");
            foreach ($data->keys() as $key){
                $temp = date("F",mktime(0,0,0,$key,1));
                $data[$temp] = $data[$key];
                unset($data[$key]);
            }
            return \response()->json($data);
        } catch (\Exception $exception){
            return \response()->json([
                "Error" => $exception->getMessage()
            ],401);
        }
    }

    public function CostBookingIn5LastMonth(): \Illuminate\Http\JsonResponse
    {
        try {
            $data = bookings::select(DB::raw("sum(cost) as sum"),DB::raw("month(created_at) as month"))
                ->whereYear("created_at",Carbon::now()->year)
                ->groupBy(DB::raw("month"))
                ->orderBy("month","desc")
                ->take(5)
                ->pluck("sum","month");
            foreach ($data->keys() as $key){
                $temp = date("F",mktime(0,0,0,$key,1));
                $data[$temp] = $data[$key];
                unset($data[$key]);
            }
            return \response()->json($data);
        } catch (\Exception $exception){
            return \response()->json([
                "Error" => $exception->getMessage()
            ],401);
        }
    }

    public function CostBookingsInSystem(): \Illuminate\Http\JsonResponse
    {
        try {
            return \response()->json(bookings::all()->sum("cost"));
        } catch (\Exception $exception){
            return \response()->json([
                "Error" => $exception->getMessage()
            ],401);
        }
    }

    public function CountCancelBookingInSystem(): \Illuminate\Http\JsonResponse
    {
        try {
            return \response()->json($this->GetJsonFile($this->path_file())["countCancel"]);
        } catch (\Exception $exception){
            return \response()->json([
                "Error" => $exception->getMessage()
            ],401);
        }
    }

    public function ShowFacilitiesOwner(Request $request){
        try{
            $validate = Validator::make($request->all(), [
                "id_owner" => ["required", Rule::exists("users", "id"), "numeric"],
            ]);
            if ($validate->fails()) {
                return \response()->json([
                    "Error" => $validate->errors()
                ], 401);
            }
            $owner = User::where("id",$request->id_owner)->first();
            if($owner->rule==="1"){
                $facilities = $owner->user_facilities()->with("photos")->get();
                if(!is_null($facilities))
                {
                    return response([
                        'facilities'=>$facilities
                    ]);
                }else{
                    Throw new \Exception("the facility is not Exists");
                }
            }else{
                Throw new \Exception("the user is not Owner");
            }
        }catch (\Exception $exception){
            return \response()->json([
                "Error" => $exception->getMessage(),
                "message" => 'no id'
            ],401);
        }
    }

    /**
     * @throws \Throwable
     */
    public function DeleteFacility(Request $request){
        $deleteFac = new \App\Http\Controllers\Api\facilities\FacilitiesController();
        return $deleteFac->DeleteFacility($request);
    }
}
