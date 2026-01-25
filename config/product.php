<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Product Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your product settings for your application.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | SKU Separator
    |--------------------------------------------------------------------------
    | This value determines the separator used in generating SKUs for
    | product variants. You can set this in your ".env" file.
    |*/
    'sku_separator' => env('PRODUCT_SKU_SEPARATOR', '_'),

];