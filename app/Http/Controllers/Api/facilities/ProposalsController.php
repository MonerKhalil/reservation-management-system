<?php

namespace App\Http\Controllers\Api\facilities;

use App\Class_Public\Paginate;
use App\Http\Controllers\Controller;
use App\Models\bookings;
use App\Models\facilities;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProposalsController extends Controller
{
    use Paginate;
    public function __construct()
    {
        $this->middleware(["auth:userapi","multi.auth:0"]);
    }

    public function Proposals(Request $request): \Illuminate\Http\JsonResponse
    {
        $FacilitiesAlike = facilities::whereIn("id",$this->GetIdsFacilitiesAlike())
             ->orderBy("rate","desc")
             ->paginate($this->NumberOfValues($request));
        $FinalAllData = $this->Paginate("facilities",$FacilitiesAlike);
        foreach ($FinalAllData["facilities"] as $item){
            $item->photos = DB::table("photos_facility")
                ->select(["photos_facility.path_photo"])
                ->where("photos_facility.id_facility",$item->id)
                ->get();
        }
        return response()->json($FinalAllData);
    }

    public function GetIdsFacilitiesAlike(){
        $user = auth()->user();
        $ids_facilities = [];
        $ids_facilities_temp = $user->bookings()
            ->select("id_facility")
            ->distinct()->get()->toArray();//id_facility : values
        foreach ($ids_facilities_temp as $item){
            $ids_facilities []= $item["id_facility"];
        }
        $ids_users = [];
        $ids_users_temp = bookings::select("id_user")
            ->whereIn("id_facility",$ids_facilities)->where("id_user","!=",$user->id)
            ->distinct()->get()->toArray();//id_facility : values
        foreach ($ids_users_temp as $item){
            $ids_users []= $item["id_user"];
        }

        return bookings::select("id_facility")
            ->whereIn("id_user",$ids_users)->whereNotIn("id_facility",$ids_facilities)
            ->distinct()->get()->toArray();//id_facility : values
    }
}
