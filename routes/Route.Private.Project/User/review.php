<?php

use Illuminate\Support\Facades\Route;
use \App\Http\Controllers\Api\User\ReviewController;


Route::controller(ReviewController::class)->prefix("user")->group(function (){
    Route::post("rate","Online");
    Route::post("comment","Offline");
});
