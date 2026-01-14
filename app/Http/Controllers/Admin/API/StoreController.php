<?php

namespace App\Http\Controllers\Admin\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Repositories\InvoiceRepository;
use App\Repositories\StoreRepository;
use Exception;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    public function addStoreLogo(Request $request, $storeId)
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
                storeId: $storeId,
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

    public function updateStoreLogo(Request $request, $storeId)
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
                storeId: $storeId,
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

    public function deleteStoreLogo(Request $request, $storeId)
    {
        try {
            StoreRepository::deleteStoreLogo(
                storeId: $storeId,
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

    public function addStoreBanner(Request $request, $storeId)
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
                storeId: $storeId,
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

    public function updateStoreBanner(Request $request, $storeId)
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
                storeId: $storeId,
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

    public function deleteStoreBanner(Request $request, $storeId)
    {
        try {
            StoreRepository::deleteStoreBanner(
                storeId: $storeId,
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

    public function getStoreInvoices(Request $request, $storeId)
    {
        $limit = $request->input('limit', 10);
        $search = $request->input('search');
        $orderBy = $request->input('order_by', 'created_at');
        $orderDirection = $request->input('order_direction', 'desc');
        $userId = $request->input('user_id');
        $brandId = $request->input('brand_id');

        $invoices = InvoiceRepository::getInvoices(
            storeId: $storeId,
            userId: $userId,
            limit: $limit,
            search: $search,
            orderBy: $orderBy,
            orderDirection: $orderDirection,
            brandId: $brandId
        );

        return ResponseFormatter::success(
            $invoices,
            'Berhasil mengambil data pesanan.'
        );
    }

    public function addUserRole(Request $request, $storeId)
    {
        $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'role_slug' => 'required|string|exists:roles,slug',
        ], [
            'role_slug.required' => 'Peran harus diisi.',
            'role_slug.exists' => 'Peran yang dipilih tidak valid.',
        ]);

        try {
            $userId = $request->input('user_id');
            $roleSlug = $request->input('role_slug');

            StoreRepository::addUserRole(
                storeId: $storeId,
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

    public function updateUserRole(Request $request, $storeId)
    {
        $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'role_slug' => 'required|string|exists:roles,slug',
        ], [
            'role_slug.required' => 'Peran harus diisi.',
            'role_slug.exists' => 'Peran yang dipilih tidak valid.',
        ]);

        try {
            $userId = $request->input('user_id');
            $newRole = $request->input('role_slug');

            StoreRepository::updateUserRole(
                storeId: $storeId,
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

    public function removeUserRole(Request $request, $storeId)
    {
        $request->validate([
            'user_id' => 'required|integer|exists:users,id',
        ], [
            'user_id.required' => 'ID pengguna harus diisi.',
            'user_id.exists' => 'Pengguna yang dipilih tidak valid.',
        ]);

        try {
            $userId = $request->input('user_id');

            StoreRepository::removeUserRole(
                storeId: $storeId,
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
