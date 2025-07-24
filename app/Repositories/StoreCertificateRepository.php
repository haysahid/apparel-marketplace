<?php

namespace App\Repositories;

use App\Models\StoreCertificate;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class StoreCertificateRepository
{
    public static function getCertificates(
        $limit = 5,
        $search = null,
        $sortBy = 'created_at',
        $sortDirection = 'desc',
    ) {
        $certificates = StoreCertificate::query();

        if ($search) {
            $certificates->where('name', 'like', '%' . $search . '%')
                ->orWhere('description', 'like', '%' . $search . '%');
        }

        $certificates->orderBy($sortBy, $sortDirection);
        $certificates->get();

        return $certificates->paginate($limit);
    }

    public static function createCertificate(array $data)
    {
        try {
            DB::beginTransaction();

            $certificate = StoreCertificate::create([
                'store_id' => $data['store_id'],
                'name' => $data['name'],
                'description' => $data['description'],
                'image' => $data['image']->store('certificates', 'public'),
            ]);

            DB::commit();

            return $certificate;
        } catch (Exception $e) {
            Log::error('Failed to create certificate: ' . $e);
            throw new Exception('Gagal menyimpan sertifikat: ' . $e);
        }
    }

    public static function updateCertificate(StoreCertificate $certificate, array $data)
    {
        try {
            DB::beginTransaction();

            $certificate->name = $data['name'];
            $certificate->description = $data['description'] ?? null;

            if (isset($data['image'])) {
                // Delete old image if exists
                if ($certificate->image) {
                    Storage::disk('public')->delete($certificate->image);
                }
                $certificate->image = $data['image']->store('certificates', 'public');
            }

            $certificate->save();

            DB::commit();

            return $certificate;
        } catch (Exception $e) {
            Log::error('Failed to update certificate: ' . $e);
            DB::rollBack();
            throw new Exception('Gagal memperbarui sertifikat: ' . $e);
        }
    }

    public static function deleteCertificate(StoreCertificate $certificate)
    {
        try {
            DB::beginTransaction();

            // Delete the certificate image if it exists
            if ($certificate->image) {
                Storage::disk('public')->delete($certificate->image);
            }

            $certificate->delete();

            DB::commit();
        } catch (Exception $e) {
            Log::error('Failed to delete certificate: ' . $e);
            DB::rollBack();
            throw new Exception('Gagal menghapus sertifikat: ' . $e);
        }
    }
}
