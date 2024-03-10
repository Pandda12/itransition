<?php

namespace Tests\Feature;

use App\Models\Product;

use App\Imports\ProductImport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class ProductImportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Generate a CSV file with sample product data
        $csvData = "Product Code,Product Name,Product Description,Stock,Cost in GBP,Discontinued\n";
        $csvData .= ",Product 1,Description 1,15,10.50,no\n";
        $csvData .= "DEF456,Product 2,Description 2,5,3.5,\n";
        $csvData .= "GHI789,Product 3,Description 3,20,1500,\n";
        $csvData .= "JKL012,Product 4,Description 4,15,399,\n";

        // Save the CSV file to a temporary location
        File::put(public_path('files/testStock.csv'), $csvData);
    }

    protected function tearDown(): void
    {
        // Delete the temporary CSV file after the test
        $csvFilePath = public_path('files/testStock.csv');
        if (File::exists($csvFilePath)) {
            File::delete($csvFilePath);
        }

        parent::tearDown();
    }

    /** @test */
    public function it_imports_products_from_csv_file()
    {
        // Initialize the ProductImport class
        $import = new ProductImport();

        // Expecting output messages for skipped and imported products
        $expectedOutput = 'Column "Product Code" not found' . PHP_EOL .
            "Product with sku: DEF456. Was skipped\n" .
            "Product with sku: GHI789. Was skipped\n";
        // Use output buffering to capture the output
        ob_start();
        $import->import(public_path('files/testStock.csv'));
        $actualOutput = ob_get_clean();

        // Assert that the expected and actual output strings are identical
        $this->assertEquals($expectedOutput, $actualOutput);
    }

}
