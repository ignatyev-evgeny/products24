<?php

namespace App\Console\Commands;

use App\Models\Deals;
use App\Models\Integration;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class UpdateDealList extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'product:update-deal-list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Команда для обновления списка успешных сделок на портале';

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

            $start = 0;


            do {

                $dealsList = Http::get("https://$integration->domain/rest/crm.deal.list?auth=$integration->auth_id&start=$start&order[ID]=DESC&filter[STAGE_ID]=WON&select[]=ID&select[]=TITLE&select[]=COMPANY_ID&select[]=CONTACT_ID");

                if($dealsList->status() != 200 || empty($dealsList->object()->result)) {
                    $this->errorMessage("Ошибка соединения с порталом - ".$dealsList->status());
                    return false;
                }

                foreach ($dealsList->object()->result as $deal) {

                    $localDeal = Deals::firstOrCreate([
                        'integration_id' => $integration->id,
                        'bitrix_id' => $deal->ID,
                        'title' => $deal->TITLE,
                        'company_id' => $deal->COMPANY_ID,
                        'contact_id' => $deal->CONTACT_ID,
                    ]);

                    if ($localDeal->wasRecentlyCreated) {
                        $this->log($counter." - Успешно создана - ".$deal->ID." - ".$localDeal->bitrix_id);
                    } else {
                        $this->log($counter." - Уже существует - ".$deal->ID." - ".$localDeal->bitrix_id);
                    }

                    $counter++;
                }

                $start = isset($dealsList->object()->next) ? $dealsList->object()->next : null;

            } while (isset($dealsList->object()->next));

        }

    }

    public function log(string $message) {
        $this->info($message);
        Log::channel('UpdateDealList')->info($message);
    }

    public function errorMessage(string $message): void {
        $this->error($message);
    }
}
