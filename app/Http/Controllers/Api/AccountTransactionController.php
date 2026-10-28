<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AccountTransaction;
use Illuminate\Http\Request;

/**
 * @group Manajemen Transaksi Akun
 * 
 * Endpoint untuk mengelola dan melihat data transaksi akun serta proses rekonsiliasi.
 */
class AccountTransactionController extends Controller
{

    /**
     * Ambil Daftar Mutasi Bank
     * 
     * Menampilkan data mutasi bank yang belum dicocokkan (is_matched = false) 
     * dengan opsi filter berdasarkan kode bank dan rentang tanggal transaksi.
     * 
     * @queryParam is_matched string Status penyesuaian (true atau false). Example: false
     * @queryParam bank_code string Kode bank (contoh: 0001, 0002). Example: 0002
     * @queryParam account_number string Nomor rekening bank. Example: 1234567890
     * @queryParam start_date string Tanggal awal transaksi dengan format YYYY-MM-DD. Example: 2026-01-01
     * @queryParam end_date string Tanggal akhir transaksi dengan format YYYY-MM-DD. Example: 2026-01-02
     * 
     * @response 200 {
     *   "status": true,
     *   "message": "Data mutasi bank berhasil diambil.",
     *   "filters": {
     *       "is_matched": false,
     *       "bank_code": null,
     *       "start_date": "2026-08-01",
     *       "end_date": "2026-08-02"
     *   },
     *   "total_records": 2,
     *   "data": [
     *       {
     *       "id": 1,
     *       "journal_id": null,
     *       "bank_account_id": null,
     *       "source_id": null,
     *       "bank_code": "1",
     *       "bank_name": "a",
     *       "debit": "0.00",
     *       "credit": "100.00",
     *       "transaction_date": "2026-08-01",
     *       "transaction_time": null,
     *       "description": "test",
     *       "reference": "trx-12345",
     *       "is_matched": false,
     *       "created_at": "2026-08-31",
     *       "updated_at": "2026-08-31",
     *       "deleted_at": null
     *       },
     *       {
     *       "id": 2,
     *       "journal_id": null,
     *       "bank_account_id": null,
     *       "source_id": null,
     *       "bank_code": "2",
     *       "bank_name": "b",
     *       "debit": "100.00",
     *       "credit": "0.00",
     *       "transaction_date": "2026-08-02",
     *       "transaction_time": null,
     *       "description": "test1",
     *       "reference": "trx-12346",
     *       "is_matched": false,
     *       "created_at": "2026-08-31",
     *       "updated_at": "2026-08-31",
     *       "deleted_at": null
     *       }
     *   ]
     * }
     */
    public function index(Request $request)
    {
        $query = AccountTransaction::where('is_matched', false);

        // Filter berdasarkan status penyesuaian (matching) jika dikirim oleh client
        if ($request->filled('is_matched')) {
            $isMatched = filter_var($request->is_matched, FILTER_VALIDATE_BOOLEAN);
            $query->where('is_matched', $isMatched);
        }
        
        // Filter berdasarkan bank_code (jika dikirim oleh client)
        if ($request->filled('bank_code')) {
            $query->where('bank_code', $request->bank_code);
        }

        // Filter berdasarkan account_number (jika dikirim oleh client)
        if ($request->filled('account_number')) {
            $query->where('account_number', $request->account_number);
        }

        // Filter berdasarkan date range (transaction_date)
        // Format yang diharapkan dari client umumnya 'YYYY-MM-DD'
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('transaction_date', [
                $request->start_date . ' 00:00:00', 
                $request->end_date . ' 23:59:59'
            ]);
        } elseif ($request->filled('start_date')) {
            // Jika hanya start_date yang diisi
            $query->where('transaction_date', '>=', $request->start_date . ' 00:00:00');
        } elseif ($request->filled('end_date')) {
            // Jika hanya end_date yang diisi
            $query->where('transaction_date', '<=', $request->end_date . ' 23:59:59');
        }

        // Sort berdasarkan tanggal transaksi (terbaru)
        $mutations = $query->latest()->get()->map(function ($mutation) {
            $data = $mutation->toArray();
            $data['transaction_date'] = $mutation->transaction_date?->format('Y-m-d');
            $data['created_at'] = $mutation->created_at?->format('Y-m-d');
            $data['updated_at'] = $mutation->updated_at?->format('Y-m-d');

            return $data;
        });
        // $mutations = $query->latest();
        
        // Paginate hasil query
        // $mutations = $query->paginate(10)->paginate(10);

        return response()->json(
            [
                'status' => true,
                'message' => $mutations->isEmpty() 
                        ? 'Data mutasi bank tidak ditemukan.' 
                        : 'Data mutasi bank berhasil diambil.',
                'filters' => [
                    'is_matched'     => $isMatched ?? null,
                    'bank_code'      => $request->bank_code ?? null,
                    'account_number' => $request->account_number ?? null,
                    'start_date'     => $request->start_date ?? null,
                    'end_date'       => $request->end_date ?? null,
                ],
                'total_records' => $mutations->count(),
                'data' => $mutations
            ]
        );
    }


    /** 
     * Ambil Detail Mutasi Bank
     * 
     * Mengambil detail data mutasi bank berdasarkan ID.
     * 
     * @urlParam id integer required ID dari mutasi bank. Example: 1
     * 
     * @response 200 {
     *  "success": true,
     *  "message": "Data mutasi bank berhasil diambil.",
     *  "data": {
     *      "id": 1,
     *      "journal_id": null,
     *      "bank_account_id": null,
     *      "source_id": null,
     *      "bank_code": "1",
     *      "bank_name": "a",
     *      "debit": "0.00",
     *      "credit": "100.00",
     *      "transaction_date": "2026-08-01",
     *      "transaction_time": null,
     *      "description": "test",
     *      "reference": "trx-12345",
     *      "is_matched": false,
     *      "created_at": "2026-08-31",
     *      "updated_at": "2026-08-31",
     *      "deleted_at": null
     *  }
     * }
     */
    public function show(int $id)
    {
        $mutation = AccountTransaction::find($id);

        if (!$mutation) {
            return response()->json([
                'success' => false,
                'message' => 'Data mutasi bank tidak ditemukan.',
            ], 404);
        }

        $data = $mutation->toArray();
        $data['transaction_date'] = $mutation->transaction_date?->format('Y-m-d');
        $data['created_at'] = $mutation->created_at?->format('Y-m-d');
        $data['updated_at'] = $mutation->updated_at?->format('Y-m-d');

        return response()->json([
            'success' => true,
            'message' => 'Data mutasi bank berhasil diambil.',
            'data'    => $data
        ]);
    }

    /**
     * Batch Update Mutasi Bank (is_matched = true)
     * 
     * Mengupdate status is_matched menjadi true untuk beberapa ID sekaligus.
     * 
     * @bodyParam ids array required Daftar ID yang akan diupdate. Example: [1, 2, 3, 4, 5]
     * 
     * @response 200 {
     *  "success": true,
     *  "message": "Proses batch update selesai.",
     *  "data": {
     *      "success_count": 3,
     *      "failed_count": 2,
     *      "failed_ids": [4, 5]
     *  }
     * }
     */
    public function batchUpdateStatus(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:account_transactions,id',
        ]);

        $ids = $request->ids;
        $successIds = [];
        $failedIds = [];

        foreach ($ids as $id) {
            try {
                $mutation = AccountTransaction::find($id);
                if ($mutation) {
                    $mutation->update(['is_matched' => true]);
                    $successIds[] = $id;
                } else {
                    $failedIds[] = $id;
                }
            } catch (\Exception $e) {
                $failedIds[] = $id;
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Proses batch update selesai.',
            'data' => [
                'success_count' => count($successIds),
                'failed_count' => count($failedIds),
                'failed_ids' => $failedIds
            ]
        ]);
    }

    /**
     * Update Mutasi Bank
     * 
     * Mengupdate data mutasi bank berdasarkan ID.
     * 
     * @urlParam id integer required ID dari mutasi bank. Example: 1
     * @bodyParam debit decimal Jumlah debit. Example: 0.00
     * @bodyParam credit decimal Jumlah kredit. Example: 100.00
     * @bodyParam transaction_date date Tanggal transaksi. Example: 2026-08-01
     * @bodyParam transaction_time string Waktu transaksi. Example: 10:00:00
     * @bodyParam description string Deskripsi transaksi. Example: Pembayaran layanan
     * @bodyParam reference string Referensi transaksi. Example: trx-12345
     * @bodyParam fee_amount decimal Biaya transaksi. Example: 0.00
     * @bodyParam is_matched boolean Status penyesuaian. Example: true
     * 
     * @response 200 {
     *  "success": true,
     *  "message": "Data mutasi bank berhasil diperbarui.",
     *  "data": {
     *      "id": 1,
     *      "description": "Pembayaran layanan",
     *      "is_matched": true,
     *      "updated_at": "2026-10-28"
     *  }
     * }
     */
    public function update(Request $request, int $id)
    {
        $mutation = AccountTransaction::find($id);

        if (!$mutation) {
            return response()->json([
                'success' => false,
                'message' => 'Data mutasi bank tidak ditemukan.',
            ], 404);
        }

        $validated = $request->validate([
            'debit'            => 'nullable|numeric|required_without:credit',
            'credit'           => 'nullable|numeric|required_without:debit',
            'transaction_date' => 'nullable|date_format:Y-m-d',
            'transaction_time' => 'nullable|date_format:H:i:s',
            'description'      => 'nullable|string|max:255',
            'reference'        => 'nullable|string|max:255',
            'fee_amount'       => 'nullable|numeric',
            'is_matched'       => 'nullable|boolean',
        ]);

        $mutation->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Data mutasi bank berhasil diperbarui.',
            'data'    => $mutation
        ]);
    }

    /**
     * Hitung Mutasi Belum Dicocokkan
     * 
     * Mengembalikan jumlah total data mutasi bank yang belum dicocokkan (is_matched = false).
     * 
     * @response 200 {
     *   "success": true,
     *   "message": "Jumlah data mutasi bank yang belum dicocokkan berhasil diambil.",
     *   "data": {
     *       "total_unmatched": 3
     *   }
     * }
     */
    public function getCountUnmatchedRecords()
    {
        $count = AccountTransaction::where('is_matched', false)->count();

        return response()->json([
            'success' => true,
            'message' => 'Jumlah data mutasi bank yang belum dicocokkan berhasil diambil.',
            'data'    => [
                'total_unmatched' => $count
            ]
        ]);
    }
}
