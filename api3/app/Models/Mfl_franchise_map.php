<?php

# app/Models/Mfl_franchise_map.php 

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mfl_franchise_map extends Model
{

    protected $table = 'mfl_franchise_map';

    protected $primaryKey = 'league_franchise';

    protected $fillable = [
        'league_franchise',
        'franchise_name',
        'created_at',
        'updated_at'
    ];
    //Becuase it hates varchar primary keys
    protected $casts = [
        'league_franchise' => 'string',
    ];
    
}

?>