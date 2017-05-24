<?php

# app/Models/Mfl_players_table.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mfl_players_table extends Model
{

    protected $table = 'mfl_players_table';

    protected $fillable = [
        'id',
        'name',
        'position',
        'team',
        'created_at',
        'updated_at'
    ];
    
}

?>