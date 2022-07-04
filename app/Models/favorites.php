<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class favorites extends Model
{
    use HasFactory;
    protected $table = "favorites";
    protected $fillable =[
        "id_facility","id_user"
    ];
    public $timestamps = false;
}
