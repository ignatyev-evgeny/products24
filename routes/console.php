<?php

use App\Console\Commands\UpdateCompanyList;
use App\Console\Commands\UpdateDealList;
use App\Console\Commands\UpdateDealProductsList;
use App\Console\Commands\UpdateProductList;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Schedule::command(UpdateCompanyList::class)->everyFifteenMinutes();
Schedule::command(UpdateDealList::class)->everyFifteenMinutes();
Schedule::command(UpdateProductList::class)->hourly();
Schedule::command(UpdateDealProductsList::class)->hourly();