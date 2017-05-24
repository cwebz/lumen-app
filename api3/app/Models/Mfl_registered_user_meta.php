<?php

# app/Models/Mfl_registered_user_meta.php 

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mfl_registered_user_meta extends Model
{
    protected $table = 'mfl_registered_user_meta';

    protected $primaryKey = 'meta_id';

    protected $fillable = [
        'meta_id',
        'id',
        'league_id',
        'slack_username',
        'webhook',
        'last_proposal'
    ];
}

?>