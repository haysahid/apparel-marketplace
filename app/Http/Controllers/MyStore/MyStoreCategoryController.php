<?php

namespace App\Http\Controllers\MyStore;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Repositories\CategoryRepository;
use Exception;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MyStoreCategoryController extends Controller
{
    protected $storeId;

    public function __construct()
    {
        $this->storeId = session('selected_store_id');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $limit = $request->input('limit', 10);
        $search = $request->input('search');
        $orderBy = $request->input('order_by', 'created_at');
        $orderDirection = $request->input('order_direction', 'desc');

        $categories = CategoryRepository::getCategories(
            storeId: $this->storeId,
            limit: $limit,
            search: $search,
            orderBy: $orderBy,
            orderDirection: $orderDirection,
        );

        return Inertia::render('MyStore/Category', [
            'categories' => $categories,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('MyStore/Category/AddCategory');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'nullable|file|mimes:jpg,jpeg,png,webp|max:2048',
            'is_dialog' => 'nullable|boolean',
        ], [
            'name.required' => 'Nama kategori harus diisi.',
            'name.string' => 'Nama kategori harus berupa string.',
            'name.max' => 'Nama kategori tidak boleh lebih dari 255 karakter.',
            'image.file' => 'File gambar harus valid.',
            'image.mimes' => 'Gambar harus berupa file dengan format jpg, jpeg, png, atau webp.',
            'image.max' => 'Ukuran gambar tidak boleh lebih dari 2MB.',
        ]);

        try {
            CategoryRepository::createCategory([
                'store_id' => $this->storeId,
                'name' => $validated['name'],
                'image' => $request->file('image') ? $request->file('image') : null,
            ]);

            if ($request->input('is_dialog')) {
                return redirect()->back()->with('success', 'Kategori berhasil ditambahkan.');
            }

            return redirect()->route('my-store.category')->with('success', 'Kategori berhasil ditambahkan.');
        } catch (Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Category $category)
    {
        return Inertia::render('MyStore/Category/EditCategory', [
            'category' => $category,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'nullable|file|mimes:jpg,jpeg,png,webp|max:2048',
        ], [
            'name.required' => 'Nama kategori harus diisi.',
            'name.string' => 'Nama kategori harus berupa string.',
            'name.max' => 'Nama kategori tidak boleh lebih dari 255 karakter.',
            'image.file' => 'File gambar harus valid.',
            'image.mimes' => 'Gambar harus berupa file dengan format jpg, jpeg, png, atau webp.',
            'image.max' => 'Ukuran gambar tidak boleh lebih dari 2MB.',
        ]);

        try {
            CategoryRepository::updateCategory($category, [
                'name' => $validated['name'],
                'image' => $request->file('image') ? $request->file('image') : null,
            ]);

            return redirect()->route('my-store.category')->with('success', 'Kategori berhasil diperbarui.');
        } catch (Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        try {
            CategoryRepository::deleteCategory($category);

            return redirect()->route('my-store.category')->with('success', 'Kategori berhasil dihapus.');
        } catch (Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
