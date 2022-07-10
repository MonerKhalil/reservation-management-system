<?php

namespace App\Http\Controllers\Api\User;

use App\Class_Public\DataInNotifiy;
use App\Class_Public\Paginate;
use App\Http\Controllers\Controller;
use App\Models\bookings;
use App\Models\facilities;
use App\Models\User;
use App\Notifications\UserNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Validator;

class BookingController extends Controller
{
    use Paginate;
    public function __construct()
    {
        $this->middleware(["auth:userapi", "multi.auth:0|2"]);
    }

    public function DatesNotAvailable(Request $request)
    {
        try {
            $validate = Validator::make($request->all(), [
                "num_values" => ["nullable","numeric"],
                "id_facility" => ["required", Rule::exists("facilities", "id"), "numeric"],
            ]);
            if ($validate->fails()) {
                return \response()->json([
                    "Error" => $validate->errors()
                ], 401);
            }
            $facility = facilities::all()
                ->where("id",$request->id_facility)->first()
                ->bookings()->select(["id","start_date","end_date"])
                ->paginate($this->NumberOfValues($request));
            return $this->Paginate("facility",$facility);
        }catch (\Exception $exception){
            return \response()->json([
                "Error" => $exception->getMessage()
            ], 401);
        }
    }

    public function CostBooking(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $validate = Validator::make($request->all(), [
                "id_facility" => ["required", Rule::exists("facilities", "id"), "numeric"],
                "start_date" => ["required","date"],
                "end_date" => ["required","date"]
            ]);
            if($validate->fails()){
                return response()->json(["Error"=>$validate->errors()]);
            }
            $start_date = $request->start_date  ?? null;
            $end_date =  $request->end_date ?? null;
            if($start_date!==null&&$end_date!==null){
                if(!$this->Check_Date($start_date,$end_date)){
                    return response()->json(["Error"=>"The Problem in Date"]);
                }
            }
            $facility = facilities::where("id",$request->id_facility)->first();
            $days = round(abs(strtotime($end_date) - strtotime($start_date))/86400)+1;
            return \response()->json([
                "cost" => $days * $facility->cost
            ]);
        }catch (\Exception $exception){
            return \response()->json([
                "Error" => $exception->getMessage()
            ], 401);
        }
    }
    /**
     * @throws \Throwable
     */

    public function UnBooking(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            DB::beginTransaction();
            $validate = Validator::make($request->all(), [
                "id_booking" =>["required", Rule::exists("bookings", "id"), "numeric"],
                "id_facility" => ["required", Rule::exists("bookings", "id_facility")
                    ,Rule::exists("facilities", "id"), "numeric"],
            ]);
            if($validate->fails()){
                return response()->json(["Error"=>$validate->errors()]);
            }
            $user = auth()->user();
            $booking = bookings::where("id",$request->id_booking)
                ->where("id_facility",$request->id_facility)->first();
            if($user->id!==$booking->id_user){
                throw new \Exception("Unauthenticated.");
            }
            $facility = facilities::where("id",$booking->id_facility)->first();
            $owner = User::where("id",$facility->id_user)->first();
            $time = round(abs(strtotime(Carbon::now()) - strtotime($booking->created_at))/86400);
            if($time > 1){
                return \response()->json(["Error"=>[
                    "facility" => "You Can't UnBooking because .....!!"
                ]]);
            }
            else{
                $owner->decrement("amount",$booking->cost);
                $user->increment("amount",$booking->cost);
                $booking->delete();
                DB::commit();
                $header = "unbooking facility ".$facility->name;
                $body = "This property has already been cancelled ".$user->name;
                $owner->notify(new UserNotification($header,"UnBooking",$body,Carbon::now()));
                $user->notify(new UserNotification($header,"UnBooking", "Success UnBooking The Facility :)",Carbon::now()));
                return \response()->json(["message"=>"Success UnBooking The Facility :)"]);
            }
        }catch (\Exception $exception){
            DB::rollBack();
            return \response()->json([
                "Error" => $exception->getMessage()
            ], 401);
        }
    }


    public function Booking(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            DB::beginTransaction();
            $validate = Validator::make($request->all(), [
                "id_facility" => ["required", Rule::exists("facilities", "id"), "numeric"],
                "start_date" => ["required","date"],
                "end_date" => ["required","date"]
            ]);
            if($validate->fails()){
                return response()->json(["Error"=>$validate->errors()]);
            }
            $start_date = $request->start_date  ?? null;
            $end_date =  $request->end_date ?? null;
            if($start_date!==null&&$end_date!==null){
                if(!$this->Check_Date($start_date,$end_date)){
                    return response()->json(["Error"=>"The Problem in Date :("]);
                }
            }
            $user = auth()->user();
            $facility = facilities::where("id",$request->id_facility)->first();
            $owner = User::where("id",$facility->id_user)->first();
            $price = $this->PriceTheFinal($facility,$start_date,$end_date);
            if($facility->available===0){
                return \response()->json(["Error"=>[
                    "facility" => "The Facility is Not Available Now :("
                ]]);
            }
            if($price != -1){
                if($user->amount>=$price){
                    $user->decrement("amount",$price);
                    $owner->increment("amount",$price);
                    $booking = bookings::create([
                        "id_user"=>$user->id,
                        "id_facility"=>$facility->id,
                        "cost"=>$price,
                        "start_date"=>$start_date,
                        "end_date"=>$end_date
                    ]);
                    DB::commit();
                    $header = "booking facility ".$facility->name;
                    $body = "The facility has been booked by the user ".$user->name;
                    $body_request = ["id_booking"=>$booking->id];
                    $Data = new DataInNotifiy("/api/bookings/info",$body_request,"GET");
                    $owner->notify(new UserNotification($header,"Booking",$body,$booking->created_at,$Data
                    ));
                    $user->notify(new UserNotification($header,"Booking",
                        "The property has been booked successfully",$booking->created_at, $Data));
                    return \response()->json(["booking"=>$booking]);
                }else{
                    DB::rollBack();
                    return \response()->json(["Error"=>[
                        "user" => "The User Amount Not ! -_-"
                    ]]);
                }
            }else{
                DB::rollBack();
                return \response()->json(["Error"=>[
                    "facility" => "The Facility is Not Available in Between This Date :("
                ]]);
            }
        }catch (\Exception $exception){
            DB::rollBack();
            return \response()->json([
                "Error" => $exception->getMessage()
            ], 401);
        }
    }

    public function GetInfoBooking(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $validate = Validator::make($request->all(), [
                "id_booking" =>["required", Rule::exists("bookings", "id"), "numeric"]]);
            if($validate->fails()){
                return response()->json(["Error"=>$validate->errors()]);
            }
            $bk=bookings::where("id",$request->id_booking)->first();
            return \response()->json([
                "booking" => $bk
                ]);
        }
        catch (\Exception $exception){
                return \response()->json([
                    "Error" => $exception->getMessage()
                ], 401);
            }
    }

    private function CheckBooking($facility,$start_date,$end_date):bool{
        $bookings_facility = DB::table("bookings")->where("id_facility",$facility->id);
        $count = $bookings_facility->count();
        $test1 = clone $bookings_facility;
        if($test1->get()->toArray()===[]){
            return true;
        }
        $test2 = clone $bookings_facility;
        $GetBookings = $test2
            ->whereNotBetween("bookings.start_date",[$start_date,$end_date])
            ->whereNotBetween("bookings.end_date",[$start_date,$end_date])
            ->WhereRaw("Not( ? between  bookings.start_date and bookings.end_date ) ",[$start_date])
            ->WhereRaw("Not( ? between  bookings.start_date and bookings.end_date ) ",[$end_date])
            ->whereRaw("( (? != bookings.start_date) and (? != bookings.end_date) )",[$start_date,$start_date])
            ->whereRaw("( (? != bookings.start_date) and (? != bookings.end_date) )",[$end_date,$end_date])
            ->get()->toArray();
        if ($GetBookings!==[]&&$count===count($GetBookings))
        {
            return true;
        }
        return false;
    }
    private function PriceTheFinal($facility,$start_date,$end_date):float{
        if($this->CheckBooking($facility,$start_date,$end_date)===true){
            $days = round(abs(strtotime($end_date) - strtotime($start_date))/86400)+1;
            return $days * $facility->cost;
        }
        return -1;
    }
}
