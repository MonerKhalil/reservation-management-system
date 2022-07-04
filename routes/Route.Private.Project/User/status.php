<?php

use Illuminate\Support\Facades\Route;
use \App\Http\Controllers\Api\User\StatusController;


Route::controller(StatusController::class)->prefix("state")->group(function (){
    Route::put("online","Online");
    Route::put("offline","Offline");
});
