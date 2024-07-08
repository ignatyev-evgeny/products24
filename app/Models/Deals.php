<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Deals extends Model {

    public $timestamps = false;
    protected $fillable = [
        'integration_id',
        'company_id',
        'bitrix_id',
        'contact_id',
        'title'
    ];

}
