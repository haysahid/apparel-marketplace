<?php

namespace App\Http\Controllers\MyStore\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Repositories\StoreRepository;
use Exception;
use GuzzleHttp\Psr7\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MyStoreController extends Controller
{
    private $storeId;

    public function __construct(Request $request)
    {
        $this->storeId = $request->header('X-Selected-Store-ID');
    }

    public function addUserRole(Request $request, $userId)
    {
        $request->validate([
            'role_slug' => 'required|string|exists:roles,slug',
        ], [
            'role_slug.required' => 'Peran harus diisi.',
            'role_slug.exists' => 'Peran yang dipilih tidak valid.',
        ]);

        try {
            $roleSlug = $request->input('role_slug');

            StoreRepository::addUserRole(
                storeId: $this->storeId,
                userId: $userId,
                roleSlug: $roleSlug,
            );

            return ResponseFormatter::success(
                null,
                'Peran pengguna berhasil ditetapkan.'
            );
        } catch (Exception $e) {
            return ResponseFormatter::error(
                $e->getMessage(),
                $e->getCode() ?: 500
            );
        }
    }

    public function updateUserRole(Request $request, $userId)
    {
        $request->validate([
            'role_slug' => 'required|string|exists:roles,slug',
        ], [
            'role_slug.required' => 'Peran harus diisi.',
            'role_slug.exists' => 'Peran yang dipilih tidak valid.',
        ]);

        try {
            $newRole = $request->input('role_slug');

            StoreRepository::updateUserRole(
                storeId: $this->storeId,
                userId: $userId,
                roleSlug: $newRole,
            );

            return ResponseFormatter::success(
                null,
                'Peran pengguna berhasil diubah.'
            );
        } catch (Exception $e) {
            return ResponseFormatter::error(
                $e->getMessage(),
                $e->getCode() ?: 500
            );
        }
    }

    public function removeUserRole($userId)
    {
        try {
            StoreRepository::removeUserRole(
                storeId: $this->storeId,
                userId: $userId,
            );

            return ResponseFormatter::success(
                null,
                'Peran pengguna berhasil dihapus.'
            );
        } catch (Exception $e) {
            return ResponseFormatter::error(
                $e->getMessage(),
                $e->getCode() ?: 500
            );
        }
    }
}
