<?php

namespace App\Http\Controllers\MyStore\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\TemporaryMedia;
use App\Repositories\MediaRepository;
use App\Repositories\TemporaryMediaRepository;
use Exception;
use Illuminate\Http\Request;

class MediaController extends Controller
{
    private $storeId;

    public function __construct(Request $request)
    {
        $this->storeId = $request->header('X-Selected-Store-ID');
    }

    public function index(Request $request)
    {
        $limit = $request->input('limit', 10);
        $orderBy = $request->input('order_by', 'created_at');
        $orderDirection = $request->input('order_direction', 'desc');
        $search = $request->input('search');

        $model = $request->input('model');
        $collectionName = $request->input('collection_name');

        $media = MediaRepository::getAllMedia(
            storeId: $this->storeId,
            model: $model,
            collectionName: $collectionName,
            limit: $limit,
            search: $search,
            orderBy: $orderBy,
            orderDirection: $orderDirection
        );

        return ResponseFormatter::success(
            $media,
            'Daftar media berhasil diambil.'
        );
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'model_type' => 'required|string|in:product,variant',
            'model_id' => 'required|integer',
            'file' => 'required|file|max:5120', // Max 5MB
            'collection_name' => 'nullable|string',
        ], [
            'model_type.required' => 'Tipe model harus diisi.',
            'model_id.required' => 'ID model harus diisi.',
            'file.required' => 'File media harus diunggah.',
            'file.file' => 'File media harus berupa file yang valid.',
            'file.max' => 'Ukuran file media maksimal 5MB.',
        ]);

        $modelType = $validatedData['model_type'];
        $modelId = $validatedData['model_id'];
        $file = $validatedData['file'];
        $collectionName = $validatedData['collection_name'] ?? 'default';

        if ($modelType === 'product') {
            $modelType = Product::class;
        } elseif ($modelType === 'variant') {
            $modelType = ProductVariant::class;
        }

        try {
            $modelClass = app($modelType);
            $model = $modelClass::findOrFail($modelId);

            $media = MediaRepository::createMedia(
                model: $model,
                file: $file,
                collectionName: $collectionName,
            );

            return ResponseFormatter::success(
                $media,
                'Media berhasil diunggah.',
                201
            );
        } catch (Exception $e) {
            return ResponseFormatter::error(
                $e->getMessage(),
                500
            );
        }
    }

    public function show(string $id)
    {
        try {
            $media = MediaRepository::getMediaDetail($id);

            if (!$media) {
                return ResponseFormatter::error(
                    'Media tidak ditemukan.',
                    404
                );
            }

            return ResponseFormatter::success(
                $media,
                'Detail media berhasil diambil.'
            );
        } catch (Exception $e) {
            return ResponseFormatter::error(
                $e->getMessage(),
                500
            );
        }
    }

    public function destroy(string $id)
    {
        try {
            $deleted = MediaRepository::deleteMedia($id);

            if (!$deleted) {
                return ResponseFormatter::error(
                    'Media tidak ditemukan.',
                    404
                );
            }

            return ResponseFormatter::success(
                null,
                'Media berhasil dihapus.'
            );
        } catch (Exception $e) {
            return ResponseFormatter::error(
                $e->getMessage(),
                500
            );
        }
    }

    public function getAllTemporaryMedia(Request $request)
    {
        $tempMedia = TemporaryMediaRepository::getAllTemporaryMedia(
            storeId: $this->storeId,
        );

        return ResponseFormatter::success(
            $tempMedia,
            'Daftar media sementara berhasil diambil.'
        );
    }

    public function uploadTemporaryMedia(Request $request)
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

    public function getTemporaryMedia(string $id)
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
}
