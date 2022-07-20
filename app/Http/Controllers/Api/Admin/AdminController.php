<?php

namespace App\Http\Controllers\Api\Admin;

use App\Class_Public\Paginate;
use App\Http\Controllers\Api\User\AuthController;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    use Paginate;

    public function __construct()
    {
        $this->middleware(["auth:userapi","multi.auth:2"]);
    }

    public function DeleteUser(Request $request): \Illuminate\Http\JsonResponse
    {
        $path = null;
        DB::beginTransaction();
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
            $user = User::all()->where("id",$request->id)->first();
            if($user->rule==="2"){
                throw new \Exception("the user is admin");
            }
            if($user!==null){
                if( $user->profile!==null){
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

    //update to 1 or 0 rule
    public function ShowUsersAll(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $validate = Validator::make($request->all(),[
                "num_values" => ["nullable","numirce"]
            ]);
            $users = User::with("profile")
                ->where("rule","!=","2")
                ->paginate($this->NumberOfValues($request));
            return \response()->json([
                $this->Paginate("users",$users)
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
    public function UpdateUser(Request $request): \Illuminate\Http\JsonResponse
    {
        DB::beginTransaction();
        try {
            $validate = Validator::make(["id" => $request->id],[
                "id" => ["required",Rule::exists("users","id")]
            ]);
            if($validate->fails())
            {
                return \response()->json([
                    "Error" => $validate->errors()
                ],401);
            }
            $user = User::all()->where("id",$request->id)->first();
            if($user->rule==="2"){
                throw new \Exception("the user is admin");
            }
            $validate = Validator::make($request->all(),[
                "name" => ["nullable","string"],
                "email" => ["nullable",Rule::unique("users","email")->ignore($user->id)],
                "password" => ["nullable","min:8"],
                "gender" => ["nullable",Rule::in(["female","male"])],
                "path_photo" => ["nullable",'mimes:jpeg,png,jpg'],
                "age" => ["nullable","date"],
                "phone" => ["nullable","min:10","numeric"]
            ]);
            if($validate->fails())
            {
                return \response()->json([
                    "Error" => $validate->errors()
                ],401);
            }
            $profile = $user->profile;
            $newPhoto = null;
            $photo = $request->file("path_photo") ?? null;
            if($photo !==null){
                if($photo->isValid()){
                    $newPhoto = time().$photo->getClientOriginalName();
                    $newPhoto = 'uploads/Users/'.$newPhoto;
                }
            }
            $user->update([
                "name" => $request->name ?? $user->name,
                "email" => $request->email ?? $user->email,
                "password" => password_hash($request->password,PASSWORD_DEFAULT) ?? $user->password,
            ]);
            if($profile!==null){
                if($newPhoto!==null&&$profile->path_photo!==null){
                    unlink($profile->path_photo);
                }
                $user->profile()
                    ->update([
                        "path_photo"=>  $newPhoto ?? $profile->path_photo,
                        "gender" => $request->gender ?? $profile->gender,
                        "age" => $request->age ?? $profile->age,
                        "phone" => $request->phone ?? $profile->phone
                    ]);
            }else{
                $user->profile()
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
    }

    public function AddUser(Request $request): \Illuminate\Http\JsonResponse
    {
        $test = new AuthController();
        return $test->register($request);
    }

}
