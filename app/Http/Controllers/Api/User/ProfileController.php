<?php

namespace App\Http\Controllers\Api\User;

use App\Class_Public\Paginate;
use App\Http\Controllers\Api\Admin\AdminController;
use App\Http\Controllers\Controller;
use App\Models\facilities;
use App\Models\favorites;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use phpDocumentor\Reflection\Types\ClassString;


class ProfileController extends Controller
{
    use Paginate;
    public function __construct()
    {
        $this->middleware(["auth:userapi"]);
    }

    public function ShowProfileAllData(): \Illuminate\Http\JsonResponse
    {
        try {
            $user = auth()->user();
            $temp = clone $user;
            $profile = $temp->profile;
            return  \response()->json([
                "user" => $user,
                "profile"=>$profile
            ]);
        } catch (\Exception $exception){
            return \response()->json([
                "Error" => $exception->getMessage()
            ],401);
        }
    }


    public function UserProfileOther(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $validate = Validator::make($request->all(),[
                "id" => ["required",Rule::exists("users","id")]
            ]);
            if($validate->fails())
            {
                return \response()->json([
                    "Error" => $validate->errors()
                ],401);
            }
            $user = User::with("profile")
                ->where("id","=",$request->id)
                ->first();
            if($user->rule==="2"){
                throw new \Exception("the user is admin");
            }
            return \response()->json([
                "user" => $user
            ]);
        } catch (\Exception $exception){
            return \response()->json([
                "Error" => $exception->getMessage()
            ],401);
        }
    }

    /**
     * @throws \Throwable
     */
    public function UpdateData(Request $request): \Illuminate\Http\JsonResponse
    {
        DB::beginTransaction();
        try {
            $DATAUser = $this->ShowProfileAllData();
            $profile = $DATAUser->getData()->profile;
            $user = $DATAUser->getData()->user;
            $newPhoto = null;
            $validate = Validator::make($request->all(),[
                "name" => ["nullable","string"],
                "email" => ["nullable",Rule::unique("users","email")->ignore($user->id)],
                "password" => ["nullable","min:8"],
                "gender" => ["nullable",Rule::in(["female","male"])],
                "path_photo" => ["nullable",'mimes:jpeg,png,jpg'],
                "age" => ["nullable","date"],
                "phone" => ["nullable","min:10","regex:/^[0-9]+$/"]
            ]);
            if($validate->fails())
            {
                return \response()->json([
                    "Error" => $validate->errors()
                ],401);
            }
            $photo = $request->file("path_photo") ?? null;
            if($photo !==null){
                if($photo->isValid()){
                    $newPhoto = time().$photo->getClientOriginalName();
                    $newPhoto = 'uploads/Users/'.$newPhoto;
                }
            }
            auth()->user()->update([
                "name" => $request->name ?? $user->name,
                "email" => $request->email ?? $user->email,
                "password" => password_hash($request->password,PASSWORD_DEFAULT) ?? $user->password,
            ]);
            if($profile!==null){
                if($newPhoto!==null&&$profile->path_photo!==null){
                    unlink($profile->path_photo);
                }
                auth()->user()->profile()
                    ->update([
                        "path_photo"=>  $newPhoto ?? $profile->path_photo,
                        "gender" => $request->gender ?? $profile->gender,
                        "age" => $request->age ?? $profile->age,
                        "phone" => $request->phone ?? $profile->phone
                    ]);
            }else{
                auth()->user()->profile()
                    ->create([
                        "path_photo"=>  $newPhoto,
                        "gender" => $request->gender,
                        "age" => $request->age,
                        "phone" => $request->phone
                    ]);
            }
            if($newPhoto!==null){
                $photo->move('uploads/Users',$newPhoto);
            }
            DB::commit();
            return \response()->json([
                "Message" => "Successfully Update Profile"
            ]);
        }catch (\Exception $exception){
            DB::rollBack();
            return \response()->json([
                "Error" => $exception->getMessage()
            ],401);
        }
//$DATAUser->getData()->user->id
    }

    /**
     * @throws \Throwable
     */
    public function DeleteUserAndProfile(): \Illuminate\Http\JsonResponse
    {
        $path = null;
        DB::beginTransaction();
        try {
            $user = User::all()->where("id",auth()->id())->first();
            if($user!==null){
                if( $user->profile!==null ){
                    $path = $user->profile->path_photo??null;
                }
            }
            $user->tokens()->delete();
            $user->delete();
            if($path!==null){
                unlink($path);
            }
            DB::commit();
            return \response()->json([
                "Message" => "Successfully Deleted User"
            ]);
        }catch (\Exception $exception){
            DB::rollBack();
            return \response()->json([
                "Error" => $exception->getMessage()
            ],401);
        }
    }
}
