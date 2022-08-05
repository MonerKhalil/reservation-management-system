<?php

use Illuminate\Support\Facades\Route;
use \App\Http\Controllers\Api\Admin\{UsersController,FacilitiesController};


Route::controller(UsersController::class)->prefix("admin/dashboard/user")->group(function (){
    Route::get("month","CountNewAllUsersInLast5Month");
    Route::get("count","CountUsersInSystem");
    Route::get("logout","CountUsersLogoutInSystem");
    Route::get("show","ShowUsersAllRule");
    Route::get("bookings","UserBooking");
    Route::get("profile","UserProfile");
    Route::post("add","AddUser");
    Route::post("update","UpdateUser");
    Route::delete("delete","DeleteUser");
});

Route::controller(FacilitiesController::class)->prefix("admin/dashboard")->group(function (){
    Route::get("data","AllData");
    Route::delete("facility/delete","DeleteFacility");
});
