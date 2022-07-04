<?php

namespace App\Http\Controllers\Api\User;

use App\Events\StatusUserEvent;
use App\Http\Controllers\Controller;
use http\Client\Curl\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StatusController extends Controller
{
    public function __construct()
    {
        $this->middleware("auth:userapi");
    }

    /**
     * @throws \Throwable
     */
    private function Status($state,string $message)
    {
        try {
            DB::beginTransaction();
            $user = auth()->user();
            $user->update([
                "status" => $state
            ]);
            DB::commit();
            broadcast(new StatusUserEvent($user));
            return response()->json(["Active" => $message]);
        }catch (\Exception $exception){
            DB::rollBack();
            return response()->json(["Error" => $exception->getMessage()]);
        }
    }

    public function Online(): \Illuminate\Http\JsonResponse
    {
        try {
            return $this->Status(1, "the user is online");
        } catch (\Throwable $e) {
            return response()->json(["Error"=>$e->getMessage()]);
        }
    }

    public function Offline(): \Illuminate\Http\JsonResponse
    {
        try {
            return $this->Status(0, "the user is offline");
        } catch (\Throwable $e) {
            return response()->json(["Error"=>$e->getMessage()]);
        }
    }

}
