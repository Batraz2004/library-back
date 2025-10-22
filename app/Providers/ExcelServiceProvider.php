<?php

namespace App\Providers;

use App\Services\ExcelService;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class ExcelServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(ExcelService::class, function(Application $app){
            return new ExcelService(new Spreadsheet());
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
