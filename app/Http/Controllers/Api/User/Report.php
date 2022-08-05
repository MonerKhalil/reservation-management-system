<?php

namespace App\Http\Controllers;

use App\Http\Resources\ReportResource;
use App\Models\reports;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class Report extends Controller
{
    public function __construct()
    {
       $this->middleware(["auth:userapi","multi.auth:2"])->except(["store","update"]);
       $this->middleware(["auth:userapi","multi.auth:0"])->only('store');
    }
    public function store(Request $request ): \Illuminate\Http\JsonResponse
    {
      $validator=Validator::make($request->all(),[
          "id_facility"=>['required',Rule::exists('facilities','id')],
          "report"=>['required','string'],
      ]);

      if($validator->fails()){
          return response()->json([
              'state'=>false,
              'message'=>'error validate'
              ,'Error'=>$validator->errors()
          ]);
      }
      DB::beginTransaction();
      try {
          $report = reports::create([
              "id_facility" => $request->id_facility,
              "report" => $request->report,
              "id_user"=>\Auth::id()
          ]);
          DB::commit();
          //no
          return response()->json([
              "state" => true,
              "message" => "report  has been created",
              "data" => $report
          ]);
      }catch (\Exception $exception){
          DB::rollBack();
          return response()->json([
              "state" => false,
              "message" => "report  has been not created",
              "Error" => $exception->getMessage()
          ],401);
      }
  }
    public function update(Request $request ): \Illuminate\Http\JsonResponse
    {
        $validator=Validator::make($request->all(),[
            "id_facility"=>['required',Rule::exists('facilities','id')],
            "report"=>['required','string'],
        ]);

        if($validator->fails()){
            return response()->json([
                'state'=>false,
                'message'=>'error validate'
                ,'Error'=>$validator->errors()
            ]);
        }
        DB::beginTransaction();
        try {
            $report = \Auth::user()->reports_facilities()->where("id",$request->id_facility)->update([
                "report" => $request->report
            ]);
            DB::commit();
            return response()->json([
                "state" => true,
                "message" => "report  has been updated",
                "data" => $report
            ]);
        }catch (\Exception $exception){
            DB::rollBack();
            return response()->json([
                "state" => false,
                "message" => "report  has been not updated",
                "Error" => $exception->getMessage()
            ],401);
        }

    }
    public function  destroy($id): \Illuminate\Http\JsonResponse{
        DB::beginTransaction();
        try {
            $report = reports::where("id", $id)->first();

             if (is_null($report)){
                 return response()->json([
                       'state'=>false,
                       ' message'=>'report not found ',
                   ]);
             }else{
                 $report->delete();
            DB::commit();
                 return response()->json([
                     "state" => true,
                     "message" => "report  has been deleted",
                 ]);
             }
        }catch (\Exception $exception){
            DB::rollBack();
            return response()->json([
                "state" => false,
                "message" => "report  has been not deleted",
                "Error" => $exception->getMessage()
            ],401);
        }
    }
    public  function  index(): \Illuminate\Http\JsonResponse
    {
        $report=reports::all();

        if ( is_null($report) ) {
            return response()->json([
                'message'=> ' no report founded']);
        }
        return  response()->json([
            'state'=>true,
            'message'=> 'All report  exist',
            'date'=> ReportResource::collection($report),
        ]);
    }
}
