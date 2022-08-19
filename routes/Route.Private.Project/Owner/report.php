<?php


use App\Http\Controllers\Api\User\ReportController;

Route::controller(ReportController::class)->prefix("owner/report")->group(function (){
    Route::get("info","infoReport");
    Route::post("add","AddReport");
});
