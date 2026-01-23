<?php

namespace App\Http\Controllers\MyStore\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Repositories\TemporaryMediaRepository;
use Exception;
use Illuminate\Http\Request;

class TemporaryMediaController extends Controller
{
    private $storeId;

    public function __construct(Request $request)
    {
        $this->storeId = $request->header('X-Selected-Store-ID');
    }


    public function index(Request $request)
    {
        $tempMedia = TemporaryMediaRepository::getAllTemporaryMedia(
            storeId: $this->storeId,
        );

        return ResponseFormatter::success(
            $tempMedia,
            'Daftar media sementara berhasil diambil.'
        );
    }

    public function store(Request $request)
    {
        if ($request->hasFile('file')) {
            $file = $request->file('file');

            $temp = TemporaryMediaRepository::createTemporaryMedia(
                file: $file,
                storeId: $this->storeId,
            );

            return ResponseFormatter::success(
                $temp,
                'Media sementara berhasil diunggah.',
                201
            );
        }
    }

    public function show(string $id)
    {
        try {
            $tempMedia = TemporaryMediaRepository::getTemporaryMedia($id);

            if (!$tempMedia) {
                return ResponseFormatter::error(
                    'Media sementara tidak ditemukan.',
                    404
                );
            }

            return ResponseFormatter::success(
                $tempMedia,
                'Detail media sementara berhasil diambil.'
            );
        } catch (Exception $e) {
            return ResponseFormatter::error(
                $e->getMessage(),
                500
            );
        }
    }

    public function attach(Request $request)
    {
        $validatedData = $request->validate([
            'temporary_media_ids' => 'required|array',
            'model_type' => 'required|string|in:product,variant',
            'model_id' => 'required|integer',
            'collection_name' => 'nullable|string',
        ]);

        $tempMediaIds = $validatedData['temporary_media_ids'];
        $modelType = $validatedData['model_type'];
        $modelId = $validatedData['model_id'];
        $collectionName = $validatedData['collection_name'] ?? 'default';

        if ($modelType === 'product') {
            $modelType = Product::class;
        } elseif ($modelType === 'variant') {
            $modelType = ProductVariant::class;
        }

        try {
            $modelClass = app($modelType);
            $model = $modelClass::findOrFail($modelId);

            TemporaryMediaRepository::attachTemporaryMediaToModel(
                temporaryMediaIds: $tempMediaIds,
                model: $model,
                collectionName: $collectionName,
            );

            return ResponseFormatter::success(
                null,
                'Media sementara berhasil dilampirkan ke model.'
            );
        } catch (Exception $e) {
            return ResponseFormatter::error(
                $e->getMessage(),
                500
            );
        }
    }
}
