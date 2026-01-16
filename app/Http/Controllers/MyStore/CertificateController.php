<?php

namespace App\Http\Controllers\MyStore;

use App\Http\Controllers\Controller;
use App\Models\StoreCertificate;
use App\Repositories\StoreCertificateRepository;
use Exception;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CertificateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $limit = $request->input('limit', 5);
        $search = $request->input('search');
        $orderBy = $request->input('order_by', 'created_at');
        $orderDirection = $request->input('order_direction', 'desc');

        $certificates = StoreCertificateRepository::getCertificates(
            storeId: session('selected_store_id'),
            limit: $limit,
            search: $search,
            orderBy: $orderBy,
            orderDirection: $orderDirection,
        );

        return Inertia::render('MyStore/Certificate/Index', [
            'certificates' => $certificates,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('MyStore/Certificate/AddCertificate');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'image' => 'required|file|mimes:pdf,doc,docx,png,jpg,jpeg,webp|max:2048',
        ], [
            'name.required' => 'Nama sertifikat harus diisi.',
            'name.string' => 'Nama sertifikat harus berupa string.',
            'name.max' => 'Nama sertifikat tidak boleh lebih dari 255 karakter.',
            'description.string' => 'Deskripsi sertifikat harus berupa string.',
            'description.max' => 'Deskripsi sertifikat tidak boleh lebih dari 1000 karakter.',
            'image.required' => 'File sertifikat harus diunggah.',
            'image.file' => 'File sertifikat harus berupa file.',
            'image.mimes' => 'File sertifikat harus berupa file dengan format: pdf, doc, docx, png, jpg, jpeg, webp.',
            'image.max' => 'Ukuran file sertifikat tidak boleh lebih dari 2MB.',
        ]);

        try {
            $data = [
                'store_id' => session('selected_store_id'),
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'image' => $request->file('image'),
            ];

            StoreCertificateRepository::createCertificate($data);

            return redirect()->route('my-store.certificate.index')->with('success', 'Sertifikat berhasil dibuat.');
        } catch (Exception $e) {
            return redirect()->back()->withErrors(['error' => $e]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(StoreCertificate $storeCertificate)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(StoreCertificate $storeCertificate)
    {
        return Inertia::render('MyStore/Certificate/EditCertificate', [
            'certificate' => $storeCertificate,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, StoreCertificate $storeCertificate)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'image' => 'nullable|file|mimes:pdf,doc,docx,png,jpg,jpeg,webp|max:2048',
        ], [
            'name.required' => 'Nama sertifikat harus diisi.',
            'name.string' => 'Nama sertifikat harus berupa string.',
            'name.max' => 'Nama sertifikat tidak boleh lebih dari 255 karakter.',
            'description.string' => 'Deskripsi sertifikat harus berupa string.',
            'description.max' => 'Deskripsi sertifikat tidak boleh lebih dari 1000 karakter.',
            'image.file' => 'File sertifikat harus berupa file.',
            'image.mimes' => 'File sertifikat harus berupa file dengan format: pdf, doc, docx, png, jpg, jpeg, webp.',
            'image.max' => 'Ukuran file sertifikat tidak boleh lebih dari 2MB.',
        ]);

        try {
            $data = [
                'name' => $validated('name'),
                'description' => $validated('description') ?? null,
                'image' => $request->file('image'),
            ];

            StoreCertificateRepository::updateCertificate($storeCertificate, $data);

            return redirect()->route('my-store.certificate.index')->with('success', 'Sertifikat berhasil diperbarui.');
        } catch (Exception $e) {
            return redirect()->back()->withErrors(['error' => $e]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(StoreCertificate $storeCertificate)
    {
        try {
            StoreCertificateRepository::deleteCertificate($storeCertificate);

            return redirect()->route('my-store.certificate.index')->with('success', 'Sertifikat berhasil dihapus.');
        } catch (Exception $e) {
            return redirect()->back()->withErrors(['error' => $e]);
        }
    }
}
