<?php

namespace App\Jobs;

use App\Models\Product;
use App\Repositories\MediaRepository;
use App\Repositories\ProductRepository;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class DownloadProductImage implements ShouldQueue
{
    use Queueable, Dispatchable;

    protected $imageUrl;
    protected $productId;
    protected $order;

    public function __construct($imageUrl, $productId, $order)
    {
        $this->imageUrl = $imageUrl;
        $this->productId = $productId;
        $this->order = $order;
    }

    public function handle()
    {
        $basename = basename(parse_url($this->imageUrl, PHP_URL_PATH));
        if (!preg_match('/\.[a-zA-Z0-9]+$/', $basename)) {
            $basename .= '.jpg';
        }
        $imagePath = 'tmp/' . $basename;

        Log::info('----------------------------------');
        Log::info('Processing image: ' . $imagePath);

        $existingImage = Storage::exists($imagePath);

        if ($existingImage) {
            Log::info('Image already exists: ' . $imagePath);

            // Save image path to database
            $product = Product::find($this->productId);
            $file = Storage::path($imagePath);
            $newMedia = MediaRepository::createMedia(
                model: $product,
                file: $file,
                collectionName: 'product',
            );
            $newMedia->file_name = ProductRepository::mediaGenerateNewFileName(
                $newMedia,
                $product
            );
            $newMedia->order_column = $this->order;
            $newMedia->save();

            Log::info('Image path saved to database: ' . $imagePath);

            return;
        }

        Log::info('Downloading image async: ' . $this->imageUrl);

        try {
            $imageContents = @file_get_contents($this->imageUrl);
            if ($imageContents !== false) {
                $product = Product::find($this->productId);

                Log::info('Saving image to path: ' . $imagePath);
                Storage::put($imagePath, $imageContents);

                $file = Storage::path($imagePath);
                $newMedia = MediaRepository::createMedia(
                    model: $product,
                    file: $file,
                    collectionName: 'product',
                );
                $newMedia->file_name = ProductRepository::mediaGenerateNewFileName(
                    $newMedia,
                    $product
                );
                $newMedia->order_column = $this->order;
                $newMedia->save();
            }
        } catch (Exception $e) {
            Log::error('Error downloading image: ' . $this->imageUrl);
            Log::error('Exception message: ' . $e->getMessage());
        }
    }
}
