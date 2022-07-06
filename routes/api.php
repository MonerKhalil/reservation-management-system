<?php
use Illuminate\Http\Request;



//
Route::get("test",function (Request $request){
////    $request->start_date,$request->end_date
////    $start_date = new DateTime($request->start_date);
////    $end_date = new DateTime($request->end_date);
////    return round(abs(strtotime($request->end_date) - strtotime($request->start_date))/86400);
//
//    try {
//        Throw new Exception("ksdmsdkmsdkmdskkdsm");
//    }catch (Exception $exception){
//        return \response()->json([
//            "Error" => $exception->getMessage()
//        ], 401);
//    }
//
////    return response()->json([ $end_date->diff($start_date)->days ]);
});

############### Start Users ######################

require __DIR__ . "\\Route.Private.Project\\"."User\\"."authuser.php";

require __DIR__ . "\\Route.Private.Project\\"."User\\"."user.php";

require __DIR__ . "\\Route.Private.Project\\"."User\\"."status.php";

require __DIR__ . "\\Route.Private.Project\\"."Admin\\"."admin.php";

############### End Users ######################


############### Start Facilities ######################

require __DIR__ . "\\Route.Private.Project\\"."Facilities\\"."search_facilities.php";

require __DIR__ . "\\Route.Private.Project\\"."Facilities\\"."facilities.php";

require __DIR__ . "\\Route.Private.Project\\"."Facilities\\"."favorite.php";

require __DIR__ . "\\Route.Private.Project\\"."Facilities\\"."booking.php";

############### End Facilities ######################


require __DIR__ . "\\Route.Private.Project\\"."Chat\\"."chats.php";


