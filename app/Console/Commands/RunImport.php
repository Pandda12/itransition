<?php

namespace App\Console\Commands;

use App\Imports\ProductImport;
use Illuminate\Console\Command;

class RunImport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:run-import {importParam?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $rows = 0;
        $imported = 0;
        $skipped = 0;

        $filename = public_path('/files/stock.csv');
        $isTest = false;

        if ($this->argument('importParam') && $this->argument('importParam') !== 'test') {
            $this->error("Incorrect params");
            return;
        } elseif ( $this->argument('importParam') === 'test' ) {
            $this->info("Test import");
            $isTest = true;
//            return;
        } else {
            $this->info("Normal import");
        }

        $this->output->title('Starting import');

        $import = new ProductImport( $isTest );
        $import->import($filename, null, \Maatwebsite\Excel\Excel::CSV);

        $this->info('Rows: ' . $import->getTotalRows() . '. Imported products: ' . $import->getImportedCount() . '. Skipped products: ' . $import->getSkippedCount());

        $this->info("Import type: Normal");

    }
}
