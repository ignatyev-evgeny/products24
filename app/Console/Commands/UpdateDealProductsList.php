<?php

namespace App\Console\Commands;

use App\Models\Deals;
use App\Models\Integration;
use App\Models\ProductItem;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class UpdateDealProductsList extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'product:update-deal-item-list {integration}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Команда для обновления выгрузки товарных позиций';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $integration = Integration::find($this->argument('integration'));

        if(empty($integration)) {
            $this->errorMessage("Интеграция не найдена");
            return false;
        }

        $deals = Deals::where('integration_id', $integration->id)->orderBy('bitrix_id', 'DESC')->get();

        $counter = 1;

        foreach($deals as $deal) {

            $this->log($counter." - Синхронизация товарных позиций сделки - ".$deal->bitrix_id);

            $dealProductItemList = Http::get("https://$integration->domain/rest/crm.item.productrow.list?auth=$integration->auth_id&filter[%3DownerType]=D&filter[%3DownerId]=".$deal->bitrix_id);

            if($dealProductItemList->status() != 200 || empty($dealProductItemList->object()->result)) {
                $this->errorMessage("Ошибка соединения с порталом - ".$dealProductItemList->status());
                continue;
            }

            if(count($dealProductItemList->object()->result->productRows) == 0) {
                $this->errorMessage("В сделке нет товарных позиций");
                continue;
            }

            foreach ($dealProductItemList->object()->result->productRows as $productRow) {

                $this->log($counter." - Создаем товарную позицию - ".$productRow->id);

                ProductItem::firstOrCreate([
                    'integration_id' => $integration->id,
                    'company_id' => $deal->company_id,
                    'deal_id' => $deal->bitrix_id,
                    'bitrix_id' => $productRow->id,
                ], [
                    'productId' => $productRow->productId,
                    'productName' => $productRow->productName,
                    'price' => $productRow->price,
                    'priceAccount' => $productRow->priceAccount,
                    'priceExclusive' => $productRow->priceExclusive,
                    'priceNetto' => $productRow->priceNetto,
                    'priceBrutto' => $productRow->priceBrutto,
                    'quantity' => $productRow->quantity,
                    'discountTypeId' => $productRow->discountTypeId,
                    'discountRate' => $productRow->discountRate,
                    'discountSum' => $productRow->discountSum,
                    'taxRate' => $productRow->taxRate,
                    'taxIncluded' => $productRow->taxIncluded,
                    'customized' => $productRow->customized,
                    'measureCode' => $productRow->measureCode,
                    'measureName' => $productRow->measureName,
                    'type' => $productRow->type
                ]);

            };
            $this->log($counter." - Синхронизация товарных позиций сделки - ".$deal->bitrix_id.". Прошла успешно.");
            $counter++;
        }
    }

    public function log(string $message) {
        $this->info($message);
        Log::channel('UpdateProductList')->info($message);
    }

    public function errorMessage(string $message): void {
        $this->error($message);
    }
}
