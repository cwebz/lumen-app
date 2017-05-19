<?php

# app/Models/Mfl_tradebait_timestamps.php 

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mfl_tradebait_timestamps extends Model
{

    protected $table = 'mfl_tradebait_timestamps';

    protected $primaryKey = 'league_franchise';

    public $incrementing = false;

    protected $fillable = [
        'league_franchise',
        'tradebait_timestamp',
        'created_at',
        'updated_at'
    ];
    
}

?>