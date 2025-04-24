<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\BudgetController;

class ImportBudgetsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'budgets:import';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import budgets from Google Sheets at 5 AM daily';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Gọi phương thức importFromGoogleSheets từ BudgetController
        $budgetController = new BudgetController();
        $budgetController->importFromGoogleSheets(request());

        $this->info('Budgets imported successfully!');
        return 0;
    }
}