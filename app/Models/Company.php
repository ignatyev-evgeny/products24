<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model {
    public $timestamps = false;
    protected $fillable = [
        'integration_id',
        'bitrix_id',
        'title',
        'type'
    ];
}
