<?php

use Illuminate\Support\Facades\Route;
use \App\Http\Controllers\Api\Admin\UsersController;


Route::controller(UsersController::class)->prefix("admin\dashboard\user")->group(function (){
    Route::get("lastmonth","CountNewUsersInLastMonth");
    Route::get("count","CountUsersInSystem");
    Route::get("logout","CountUsersLogoutInSystem");
    Route::get("show","ShowUsersAllRule");
    Route::get("bookings","UserBooking");
    Route::get("profile","UserProfile");
    Route::post("add","AddUser");
    Route::post("update","UpdateUser");
    Route::delete("delete","DeleteUser");
});
