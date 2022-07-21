<?php

use Illuminate\Support\Facades\Route;
use \App\Http\Controllers\Api\User\ProfileController;


Route::controller(ProfileController::class)->prefix("profile")->group(function (){
    Route::get("show","ShowProfileAllData");
    Route::post("update","UpdateData");
    Route::delete("delete","DeleteUserAndProfile");
});
