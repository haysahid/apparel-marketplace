<?php

namespace App\Http\Controllers\MyStore;

use App\Http\Controllers\Controller;
use App\Models\Promotion;
use App\Repositories\PromotionRepository;
use Exception;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PromotionController extends Controller
{
    private $storeId;

    public function __construct()
    {
        $this->storeId = session('selected_store_id') ?? null;
    }

    public function index(Request $request)
    {
        $limit = $request->input('limit', 10);
        $orderBy = $request->input('order_by', 'created_at');
        $orderDirection = $request->input('order_direction', 'desc');
        $search = $request->input('search');

        $promotions = PromotionRepository::getPromotions(
            storeId: $this->storeId,
            limit: $limit,
            orderBy: $orderBy,
            orderDirection: $orderDirection,
            search: $search
        );

        return Inertia::render('MyStore/Promotion/Index', [
            'promotions' => $promotions,
        ]);
    }

    public function create()
    {
        return Inertia::render('MyStore/Promotion/AddPromotion');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'redirection_url' => 'nullable|url|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ], [
            'name.required' => 'Nama promosi wajib diisi.',
            'image.image' => 'File yang diunggah harus berupa gambar.',
            'image.max' => 'Ukuran gambar maksimal 2MB.',
            'redirection_url.url' => 'URL tujuan promosi tidak valid.',
            'redirection_url.max' => 'URL tujuan promosi maksimal 255 karakter.',
            'start_date.required' => 'Tanggal mulai wajib diisi.',
            'end_date.required' => 'Tanggal berakhir wajib diisi.',
            'end_date.after_or_equal' => 'Tanggal berakhir harus sama atau setelah tanggal mulai.',
        ]);

        try {
            PromotionRepository::createPromotion([
                ...$validated,
                'store_id' => $this->storeId,
            ]);

            return redirect()->route('my-store.promotion.index')
                ->with('success', 'Promotion created successfully.');
        } catch (Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => 'Failed to create promotion: ' . $e->getMessage()])
                ->withInput();
        }
    }

    public function edit(Promotion $promotion)
    {
        return Inertia::render('MyStore/Promotion/EditPromotion', [
            'promotion' => $promotion,
        ]);
    }

    public function update(Request $request, Promotion $promotion)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'redirection_url' => 'nullable|url|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ], [
            'name.required' => 'Nama promosi wajib diisi.',
            'image.image' => 'File yang diunggah harus berupa gambar.',
            'image.max' => 'Ukuran gambar maksimal 2MB.',
            'redirection_url.url' => 'URL tujuan promosi tidak valid.',
            'redirection_url.max' => 'URL tujuan promosi maksimal 255 karakter.',
            'start_date.required' => 'Tanggal mulai wajib diisi.',
            'end_date.required' => 'Tanggal berakhir wajib diisi.',
            'end_date.after_or_equal' => 'Tanggal berakhir harus sama atau setelah tanggal mulai.',
        ]);

        try {
            PromotionRepository::updatePromotion($promotion, $validated);

            return redirect()->route('my-store.promotion.index')
                ->with('success', 'Promotion updated successfully.');
        } catch (Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => 'Failed to update promotion: ' . $e->getMessage()])
                ->withInput();
        }
    }

    public function destroy(Promotion $promotion)
    {
        try {
            PromotionRepository::deletePromotion($promotion);

            return redirect()->route('my-store.promotion.index')
                ->with('success', 'Promotion deleted successfully.');
        } catch (Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => 'Failed to delete promotion: ' . $e->getMessage()]);
        }
    }
}
