<?php

namespace App\Http\Resources;

use App\Http\Controllers\Integration\ProductController;
use App\Models\Integration;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class ProductListResource extends JsonResource {


    public function toArray(Request $request): array {
        $integration = Integration::find($this->integration);
        $fields = ProductController::getProductFilteredFields($integration);

        $productFields = [];
        foreach ($this->fields as $field) {
            $productFields[$field['bitrix_code']] =  [
                'title' => $field['title'],
                'value' => $field['value']
            ];
        }

        $productFormatFields = [];
        $productFormatFields['bitrix'] = $this->bitrix_id;
        foreach ($fields as $key => $field) {
            if(Str::contains($key, "PROPERTY_")) {
                $productFormatFields[$productFields[$key]['title']] = $productFields[$key]['value'];
            } else {
                $newKey = mb_strtolower($key);
                $productFormatFields[$field] = $this->$newKey;
            }
        }

        return $productFormatFields;
    }
}
