<?php

namespace App\Class_Public;

class DataInNotifiy
{
    private $url,$body,$type;
    public function __construct(string $url,array $body,string $type)
    {
        $this->body = $body;
        $this->type = $type;
        $this->url = $url;
    }
    public function GetAllData():DataInNotifiy{
        return $this;
    }
}
