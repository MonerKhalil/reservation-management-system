<?php

namespace App\Http\Controllers\Api\facilities;

use App\Class_Public\GeneralTrait;
use App\Http\Controllers\Controller;

use App\Models\facilities;
use App\Models\photos_fac;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use PHPUnit\Util\Exception;

class FacilitiesController extends Controller{
    use GeneralTrait;

    public function __construct()
    {
        $this->middleware(["auth:userapi","multi.auth:1"])->only(["ShowFacilities","AddFacility"
        ,"UpdateFacility","AddListImage","DeleteAllImage","DeleteOneImage"]);
        $this->middleware(["auth:userapi","multi.auth:1|2"])->only("DeleteFacility");
    }

    public function ShowFacilities(){
        try{
            $facilities= auth()->user()->user_facilities()->with('photos')->get();
            return response([
                'facilities'=>$facilities
            ]);
        }catch (\Exception $exception){
            return \response()->json([
                "Error" => $exception->getMessage()
            ],401);
        }
    }

    public function ShowAnyFacility(Request $request){
        try{
            $validate = Validator::make($request->all(), [
                "id_facility" => ["required", Rule::exists("facilities", "id"), "numeric"],
            ]);
            if ($validate->fails()) {
                return \response()->json([
                    "Error" => $validate->errors()
                ], 401);
            }
            $facility = facilities::with("photos")->where(["id"=>$request->id_facility])->first();
            if(!is_null($facility))
            {
                return response([
                    'facility'=>$facility
                ]);
            }else{
                Throw new \Exception("the facility is not Exists");
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
    public function DeleteFacility(Request $request)
    {
        DB::beginTransaction();
        try{
            $validator=Validator::make($request->all(),[
                "id"=>["required",Rule::exists("facilities","id")],
            ]);
            if($validator->fails()){
                return response()->json([
                    "error"=>$validator->errors()
                ]);
            }
            $user = auth()->user();
            if($user->rule==="2"){
                $facility = facilities::where(["id"=>$request->id])->first();
            }
            else{
                $facility = $user->user_facilities()->where("id",$request->id)->first();
            }
            if($facility!=null)
            {
                $this->RefundToUser($facility);
                $id_photo = $facility->photos;
                $facility->delete();
                foreach ($id_photo as $path)
                {
                    unlink($path->path_photo);
                }
                DB::commit();
                return response(['message'=>'facility deleted successfully']);
            }else{
                Throw new Exception("facility not Exists -_-");
            }
        }catch  (\Exception $exception){
            DB::rollback();
            return \response()->json([
                "Error" => $exception->getMessage()
            ],401);
        }
    }

    /**
     * @throws \Throwable
     */
    public function AddFacility(Request $request): \Illuminate\Http\JsonResponse
    {
        DB::beginTransaction();
        try {
            $validator=Validator::make($request->all(),[
                "name"=>["required","string"],
                "location"=>["required","string"],
                "description"=>["required","string"],
                "photo_list"=>["required","mimes:jpeg,png,jpg","array"],
                "type"=>["required",Rule::in(["hostel","chalet","farmer"])],
                "cost"=>["required","numeric"],
                "num_guest"=>["required","numeric"],
                "num_room"=>["required","numeric"],
                "wifi"=>["nullable","boolean"],
                "coffee_machine"=>["nullable","boolean"],
                "air_condition"=>["nullable","boolean"],
                "tv"=>["nullable","boolean"],
                "fridge"=>["nullable","boolean"],
            ]);
            if($validator->fails())
            {
                return response()->json([
                    "Error"=>$validator->errors()
                ] );
            }
            if(!$request->hasFile('photo_list')) {
                return response()->json(['upload_file_not_found'], 400);
            }
            $facility = \auth()->user()->user_facilities()->create([
            "name"=>$request->name,
            "location"=>$request->location,
            "description"=>$request->description,
            "type"=>$request->type,
            "cost"=> $request->cost,
            "num_guest"=>$request->num_guest,
            "num_room"=>$request->num_room,
            "air_condition" => $request->air_condition ?? false,
            "coffee_machine" => $request->coffee_machin ?? false,
            "tv" => $request->tv ?? false,
            "wifi" => $request->wifi ?? false,
            "fridge"=> $request->fridge ?? false
            ]);
            $photoList=$request->file('photo_list');
            foreach ($photoList as $photo){
                $newPhoto = time().$photo->getClientOriginalName();
                $facility->photos()->create([
                    "path_photo"=>'uploads/facility/'.$newPhoto,
                ]);
                $photo->move('uploads/facility',$newPhoto);
            }
            DB::commit();
            return response()->json([
                "facility" => $facility
            ],201);
        }catch (\Exception $exception){
            DB::rollBack();
            return \response()->json([
                "Error" => $exception->getMessage()
            ],401);
        }
    }

    /**
     * @throws \Throwable
     */
    public function UpdateFacility(Request $request): \Illuminate\Http\JsonResponse
    {
        DB::beginTransaction();
        try{
            $validator=Validator::make($request->all(),[
                "id"=>["required",Rule::exists("facilities","id")],
                "name"=>["nullable","string"],
                "location"=>["nullable","string"],
                "description"=>["nullable","string"],
                "type"=>["nullable",Rule::in(["hostel","chalet","farmer"])],
                "cost"=>["nullable","numeric"],
                "num_guest"=>["nullable","numeric"],
                "num_room"=>["nullable","numeric"],
                "wifi"=>["nullable","boolean"],
                "coffee_machine"=>["nullable","boolean"],
                "air_condition"=>["nullable","boolean"],
                "tv"=>["nullable","boolean"],
                "fridge"=>["nullable","boolean"],
                "available"=>["nullable","boolean"]
            ]);
            if($validator->fails()){
                return response()->json([
                    "error"=>$validator->errors()
                ]);
            }
            $facility = \auth()->user()->user_facilities()->where("id",$request->id)->first();
            $temp = clone $facility;
            $facility->update([
                "name"=>$request->name ?? $temp->name,
                "location"=>$request->location ?? $temp->location,
                "description"=>$request->description ?? $temp->description,
                "type"=>$request->type ?? $temp->type,
                "cost"=>$request->cost ?? $temp->cost,
                "num_guest"=>$request->num_guest ?? $temp->num_guest,
                "num_room"=>$request->num_room??$temp->num_room,
                "air_condition"=>$request->air_condition??$temp->air_condition,
                "coffee_machine"=>$request->coffee_machine??$temp->coffee_machine,
                "tv"=>$request->tv??$temp->tv,
                "wifi"=>$request->wifi??$temp->wifi,
                "fridge"=>$request->fridge??$temp->fridge,
                "available"==$request->available??$temp->available
            ]);
            DB::commit();
            return response()->json([
                "facility"=>$facility
            ]);
        }catch (\Exception $exception){
            DB::rollBack();
            return \response()->json([
                "Error" => $exception->getMessage()
            ],401);
        }
    }

    /**
     * @throws \Throwable
     */
    public function AddListImage(Request $request ): \Illuminate\Http\JsonResponse
    {
        $validator=Validator::make($request->all(), [
            "id" => "required",
            "photo_list" => ["required","mimes:jpeg,png,jpg","array"],
        ]);
        if($validator->fails()){
            return response()->json([
                "error"=>$validator->errors()
            ]);
        }
        if(!$request->hasFile('photo_list')) {
            return response()->json(['upload_file_not_found'], 401);
        }
        DB::beginTransaction();
        try{
            $facility = \auth()->user()->user_facilities()->where("id",$request->id)->first();
            if($facility!=null){
                $photoList = $request->file('photo_list');
                foreach ($photoList as $photo) {
                    $newPhoto = time() . $photo->getClientOriginalName();
                    $facility->photos()->create([
                        "path_photo" => 'uploads/facility/' . $newPhoto,
                    ]);
                    $photo->move('uploads/facility', $newPhoto);
                }
                DB::commit();
                return response()->json([
                    "message" => "image list add successfully"
                ]);
            }
            else{
                Throw new \Exception("Photo not Found -_-");
            }
        }catch (\Exception $exception){
            DB::rollBack();
            return response()->json([
                "status" => false,
                "message" => "created fails"
            ]);

        }
    }

    /**
     * @throws \Throwable
     */
    public function DeleteAllImage(Request $request){
        DB::beginTransaction();
        try{
            $validator=Validator::make($request->all(),[
                "id_facility"=>["required",Rule::exists("facilities","id")]
            ]);
            if($validator->fails()){
                return response()->json([
                    "error"=>$validator->errors()
                ]);
            }
            $user = auth()->user();
            $facility = $user->user_facilities()->where("id",$request->id)->first();
            if (!is_null($facility)){
                $id_photo = $facility->photos;
                $facility->delete();
                foreach ($id_photo as $path)
                {
                    unlink($path->path_photo);
                }
                DB::commit();
                return response(['message'=>'Photos deleted successfully']);
            }else{
                Throw new \Exception("Facility not Found -_-");
            }
        }catch  (\Exception $exception){
            DB::rollback();
            return \response()->json([
                "Error" => $exception->getMessage()
            ],401);
        }
    }

    /**
     * @throws \Throwable
     */
    public function DeleteOneImage(Request $request){
        DB::beginTransaction();
        try{
            $validator=Validator::make($request->all(),[
                "id_photo"=>["required",Rule::exists("photos_facility","id")],
                "id_facility"=>["required",Rule::exists("facilities","id")]
            ]);
            if($validator->fails()){
                return response()->json([
                    "error"=>$validator->errors()
                ]);
            }
            $_photo = photos_fac::where("id_facility",$request->id_facility)->where("id",$request->id_photo)->first();
            if($_photo!=null)
            {
                $temp = clone $_photo;
                $_photo->delete();
                unlink($temp->path_photo);
                DB::commit();
                return response(['message'=>'Photo deleted successfully']);
            }else{
                Throw new \Exception("Photo not Found -_-");
            }
        }catch  (\Exception $exception){
            DB::rollback();
            return \response()->json([
                "Error" => $exception->getMessage()
            ],401);
        }
    }
}
