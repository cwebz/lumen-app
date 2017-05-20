<?php

# app/Models/Mfl_checktrade_timestamps.php 

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mfl_checktrade_timestamps extends Model
{

    protected $table = 'mfl_checktrade_timestamps';

    protected $primaryKey = 'mfl_league_id';

    public $incrementing = false;

    protected $fillable = [
        'mfl_league_id',
        'lasttrade_timestamp',
        'created_at',
        'updated_at'
    ];
    
}

?>