<?php

use Illuminate\Support\Facades\Broadcast;

//Broadcast::routes(['prefix' => 'api','middleware' => ['auth:userapi']]);
Broadcast::routes(['prefix' => 'api','middleware' => ["api",'auth:userapi']]);



Route::get("test",[\App\Http\Controllers\Api\Owner\ReportController::class,"AddReport"]);


############### Start Admin ######################

require __DIR__ . "\\Route.Private.Project\\"."Admin\\"."admin.php";

############### End Admin ######################


############### Start Owner ######################

require __DIR__ . "\\Route.Private.Project\\"."Owner\\"."ownerbooking.php";

require __DIR__ . "\\Route.Private.Project\\"."Owner\\"."facilities.php";

require __DIR__ . "\\Route.Private.Project\\"."Owner\\"."report.php";

############### End Owner ######################



############### Start User ######################

require __DIR__ . "\\Route.Private.Project\\"."User\\"."authuser.php";

require __DIR__ . "\\Route.Private.Project\\"."User\\"."profile.php";

require __DIR__ . "\\Route.Private.Project\\"."User\\"."status.php";

require __DIR__ . "\\Route.Private.Project\\"."User\\"."notifications.php";

require __DIR__ . "\\Route.Private.Project\\"."User\\"."review.php";

require __DIR__ . "\\Route.Private.Project\\"."User\\"."report.php";

require __DIR__ . "\\Route.Private.Project\\"."User\\"."booking.php";

require __DIR__ . "\\Route.Private.Project\\"."User\\"."favorite.php";

############### End User ######################


############### Start Facilities ######################

require __DIR__ . "\\Route.Private.Project\\"."Facilities\\"."search_facilities.php";

require __DIR__ . "\\Route.Private.Project\\"."Facilities\\"."proposals.php";

############### End Facilities ######################


require __DIR__ . "\\Route.Private.Project\\"."Chat\\"."chats.php";


