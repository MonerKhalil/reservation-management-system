<?php


use App\Http\Controllers\Api\facilities\FacilitiesController;

Route::controller(FacilitiesController::class)->prefix("facilities")->group(function (){
    Route::get("displayAll","ShowFacilities");
    Route::get("show","show");
    Route::post("add","AddFacility");
    Route::post("update","UpdateFacility");
    Route::delete("delete","DeleteFacility");
    Route::post("addphotos","AddListImage");
    Route::delete("deleteAllphoto","DeleteAllImage");
    Route::delete("delete1photo","DeleteOneImage");
});
