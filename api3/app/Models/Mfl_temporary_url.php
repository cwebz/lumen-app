<?php

# app/Models/Mfl_temporary_url.php 

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mfl_temporary_url extends Model
{

    protected $table = 'mfl_temporary_url';

    //protected $primaryKey = 'id';

   //public $incrementing = false;

    protected $fillable = [
        'id',
        'url',
        'param',
        'slack_team',
        'created_at',
        'updated_at'
    ];
    
}

?>