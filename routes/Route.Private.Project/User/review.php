<?php

use Illuminate\Support\Facades\Route;
use \App\Http\Controllers\Api\User\ReviewController;


Route::controller(ReviewController::class)->prefix("user")->group(function (){
    Route::get("show","ShowReviewAll");
    Route::post("rate","CreateReviewRating");
    Route::post("comment","CreateReviewComment");
});
