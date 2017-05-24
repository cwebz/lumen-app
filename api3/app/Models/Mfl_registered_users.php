<?php

# app/Models/Mfl_registered_users.php 

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mfl_registered_users extends Model
{
    protected $table = 'mfl_registered_users';

    protected $primaryKey = 'id';

    protected $fillable = [
        'id',
        'mfl_username',
        'mfl_password',
        'mfl_cookie',
    ];
}

?>