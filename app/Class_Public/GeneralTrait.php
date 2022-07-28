<?php

namespace App\Class_Public;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use phpDocumentor\Reflection\DocBlock\Tags\Throws;


trait GeneralTrait
{
    public function path_file (): string
    {
        return storage_path("app\\public\\TempCountsUsers.json");
    }
    public function Paginate(string $namedata,$paginate): array
    {
        return [
            $namedata=> $paginate->items(),
            "current_page" => $paginate->currentPage(),
            "url_next_page" => $paginate->nextPageUrl(),
            "url_first_page" => $paginate->path()."?page=1",
            "url_last_page" => $paginate->path()."?page=".$paginate->lastPage(),
            "total_pages" => $paginate->lastPage(),
            "total_items" => $paginate->total()
        ];
    }
    public function NumberOfValues(Request $request): int
    {
        try {
            if($request->has("num_values")&&is_numeric($request->num_values)&&$request->num_values>0){
                return $request->num_values;
            }
            throw new \Exception("");
        }catch (\Exception $exception){
            return 10;
        }
    }
    public function Check_Date($datestr,$dateend): bool
    {
        $num = round(strtotime($dateend) - strtotime($datestr));
        if($num<0){
            return false;
        }
        return true;
    }

    public function GetJsonFile($path){
        $jsonString = file_get_contents($path);
        return json_decode($jsonString, true);
    }

    public function UpdateJsonFile($path,$newData){
        file_put_contents($path, json_encode($newData));
    }
}
