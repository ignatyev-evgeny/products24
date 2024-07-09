<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\Integration;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class UpdateCompanyList extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'product:update-company-list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Команда для обновления списка компаний на портале';

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

                $companyList = Http::get("https://$integration->domain/rest/crm.company.list?auth=$integration->auth_id&start=$start&select[]=ID&select[]=COMPANY_TYPE&select[]=TITLE");

                if($companyList->status() != 200 || empty($companyList->object()->result)) {
                    $this->errorMessage("Ошибка соединения с порталом - ".$companyList->status());
                    return false;
                }

                foreach ($companyList->object()->result as $company) {

                    $localCompany = Company::firstOrCreate([
                        'integration_id' => $integration->id,
                        'bitrix_id' => $company->ID,
                        'title' => $company->TITLE,
                        'type' => $company->COMPANY_TYPE,
                    ]);

                    if ($localCompany->wasRecentlyCreated) {
                        $this->log($counter." - Успешно создана - ".$company->ID." - ".$localCompany->bitrix_id);
                    } else {
                        $this->log($counter." - Уже существует - ".$company->ID." - ".$localCompany->bitrix_id);
                    }

                    $counter++;
                }

                $start = isset($companyList->object()->next) ? $companyList->object()->next : null;

            } while (isset($companyList->object()->next));

        }

    }

    public function log(string $message) {
        $this->info($message);
    }

    public function errorMessage(string $message): void {
        $this->error($message);
    }
}
