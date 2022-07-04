<?php

use Illuminate\Support\Facades\Route;
use \App\Http\Controllers\Api\Admin\AdminController;


Route::controller(AdminController::class)->prefix("admin")->group(function (){
    Route::get("show","ShowUsersAll");
    Route::post("add","AddUser");
    Route::post("update","UpdateUser");
    Route::delete("delete","DeleteUser");
});
