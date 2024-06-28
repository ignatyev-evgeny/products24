<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductField extends Model {

    protected $fillable = [
        'integration',
        'code',
        'title',
        'value'
    ];

    protected $casts = [
        'value' => 'array'
    ];

}
