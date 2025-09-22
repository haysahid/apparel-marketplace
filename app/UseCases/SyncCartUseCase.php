<?php

namespace App\UseCases;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Store;

class SyncCartUseCase
{
    public static function sync($group)
    {
        $store = Store::find($group['store_id']);
        $group['store'] = $store;
        $group['updated_at'] = now()->toDateTimeString();

        $items = $group['items'];
        $updateItems = [];

        foreach ($items as $item) {
            $product = Product::find($item['product_id']);
            if (!$product) continue;

            $variant = ProductVariant::with(['color', 'size', 'unit'])->find($item['variant_id']);
            if (!$variant) continue;

            $updatedItem = $item;

            // Add variant and image to the item
            $updatedItem['variant'] = $variant;
            $updatedItem['image'] = $variant->images->first()->image ?? $product->images->first()->image;

            // Add created_at and updated_at timestamps
            $updatedItem['updated_at'] = $item['updated_at'] ?? now()->toDateTimeString();

            $updateItems[] = $updatedItem;
        }

        $group['items'] = $updateItems;
        return $group;
    }
}
