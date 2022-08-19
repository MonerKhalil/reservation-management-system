<?php

use App\Http\Controllers\Api\facilities\FavoriteController;

Route::controller(FavoriteController::class)->prefix("favorite")->group(function (){
    Route::post("toggle","AddOrRemoveFavorite");
    Route::get("show","ShowFavorite");
});
