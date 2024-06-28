<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Integration extends Model {

    protected $fillable = [
        'domain',
        'auth_id',
        'refresh_id',
        'expire',
        'lang',
        'catalogs',
    ];

    protected $casts = [
        'catalogs' => 'array'
    ];

}
