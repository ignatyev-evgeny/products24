<?php

namespace App\Http\Controllers\Integration;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Deals;
use App\Models\Integration;
use App\Models\Product;
use App\Models\ProductField;
use App\Models\ProductItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class ProductController extends Controller {
    public function index(Request $request)
    {

        if(empty($request->deal) && empty($request->integration)) {

            if(empty(json_decode($request->PLACEMENT_OPTIONS)->ID)) {
                return view('errorPage', [
                    'error' => __("Идентификатор сделки не передан"),
                    'errors' => []
                ]);
            }

            $dealId = json_decode($request->PLACEMENT_OPTIONS)->ID;

            $urlString = '';
            foreach ($request->all() as $key => $value) {
                $urlString .= $key . '=' . $value."&";
            }

            Log::channel('requestFromBitrix')->info("https://ooomts.home/?".$urlString);

            $rules = [
                'DOMAIN' => 'required|string',
                'AUTH_ID' => 'required|string',
                'REFRESH_ID' => 'required|string',
                'AUTH_EXPIRES' => 'required|integer',
                'LANG' => 'required|string',
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return view('errorPage', [
                    'error' => __("Ошибка валидации"),
                    'errors' => $validator->errors()
                ]);
            }

            $validatedData = $validator->validated();

            if(!self::checkInstall($validatedData)) {
                self::install($validatedData);
            }

            $integration = Integration::where('domain', $validatedData['DOMAIN'])->first();

            if(!self::refreshIntegration($integration, $validatedData)) {
                return Controller::failResponse([
                    "message" => __("Ошибка при обновление токена портала $integration->domain")
                ]);
            }

        } else {
            $integration = Integration::find($request->integration);
            if(empty($integration)) {
                return Controller::failResponse([
                    "message" => __("Интеграция не найдена")
                ]);
            }
            $dealId = $request->deal;
        }

//        $updateCatalogList = self::catalogList($integration);
//        if(! $updateCatalogList['success']) {
//            return Controller::failResponse([
//                'message' => $updateCatalogList['message']
//            ]);
//        }
//
//        if(ProductField::where('integration', $integration->id)->count() == 0) {
//            $updateProductFields = self::productFields($integration);
//            if(! $updateProductFields['success']) {
//                return Controller::failResponse([
//                    'message' => $updateProductFields['message']
//                ]);
//            }
//        }

        return view('products.productItem', [
            'integration' => $integration,
            'dealId' => $dealId,
            'fields' => self::getProductFilteredFields($integration),
        ]);

    }

    public function productList(Integration $integration, int $deal) {
        return view('products.list', [
            'integration' => $integration,
            'dealId' => $deal,
            'fields' => self::getProductFilteredFields($integration),
        ]);
    }

    public function getProduct(Integration $integration, Request $request) {

        if(empty($request->dealId)) {
            return response()->json([
                'success' => false,
                'message' => __("Идентификатор сделки не передан")
            ], 400);
        }

        $allowedFields = self::getProductAllowedFields($integration);
        $products = Product::query()->select('bitrix_id', 'integration', 'id', 'name', 'price', 'currency_id', 'fields')->where('integration', $integration->id);

        return DataTables::of($products)
            ->addColumn('action', function($row) use ($request) {
                return "<input type='number' min='0.01' step='0.01' class='text-center' placeholder='Стоимость' id='price_$row->bitrix_id'><br><input type='number' min='1' step='1' class='text-center' placeholder='Колличество' id='count_$row->bitrix_id'><br><a onclick='addProductToDeal($row->bitrix_id, $request->dealId)' id='button_add_product_$row->bitrix_id' class='btn btn-success btn-sm w-100 mt-1'>Добавить</bra>";
            })
            ->addColumn('fields', function($row) use ($allowedFields) {
                $fields = [];
                foreach ($row->fields as $field) {
                    if(in_array($field['bitrix_code'], $allowedFields)) {
                        $fields[$field['bitrix_code']] = [
                            'title' => $field['title'],
                            'value' => $field['value']
                        ];
                    }
                }
                return $fields;
            })
            ->filter(function ($query) use ($request) {
                if ($request->has('search') && $request->search['value']) {
                    $searchValue = $request->search['value'];
                    $query->where(function ($query) use ($searchValue) {
                        $query->where('name', 'like', "%{$searchValue}%")
                            ->orWhere('fields', 'like', "%{$searchValue}%")
                            ->orWhere('price', 'like', "%{$searchValue}%");
                    });
                }
            })
            ->removeColumn('currency_id')
            ->removeColumn('integration')
            ->removeColumn('price')
            ->removeColumn('id')
            ->rawColumns(['action'])
            ->make(true);
    }

    public function getProductItem(Integration $integration, Request $request) {

        if(empty($request->dealId)) {
            return response()->json([
                'success' => false,
                'message' => __("Идентификатор сделки не передан")
            ], 400);
        }

        $dealInfo = Http::get("https://$integration->domain/rest/crm.deal.get?id=".$request->dealId."&auth=$integration->auth_id");

        if($dealInfo->status() != 200 || empty($dealInfo->object()->result->COMPANY_ID)) {
            return [
                'success' => false,
                'message' => __("Ошибка соединения с порталом <b>$integration->domain</b>")
            ];
        }

        $company = Company::where('bitrix_id', $dealInfo->object()->result->COMPANY_ID)->first();

        if(empty($company)) {
            return response()->json([
                'success' => false,
                'message' => __("Компания - ".$dealInfo->object()->result->COMPANY_ID." - указанная в сделке не найдена")
            ], 400);
        }

        $productItem = ProductItem::query()->select('integration_id', 'company_id', 'deal_id', 'bitrix_id', 'productId', 'productName', 'price', 'priceAccount', 'priceExclusive', 'priceNetto', 'priceBrutto', 'quantity', 'discountTypeId', 'discountRate', 'discountSum', 'taxRate', 'taxIncluded', 'customized', 'measureCode', 'measureName', 'type')
            ->where('integration_id', $integration->id)
            ->where('company_id', $company->bitrix_id)
            ->orderBy('deal_id', 'DESC');

        return DataTables::of($productItem)
            ->addColumn('action', function($row) use ($request) {
                return "<input type='number' min='0.01' step='0.01' class='text-center' value='{$row->priceBrutto}' placeholder='Стоимость' id='price_$row->bitrix_id'><br><input type='number' min='1' step='1' class='text-center mt-1' placeholder='Колличество' id='count_$row->bitrix_id' value='{$row->quantity}'><br><a onclick='addProductItemToDeal($row->bitrix_id, $row->productId, $request->dealId)' id='button_add_product_$row->bitrix_id' class='btn btn-success btn-sm w-100 mt-1'>Добавить</bra>";
            })->addColumn('total', function($row) use ($request) {
                return number_format(subtractPercentage($row->priceBrutto, $row->discountRate) * $row->quantity, 2, ',', ' ')." RUB";
            })
            ->addColumn('amount', function($row) use ($request) {
                return number_format($row->priceBrutto, 2, ',', ' ')." RUB";
            })
            ->addColumn('tax', function($row) use ($request) {
                return $row->taxRate."%";
            })
            ->addColumn('article', function($row) use ($request) {
                return $row->article;
            })
            ->addColumn('analogs', function($row) use ($request) {
                return $row->analogs;
            })
            ->addColumn('deal', function($row) use ($request) {
                return "<a target='_blank' href='https://ooomts.bitrix24.ru/crm/deal/details/{$row->deal_id}/'>{$row->deal_id}</a>";
            })
            ->addColumn('discount', function($row) use ($request) {
                return $row->discountRate."%";
            })
            ->filter(function ($query) use ($request) {
                if ($request->has('search') && $request->search['value']) {
                    $searchValue = $request->search['value'];
                    $query->where(function ($query) use ($searchValue) {
                        $query->where('productName', 'like', "%{$searchValue}%")
                            ->orWhere('deal_id', 'like', "%{$searchValue}%")
                            ->orWhere('article', 'like', "%{$searchValue}%")
                            ->orWhere('analogs', 'like', "%{$searchValue}%");
                    });
                }
            })
            ->removeColumn('deal_id')
            ->removeColumn('bitrix_id')
            ->removeColumn('company_id')
            ->removeColumn('customized')
            ->removeColumn('discountRate')
            ->removeColumn('discountTypeId')
            ->removeColumn('integration_id')
            ->removeColumn('measureCode')
            ->removeColumn('measureName')
            ->removeColumn('priceAccount')
            ->removeColumn('priceBrutto')
            ->removeColumn('priceExclusive')
            ->removeColumn('priceNetto')
            ->removeColumn('productId')
            ->removeColumn('taxIncluded')
            ->removeColumn('taxRate')
            ->removeColumn('type')
            ->removeColumn('price')
            ->rawColumns(['action', 'total', 'tax', 'amount', 'discount', 'deal', 'article', 'analogs'])
            ->make(true);
    }

    public static function checkInstall(array $validatedData)
    {
        return Integration::where('domain', $validatedData['DOMAIN'])->exists();
    }

    public static function install(array $validatedData)
    {
        return Integration::create([
            'domain' => $validatedData['DOMAIN'],
            'auth_id' => $validatedData['AUTH_ID'],
            'refresh_id' => $validatedData['REFRESH_ID'],
            'expire' => $validatedData['AUTH_EXPIRES'],
            'lang' => $validatedData['LANG']
        ]);
    }

    public static function catalogList(Integration $integration)
    {

        $catalogList = Http::get("https://$integration->domain/rest/catalog.catalog.list?auth=$integration->auth_id&start=100");

        if($catalogList->status() != 200 || empty($catalogList->object()->result->catalogs)) {
            return [
                'success' => false,
                'message' => __("Ошибка соединения с порталом <b>$integration->domain</b>")
            ];
        }

        $catalogIDs = [];
        foreach ($catalogList->object()->result->catalogs as $catalog) {
            $catalogIDs[] = $catalog->id;
        }

        $integration->catalogs = $catalogIDs;
        $integration->save();

        return [
            'success' => true,
            'message' => __("Список каталогов портала <b>$integration->domain</b> успешно получен")
        ];

    }

    public static function productFields(Integration $integration)
    {
        $fields = Http::get("https://$integration->domain/rest/crm.product.fields?auth=$integration->auth_id");

        if($fields->status() != 200 || empty($fields->object()->result)) {
            return [
                'success' => false,
                'message' => __("Ошибка соединения с порталом <b>$integration->domain</b>")
            ];
        }

        foreach ($fields->object()->result as $code => $field) {
            ProductField::updateOrCreate([
                'integration' => $integration->id,
                'code' => $code
            ], [
                'title' => $field->title,
                'value' => $field->values ?? null
            ]);
        }

        return [
            'success' => true,
            'message' => __("Список пользовательских полей товарных позиций портала <b>$integration->domain</b> успешно получен")
        ];
    }

    public static function refreshIntegration(Integration $integration, array $validatedData)
    {
        try {
            $integration->auth_id = $validatedData['AUTH_ID'];
            $integration->refresh_id = $validatedData['REFRESH_ID'];
            $integration->expire = $validatedData['AUTH_EXPIRES'];
            $integration->save();
            return true;
        } catch (\Exception $exception) {
            report($exception);
            return false;
        }
    }

    public static function getProductFields(Integration $integration): array {
        $fields = [];
        $integrationFields = ProductField::where('integration', $integration->id)->get();
        foreach ($integrationFields->toArray() as $field) {
            $fields[$field['code']] = $field['title'];
        }
        return $fields;
    }

    public static function getProductFilteredFields(Integration $integration) {

        $productFields = self::getProductFields($integration);

        unset($productFields['PRICE']);
        unset($productFields['ID']);
        unset($productFields['CATALOG_ID']);
        unset($productFields['CODE']);
        unset($productFields['DESCRIPTION']);
        unset($productFields['DESCRIPTION_TYPE']);
        unset($productFields['SECTION_ID']);
        unset($productFields['SORT']);
        unset($productFields['ACTIVE']);
        unset($productFields['VAT_ID']);
        unset($productFields['VAT_INCLUDED']);
        unset($productFields['MEASURE']);
        unset($productFields['XML_ID']);
        unset($productFields['PREVIEW_PICTURE']);
        unset($productFields['DETAIL_PICTURE']);
        unset($productFields['DATE_CREATE']);
        unset($productFields['TIMESTAMP_X']);
        unset($productFields['MODIFIED_BY']);
        unset($productFields['CREATED_BY']);
        unset($productFields['PROPERTY_45']);
        unset($productFields['PROPERTY_107']);
        unset($productFields['PROPERTY_117']);
        unset($productFields['PROPERTY_119']);
        unset($productFields['PROPERTY_121']);
        unset($productFields['PROPERTY_125']);
        unset($productFields['PROPERTY_131']);
        unset($productFields['PROPERTY_147']);
        unset($productFields['PROPERTY_153']);
        unset($productFields['PROPERTY_115']);
        unset($productFields['PROPERTY_133']);
        unset($productFields['CURRENCY_ID']);

        return $productFields;

    }

    public static function getProductAllowedFields(Integration $integration) {
        $allowedFields = [];
        foreach (self::getProductFilteredFields($integration) as $key => $field) {
            $allowedFields[] = $key;
        }
        return $allowedFields;
    }
}
