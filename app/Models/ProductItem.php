<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductItem extends Model {
    public $timestamps = false;

    protected $fillable = [
        'integration_id',
        'company_id',
        'deal_id',
        'bitrix_id',
        'productId',
        'productName',
        'price',
        'priceAccount',
        'priceExclusive',
        'priceNetto',
        'priceBrutto',
        'quantity',
        'discountTypeId',
        'discountRate',
        'discountSum',
        'taxRate',
        'taxIncluded',
        'customized',
        'measureCode',
        'measureName',
        'type'
    ];



    
}
