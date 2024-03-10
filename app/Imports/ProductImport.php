<?php

namespace App\Imports;

use App\Models\Product;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Event\Runtime\PHP;
use Maatwebsite\Excel\Concerns\{ToModel, WithUpserts, WithHeadingRow, Importable};
use function Symfony\Component\Translation\t;


class ProductImport implements ToModel, WithUpserts, WithHeadingRow
{
    use Importable;

    private int $totalRows = 0;
    private int $imported = 0;
    private int $skipped = 0;
    private bool $isTest;

    public function __construct( bool $isTest = false )
    {
        $this->isTest = $isTest;
    }

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model( array $row )
    {
        $skuKey = str_replace(' ', '_', strtolower('Product Code'));
        $productNameKey = str_replace(' ', '_', strtolower('Product Name'));
        $productDescKey = str_replace(' ', '_', strtolower('Product Description'));
        $productStockKey = str_replace(' ', '_', strtolower('Stock') );
        $productPriceKey = str_replace(' ', '_', strtolower('Cost in GBP'));
        $productDiscontinuedKey = str_replace(' ', '_', strtolower('Discontinued'));

        $headers = [
            'Product Code' => $skuKey,
            'Product Name' => $productNameKey,
            'Product Description' => $productDescKey,
            'Stock' => $productStockKey,
            'Cost in GBP' => $productPriceKey,
            'Discontinued' => $productDiscontinuedKey
        ];

        // Check headers
        foreach ( $headers as $key => $value  ) {
            if ( !isset( $value ) ) {
                echo 'Column "' . $key . '" not found' . PHP_EOL;
                return null;
            }
        }
        $this->totalRows++;

        $sku = $row[$skuKey];
        $stock = intval( $row[$productStockKey] );
        $price = number_format( floatval( $row[$productPriceKey] ), 2, '.', '');

        // Check if cost and stock meet the specified criteria
        if ( ( $price < 5 || $stock < 10 ) || $price > 1000 ) {
            $this->skipped++;
            echo 'Product with sku: ' . $sku . '. Was skipped' . PHP_EOL;
            return null; // Skip importing this item
        }


        $name = $row[$productNameKey];
        $description = $row[$productDescKey];
        $discontinued = $row[$productDiscontinuedKey];

        $this->imported++;

        if ( $this->isTest ) {
            echo 'Product with sku: ' . $sku . '. Was imported. TEST IMPORT' . PHP_EOL;
            return null; // Skip importing this item (test import)
        }

        return new Product([
            'strProductName' => $name,
            'strProductDesc' => $description,
            'strProductCode' => $sku,
            'stock' => $stock,
            'price' => $price,
            'discontinued' => $discontinued === 'yes' ? now()->toDateString() : null
        ]);
    }

    public function uniqueBy(): string
    {
        return 'strProductCode';
    }

    public function getTotalRows(): int
    {
        return $this->totalRows;
    }

    public function getImportedCount(): int
    {
        return $this->imported;
    }

    public function getSkippedCount(): int
    {
        return $this->skipped;
    }
}
