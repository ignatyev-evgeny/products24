<?php

namespace App\Console\Commands;

use App\Models\Integration;
use App\Models\Product;
use App\Models\ProductField;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class UpdateProductList extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'product:update-product-list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Команда для обновления списка продуктов интеграции';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {

        foreach(Integration::all() as $integration) {
            $counter = 1;

            foreach ($integration->catalogs as $catalog) {

                $start = 0;

                do {

                    $productList = Http::asJson()->post("https://$integration->domain/rest/crm.product.list?auth=$integration->auth_id&start=$start", [
                        'select' => [
                            "ID",
                            "NAME",
                            "CODE",
                            "ACTIVE",
                            "PREVIEW_PICTURE",
                            "DETAIL_PICTURE",
                            "SORT",
                            "XML_ID",
                            "TIMESTAMP_X",
                            "DATE_CREATE",
                            "MODIFIED_BY",
                            "CREATED_BY",
                            "CATALOG_ID",
                            "SECTION_ID",
                            "DESCRIPTION",
                            "DESCRIPTION_TYPE",
                            "PRICE",
                            "CURRENCY_ID",
                            "VAT_ID",
                            "VAT_INCLUDED",
                            "MEASURE",
                            'PROPERTY_*',
                        ],
                        'filter' => [
                            'CATALOG_ID' => $catalog
                        ]
                    ]);

                    if($productList->status() != 200 || empty($productList->object()->result)) {
                        $this->errorMessage("Ошибка соединения с порталом - ".$productList->status());
                        return false;
                    }

                    $productFields = collect(ProductField::where('integration', $integration->id)->get()->toArray());

                    foreach ($productList->object()->result as $product) {

                        $fields = [];

                        foreach ($product as $key => $value) {

                            if(Str::contains($key, 'PROPERTY_')) {

                                $field = $productFields->where('code', $key);
                                $field = $field->first(function ($field) {
                                    return $field;
                                });

                                if(is_object($value) && is_array($field['value'])) {
                                    $productFieldValue = $field['value'][$value->value]['VALUE'];
                                } else if(is_object($value)) {
                                    $productFieldValue = $value->value;
                                } else if(is_null($value)) {
                                    $productFieldValue = null;
                                } else {
                                    $productFieldValue = $field['value'];
                                }

                                $fields[] = [
                                    'bitrix_code' => $key,
                                    'title' => $field['title'],
                                    'value' => $productFieldValue,
                                ];

                            }

                        }

                        $localProduct = Product::updateOrCreate([
                            'integration' => $integration->id,
                            'bitrix_id' => $product->ID,
                        ], [
                            'name' => $product->NAME,
                            'code' => $product->CODE,
                            'active' => $product->ACTIVE,
                            'preview_picture' => $product->PREVIEW_PICTURE,
                            'detail_picture' => $product->DETAIL_PICTURE,
                            'sort' => $product->SORT,
                            'xml_id' => $product->XML_ID,
                            'timestamp_x' => $product->TIMESTAMP_X,
                            'date_create' => $product->DATE_CREATE,
                            'modified_by' => $product->MODIFIED_BY,
                            'created_by' => $product->CREATED_BY,
                            'catalog_id' => $product->CATALOG_ID,
                            'section_id' => $product->SECTION_ID,
                            'description' => $product->DESCRIPTION,
                            'description_type' => $product->DESCRIPTION_TYPE,
                            'price' => $product->PRICE,
                            'currency_id' => $product->CURRENCY_ID,
                            'vat_id' => $product->VAT_ID,
                            'vat_included' => $product->VAT_INCLUDED,
                            'measure' => $product->MEASURE,
                            'fields' => $fields,
                        ]);

                        $this->log($counter." - ".$localProduct->id." - ".$product->NAME);
                        $counter++;
                    }

                    $start = isset($productList->object()->next) ? $productList->object()->next : null;
                    sleep(1);
                } while (isset($productList->object()->next));

            }
        }
    }

    public function log(string $message) {
        $this->info($message);
    }

    public function errorMessage(string $message): void {
        $this->error($message);
    }
}
