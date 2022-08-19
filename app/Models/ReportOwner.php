<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportOwner extends Model
{
    use HasFactory;
    protected $table = "report_owners";
    protected $fillable =[
        "id_owner","id_user","report"
    ];
}
