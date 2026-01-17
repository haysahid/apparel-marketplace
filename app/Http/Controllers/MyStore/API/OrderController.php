<?php

namespace App\Http\Controllers\MyStore\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    public function changeStatus(Request $request)
    {
        $validated = $request->validate([
            'invoice_id' => 'required|integer|exists:invoices,id',
            'status' => 'required|string|in:pending,paid,processing,completed,cancelled',
        ]);

        try {
            DB::beginTransaction();

            $invoice = Invoice::findOrFail($validated['invoice_id']);
            $invoice->status = $validated['status'];
            $invoice->save();

            DB::commit();

            return ResponseFormatter::success(
                $invoice,
                'Status transaksi berhasil diubah',
                200
            );
        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Change status failed: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'transaction_id' => $validated['transaction_id'],
            ]);

            return ResponseFormatter::error(
                'Gagal mengubah status transaksi: ' . $e->getMessage(),
                500
            );
        }
    }

    public function setDelivering(Request $request)
    {
        $validated = $request->validate([
            'invoice_id' => 'required|integer|exists:invoices,id',
            'tracking_number' => 'required|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            $invoice = Invoice::findOrFail($validated['invoice_id']);
            $invoice->status = 'processing';
            $invoice->tracking_number = $validated['tracking_number'];
            $invoice->save();

            DB::commit();

            return ResponseFormatter::success(
                $invoice,
                'Status pengiriman berhasil diubah',
                200
            );
        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Set delivering failed: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'invoice_id' => $validated['invoice_id'],
            ]);

            return ResponseFormatter::error(
                'Gagal mengubah status pengiriman: ' . $e->getMessage(),
                500
            );
        }
    }
}
