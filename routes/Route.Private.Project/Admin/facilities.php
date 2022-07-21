<?php

use Illuminate\Support\Facades\Route;
use \App\Http\Controllers\Api\Admin\UsersController;


Route::controller(UsersController::class)->prefix("admin\dashboard\user")->group(function (){
    Route::get("showAll","ShowUsersAll");
    Route::get("profile","UserProfileOther");
    Route::get("bookings","UserBooking");
    Route::get("count","CountUsersInSystem");
    Route::post("add","AddUser");
    Route::post("update","UpdateUser");
    Route::delete("delete","DeleteUser");
});
