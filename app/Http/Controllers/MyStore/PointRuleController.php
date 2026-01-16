<?php

namespace App\Http\Controllers\MyStore;

use App\Http\Controllers\Controller;
use App\Models\PointRule;
use App\Repositories\PointRuleRepository;
use Exception;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PointRuleController extends Controller
{
    private $storeId;

    public function __construct()
    {
        $this->storeId = session('selected_store_id');
    }

    public function index(Request $request)
    {
        $limit = $request->input('limit', 10);
        $search = $request->input('search');
        $orderBy = $request->input('order_by', 'created_at');
        $orderDirection = $request->input('order_direction', 'desc');

        $pointRules = PointRuleRepository::getPointRules(
            storeId: $this->storeId,
            limit: $limit,
            search: $search,
            orderBy: $orderBy,
            orderDirection: $orderDirection,
        );

        return Inertia::render('MyStore/PointRule/Index', [
            'pointRules' => $pointRules,
        ]);
    }

    public function create()
    {
        return Inertia::render('MyStore/PointRule/AddPointRule');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'description' => 'nullable|string|max:255',
            'type' => 'required|string|max:50',
            'min_spend' => 'nullable|numeric|min:0',
            'points_earned' => 'required|integer|min:0',
            'conversion_rate' => 'nullable|numeric|min:0',
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date|after_or_equal:valid_from',
            'is_dialog' => 'nullable|boolean',
        ], [
            'name.required' => 'Nama aturan poin wajib diisi.',
            'name.string' => 'Nama aturan poin harus berupa teks.',
            'name.max' => 'Nama aturan poin maksimal 50 karakter.',
            'description.string' => 'Deskripsi harus berupa teks.',
            'description.max' => 'Deskripsi maksimal 255 karakter.',
            'type.required' => 'Tipe aturan poin wajib diisi.',
            'type.string' => 'Tipe harus berupa teks.',
            'type.max' => 'Tipe maksimal 50 karakter.',
            'min_spend.numeric' => 'Pengeluaran minimum harus berupa angka.',
            'min_spend.min' => 'Pengeluaran minimum tidak boleh negatif.',
            'points_earned.required' => 'Poin yang diperoleh wajib diisi.',
            'points_earned.integer' => 'Poin yang diperoleh harus berupa bilangan bulat.',
            'points_earned.min' => 'Poin yang diperoleh tidak boleh negatif.',
            'conversion_rate.numeric' => 'Rasio konversi harus berupa angka.',
            'conversion_rate.min' => 'Rasio konversi tidak boleh negatif.',
            'valid_from.date' => 'Tanggal mulai tidak valid.',
            'valid_until.date' => 'Tanggal berakhir tidak valid.',
            'valid_until.after_or_equal' => 'Tanggal berakhir harus setelah atau sama dengan tanggal mulai.',
        ]);

        try {
            if (PointRuleRepository::isPointRuleExists($validated['name'], $this->storeId)) {
                return back()->withErrors(['name' => 'Nama aturan poin sudah digunakan.'])->withInput();
            }

            PointRuleRepository::createPointRule([
                'store_id' => $this->storeId,
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
            ]);

            if ($request->boolean('is_dialog')) {
                return redirect()->back()->with('success', 'Aturan poin berhasil ditambahkan.')->with('closeDialog', true);
            }

            return redirect()->route('my-store.point-rule.index')->with('success', 'Aturan poin berhasil ditambahkan.');
        } catch (Exception $e) {
            return back()->withErrors(['error' => 'Terjadi kesalahan saat menambahkan aturan poin.'])->withInput();
        }
    }

    public function edit(PointRule $pointRule)
    {
        return Inertia::render('MyStore/PointRule/EditPointRule', [
            'pointRule' => $pointRule,
        ]);
    }

    public function update(Request $request, PointRule $pointRule)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'description' => 'nullable|string|max:255',
            'type' => 'required|string|max:50',
            'min_spend' => 'nullable|numeric|min:0',
            'points_earned' => 'required|integer|min:0',
            'conversion_rate' => 'nullable|numeric|min:0',
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date|after_or_equal:valid_from',
        ], [
            'name.required' => 'Nama aturan poin wajib diisi.',
            'name.string' => 'Nama aturan poin harus berupa teks.',
            'name.max' => 'Nama aturan poin maksimal 50 karakter.',
            'description.string' => 'Deskripsi harus berupa teks.',
            'description.max' => 'Deskripsi maksimal 255 karakter.',
            'type.required' => 'Tipe aturan poin wajib diisi.',
            'type.string' => 'Tipe harus berupa teks.',
            'type.max' => 'Tipe maksimal 50 karakter.',
            'min_spend.numeric' => 'Pengeluaran minimum harus berupa angka.',
            'min_spend.min' => 'Pengeluaran minimum tidak boleh negatif.',
            'points_earned.required' => 'Poin yang diperoleh wajib diisi.',
            'points_earned.integer' => 'Poin yang diperoleh harus berupa bilangan bulat.',
            'points_earned.min' => 'Poin yang diperoleh tidak boleh negatif.',
            'conversion_rate.numeric' => 'Rasio konversi harus berupa angka.',
            'conversion_rate.min' => 'Rasio konversi tidak boleh negatif.',
            'valid_from.date' => 'Tanggal mulai tidak valid.',
            'valid_until.date' => 'Tanggal berakhir tidak valid.',
            'valid_until.after_or_equal' => 'Tanggal berakhir harus setelah atau sama dengan tanggal mulai.',
        ]);

        try {
            if (PointRuleRepository::isPointRuleExists($validated['name'], $this->storeId, $pointRule->id)) {
                return back()->withErrors(['name' => 'Nama aturan poin sudah digunakan.'])->withInput();
            }

            PointRuleRepository::updatePointRule($pointRule, [
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'type' => $validated['type'],
                'min_spend' => $validated['min_spend'] ?? null,
                'points_earned' => $validated['points_earned'],
                'conversion_rate' => $validated['conversion_rate'] ?? null,
                'valid_from' => $validated['valid_from'] ?? null,
                'valid_until' => $validated['valid_until'] ?? null,
            ]);

            return redirect()->route('my-store.point-rule.index')->with('success', 'Aturan poin berhasil diperbarui.');
        } catch (Exception $e) {
            return back()->withErrors(['error' => 'Terjadi kesalahan saat memperbarui aturan poin.'])->withInput();
        }
    }

    public function destroy(PointRule $pointRule)
    {
        try {
            PointRuleRepository::deletePointRule($pointRule);
            return redirect()->route('my-store.point-rule.index')->with('success', 'Aturan poin berhasil dihapus.');
        } catch (Exception $e) {
            return back()->withErrors(['error' => 'Terjadi kesalahan saat menghapus aturan poin.']);
        }
    }
}
