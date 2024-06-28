<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model {

    protected $fillable = [
        'integration',
        'bitrix_id',
        'name',
        'code',
        'active',
        'preview_picture',
        'detail_picture',
        'sort',
        'xml_id',
        'timestamp_x',
        'date_create',
        'modified_by',
        'created_by',
        'catalog_id',
        'section_id',
        'description',
        'description_type',
        'price',
        'currency_id',
        'vat_id',
        'vat_included',
        'measure',
        'fields'
    ];

    protected $casts = [
        'fields' => 'array'
    ];

}
