<?php

namespace App\Http\Controllers\Api\Owner;

use App\Class_Public\DataInNotifiy;
use App\Class_Public\GeneralTrait;
use App\Http\Controllers\Controller;
use App\Models\bookings;
use App\Models\facilities;
use App\Models\Profile;
use App\Models\ReportOwner;
use App\Models\User;
use App\Notifications\UserNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ReportController extends Controller
{
    use GeneralTrait;
    public function __construct()
    {
        $this->middleware(["auth:userapi","multi.auth:2"])->except(["AddReport","infoReport"]);
        $this->middleware(["auth:userapi","multi.auth:1"])->only(['AddReport']);
    }

    /**
     * @throws \Throwable
     */
    public function AddReport(Request $request): \Illuminate\Http\JsonResponse
    {
        $validator=Validator::make($request->all(),[
            "id_user"=>['required',Rule::exists('users','id')],
            "report"=>['required','string'],
        ]);
        if($validator->fails()){
            return response()->json([
                'Error'=>$validator->errors()
            ]);
        }
        $owner = auth()->user();
        $user = User::where("id",$request->id_user)->first();
        $admins = User::where("rule","2")->get();
        DB::beginTransaction();
        try {
            if($this->CheckUserBookingFacility($owner,$user)){
                $report = ReportOwner::create([
                    "id_user" => $user->id,
                    "id_owner" => $owner->id,
                    "report" => $request->report
                ]);
                if($this->CheckIS3Report($owner,$user)){
                    $path_photo = $user->profile->path_photo;
                    $user->delete();
                    if ($path_photo!==$this->NameImage_DefultPath()){
                        unlink($path_photo);
                    }
                }
                $header = "Report ِa User";
                $body = "The owner of the facility has reported about ".$user->name;
                $body_request = ["id_report"=>$report->id];
                $Data = new DataInNotifiy("owner/report/info",$body_request,"GET");
                Notification::send($admins,new UserNotification($header,"Report User",$body,Carbon::now(),$Data));
                $header = "Report about you";
                $body = "The owner of the facility has reported about you";
                $user->notify(new UserNotification($header,"Report User", $body,Carbon::now(),$Data));
                DB::commit();
                return response()->json([
                    "report" => $report
                ]);
            }
            else{
                Throw new \Exception("the user is not Booking facility Owner");
            }
        }catch (\Exception $exception){
            DB::rollBack();
            return response()->json([
                "Error" => $exception->getMessage()
            ],401);
        }
    }
    public function infoReport(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $validator=Validator::make($request->all(),[
                "id_report"=>['required',Rule::exists('reports','id')],
            ]);

            if($validator->fails()){
                return response()->json([
                    'Error'=>$validator->errors()
                ]);
            }
            return response()->json([
                "report" => ReportOwner::where("id",$request->id_report)->first()
            ]);
        }catch (\Exception $exception){
            return response()->json([
                "Error" => $exception->getMessage()
            ],401);
        }
    }

    private function CheckUserBookingFacility($owner,$user): bool
    {
        $id_fac = $owner->user_facilities()->select("facilities.id as id")->pluck("id");
        $booking = bookings::where("id_user",$user->id)->whereIn("id_facility",$id_fac)->first();
        if(is_null($booking)){
            return false;
        }
        return true;
    }

    /**
     * @throws \Throwable
     */
    private function CheckIS3Report($owner,$user): bool
    {
        $count = ReportOwner::where(["id_user",$user->id,"id_owner",$owner->id])
            ->select(["id_owner","id_user"])->distinct()->count(["id_owner","id_user"]);
        if ($count >= 3){
            return true;
        }
        return false;
    }

}
