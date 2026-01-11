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

    public function addStoreLogo(Request $request)
    {
        $request->validate([
            'logo' => 'required|image|max:2048',
        ], [
            'logo.required' => 'Logo toko harus diunggah.',
            'logo.image' => 'File yang diunggah harus berupa gambar.',
            'logo.max' => 'Ukuran logo tidak boleh lebih dari 2MB.',
        ]);

        try {
            $logoPath = StoreRepository::addStoreLogo(
                storeId: $this->storeId,
                file: $request->file('logo'),
            );

            return ResponseFormatter::success(
                ['logo_url' => $logoPath],
                'Logo toko berhasil diunggah.',
                201
            );
        } catch (Exception $e) {
            return ResponseFormatter::error(
                $e->getMessage(),
                $e->getCode() ?: 500
            );
        }
    }

    public function updateStoreLogo(Request $request)
    {
        $request->validate([
            'logo' => 'required|image|max:2048',
        ], [
            'logo.required' => 'Logo toko harus diunggah.',
            'logo.image' => 'File yang diunggah harus berupa gambar.',
            'logo.max' => 'Ukuran logo tidak boleh lebih dari 2MB.',
        ]);

        try {
            $logoPath = StoreRepository::updateStoreLogo(
                storeId: $this->storeId,
                file: $request->file('logo'),
            );

            return ResponseFormatter::success(
                ['logo_url' => $logoPath],
                'Logo toko berhasil diperbarui.'
            );
        } catch (Exception $e) {
            return ResponseFormatter::error(
                $e->getMessage(),
                $e->getCode() ?: 500
            );
        }
    }

    public function deleteStoreLogo(Request $request)
    {
        try {
            StoreRepository::deleteStoreLogo(
                storeId: $this->storeId,
            );

            return ResponseFormatter::success(
                null,
                'Logo toko berhasil dihapus.'
            );
        } catch (Exception $e) {
            return ResponseFormatter::error(
                $e->getMessage(),
                $e->getCode() ?: 500
            );
        }
    }

    public function addStoreBanner(Request $request)
    {
        $request->validate([
            'banner' => 'required|image|max:4096',
        ], [
            'banner.required' => 'Banner toko harus diunggah.',
            'banner.image' => 'File yang diunggah harus berupa gambar.',
            'banner.max' => 'Ukuran banner tidak boleh lebih dari 4MB.',
        ]);

        try {
            $bannerPath = StoreRepository::addStoreBanner(
                storeId: $this->storeId,
                file: $request->file('banner'),
            );

            return ResponseFormatter::success(
                ['banner_url' => $bannerPath],
                'Banner toko berhasil diunggah.',
                201
            );
        } catch (Exception $e) {
            return ResponseFormatter::error(
                $e->getMessage(),
                $e->getCode() ?: 500
            );
        }
    }

    public function updateStoreBanner(Request $request)
    {
        $request->validate([
            'banner' => 'required|image|max:4096',
        ], [
            'banner.required' => 'Banner toko harus diunggah.',
            'banner.image' => 'File yang diunggah harus berupa gambar.',
            'banner.max' => 'Ukuran banner tidak boleh lebih dari 4MB.',
        ]);

        try {
            $bannerPath = StoreRepository::updateStoreBanner(
                storeId: $this->storeId,
                file: $request->file('banner'),
            );

            return ResponseFormatter::success(
                ['banner_url' => $bannerPath],
                'Banner toko berhasil diperbarui.'
            );
        } catch (Exception $e) {
            return ResponseFormatter::error(
                $e->getMessage(),
                $e->getCode() ?: 500
            );
        }
    }

    public function deleteStoreBanner(Request $request)
    {
        try {
            StoreRepository::deleteStoreBanner(
                storeId: $this->storeId,
            );

            return ResponseFormatter::success(
                null,
                'Banner toko berhasil dihapus.'
            );
        } catch (Exception $e) {
            return ResponseFormatter::error(
                $e->getMessage(),
                $e->getCode() ?: 500
            );
        }
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
                'Peran pengguna berhasil ditetapkan.',
                201
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
