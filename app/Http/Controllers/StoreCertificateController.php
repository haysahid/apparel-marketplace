<?php

namespace App\Http\Controllers;

use App\Models\StoreCertificate;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class StoreCertificateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $limit = $request->input('limit', 5);
        $sortBy = $request->input('order_by', 'created_at');
        $sortDirection = $request->input('order_direction', 'desc');
        $search = $request->input('search');

        $certificates = StoreCertificate::query();

        if ($search) {
            $certificates->where('name', 'ilike', '%' . $search . '%')
                ->orWhere('description', 'ilike', '%' . $search . '%');
        }

        $certificates->orderBy($sortBy, $sortDirection);
        $certificates->get();

        return Inertia::render('Admin/Certificate', [
            'certificates' => $certificates->paginate($limit),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Admin/Certificate/AddCertificate');
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
            DB::beginTransaction();

            StoreCertificate::create([
                'store_id' => 1,
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'image' => $request->file('image')->store('certificate'),
            ]);

            DB::commit();

            return redirect()->route('admin.certificate')->with('success', 'Sertifikat berhasil dibuat.');
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'Gagal membuat sertifikat: ' . $e->getMessage()]);
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
        return Inertia::render('Admin/Certificate/EditCertificate', [
            'certificate' => $storeCertificate,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, StoreCertificate $storeCertificate)
    {
        $request->validate([
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
            DB::beginTransaction();

            // throw new Exception("Error Processing Request", 1);

            $storeCertificate->name = $request->input('name');
            $storeCertificate->description = $request->input('description');

            if ($request->hasFile('image')) {
                // Delete old file if exists
                if ($storeCertificate->image) {
                    Storage::delete($storeCertificate->image);
                }
                $storeCertificate->image = $request->file('image')->store('certificate');
            }

            $storeCertificate->save();

            DB::commit();

            return redirect()->route('admin.certificate')->with('success', 'Sertifikat berhasil diperbarui.');
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'Gagal memperbarui sertifikat: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(StoreCertificate $storeCertificate)
    {
        try {
            $storeCertificate->delete();
            return redirect()->route('admin.certificate')->with('success', 'Sertifikat berhasil dihapus.');
        } catch (Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Gagal menghapus sertifikat: ' . $e->getMessage()]);
        }
    }
}
