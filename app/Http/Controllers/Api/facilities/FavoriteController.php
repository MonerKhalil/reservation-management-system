<?php

namespace App\Http\Controllers\Api\facilities;

use App\Http\Controllers\Controller;
use App\Http\Resources\FacilityResource;
use App\Models\facilities;
use App\Models\favorites;
use App\Models\photos_fac;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class FavoriteController extends Controller
{
    public function __construct()
    {
        $this->middleware(["auth:userapi","multi.auth:0"]);
    }
    public function ShowFavorite()
    {
        $favorite=\auth()->user()->favorite_facilities()->with("photos")->get();
        return response([
            "facilities"=> $favorite
        ]);
    }
    public function AddOrRemoveFavorite(Request $request): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->all(),[
            'id_facility'=>['required',Rule::exists("facilities","id")],
        ]);
        if ($validator->fails()) {
            return response()->json([$validator->errors()]);
        }
        $favorite = favorites::where([
            'id_user'=>\auth()->user()->id,
            'id_facility'=> $request->id_facility
        ])->first();
        if(!is_null($favorite)){
            $favorite->delete();
            return response()->json([
                "message"=>"delete favorite"
            ]);
        }
        else
        {
            favorites::create([
                'id_user'=>\auth()->user()->id,
                'id_facility'=>$request->id_facility
            ]);
            return response()->json([
                "message"=>"add to favorite"
            ]);
        }
    }
}
