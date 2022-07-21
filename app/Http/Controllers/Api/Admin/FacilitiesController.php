<?php


namespace App\Http\Controllers\Api\Admin;

use App\Class_Public\GeneralTrait;
use App\Http\Controllers\Controller;
use App\Models\bookings;
use App\Models\facilities;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FacilitiesController extends Controller
{
    use GeneralTrait;

    public function __construct()
    {
        $this->middleware(["auth:userapi","multi.auth:2"]);
    }

    public function AvgBookingsWithfacilitiesAll(): \Illuminate\Http\JsonResponse
    {
        try {
            return \response()->json([
                "avg"=>(bookings::all()->select("id_facility")/facilities::all()->count())*100
            ]);
        } catch (\Exception $exception){
            return \response()->json([
                "Error" => $exception->getMessage()
            ],401);
        }
    }

    public function AvgRatingNegative(): \Illuminate\Http\JsonResponse
    {
        try {
            return \response()->json([
                "avg"=>(facilities::all()->whereIn("rate",[1,2])->count()/facilities::all()->count())*100
            ]);
        } catch (\Exception $exception){
            return \response()->json([
                "Error" => $exception->getMessage()
            ],401);
        }
    }
    public function AvgCostNnDayFacilities(): \Illuminate\Http\JsonResponse
    {
        try {
            return \response()->json([
                "avg"=>facilities::all()->avg("cost")
            ]);
        } catch (\Exception $exception){
            return \response()->json([
                "Error" => $exception->getMessage()
            ],401);
        }
    }
    public function CountBookingsAll(): \Illuminate\Http\JsonResponse
    {
        try {
            return \response()->json([
                "count"=>bookings::all()->count()
            ]);
        } catch (\Exception $exception){
            return \response()->json([
                "Error" => $exception->getMessage()
            ],401);
        }
    }
    public function CountBookingsLastMonth(): \Illuminate\Http\JsonResponse
    {
        try {
            $Date = Carbon::now()->subMonths(1)->subDay(1);
            return \response()->json([
                "count"=>bookings::all()->where("created_at",">=",$Date)->count()
            ]);
        } catch (\Exception $exception){
            return \response()->json([
                "Error" => $exception->getMessage()
            ],401);
        }
    }
    public function CountFacilities(): \Illuminate\Http\JsonResponse
    {
        try {
            return \response()->json([
                "count"=>facilities::all()->count()
            ]);
        } catch (\Exception $exception){
            return \response()->json([
                "Error" => $exception->getMessage()
            ],401);
        }
    }


}
/*
 * Avg Rating Negative ( Rate in 1,2 )
Avg Cost in day facilities in System
Count Booking in System
Cost Booking in Last Month
Count facilities
Count Cancel Booking in System
Cost All Bookings in System
Show Facilites
*/
