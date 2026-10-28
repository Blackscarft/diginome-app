<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BankMutation;
use Illuminate\Http\Request;

/**
 * @group Manajemen Mutasi Bank
 * 
 * Endpoint untuk mengelola dan melihat data mutasi bank serta proses rekonsiliasi.
 */
class BankMutationController extends Controller
{

    /**
     * Ambil Daftar Mutasi Bank
     * 
     * Menampilkan data mutasi bank yang belum dicocokkan (is_matched = false) 
     * dengan opsi filter berdasarkan kode bank dan rentang tanggal transaksi.
     * 
     * @queryParam is_matched string Status penyesuaian (true atau false). Example: false
     * @queryParam bank_code string Kode bank (contoh: 0001, 0002). Example: 0002
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
     *       "transaction_date": "2026-08-01T00:00:00.000000Z",
     *       "transaction_time": null,
     *       "description": "test",
     *       "reference": "trx-12345",
     *       "is_matched": false,
     *       "created_at": "2026-08-31T07:41:33.000000Z",
     *       "updated_at": "2026-08-31T07:41:33.000000Z",
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
     *       "transaction_date": "2026-08-02T00:00:00.000000Z",
     *       "transaction_time": null,
     *       "description": "test1",
     *       "reference": "trx-12346",
     *       "is_matched": false,
     *       "created_at": "2026-08-31T07:41:33.000000Z",
     *       "updated_at": "2026-08-31T07:41:33.000000Z",
     *       "deleted_at": null
     *       }
     *   ]
     * }
     */
    public function index(Request $request)
    {
        $query = BankMutation::where('is_matched', false);

        // Filter berdasarkan status penyesuaian (matching) jika dikirim oleh client
        if ($request->filled('is_matched')) {
            $isMatched = filter_var($request->is_matched, FILTER_VALIDATE_BOOLEAN);
            $query->where('is_matched', $isMatched);
        }
        
        // Filter berdasarkan bank_code (jika dikirim oleh client)
        if ($request->filled('bank_code')) {
            $query->where('bank_code', $request->bank_code);
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
        $mutations = $query->latest()->get();
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
                    'is_matched' => $isMatched ?? null,
                    'bank_code'  => $request->bank_code ?? null,
                    'start_date' => $request->start_date ?? null,
                    'end_date'   => $request->end_date ?? null,
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
     *      "transaction_date": "2026-08-01T00:00:00.000000Z",
     *      "transaction_time": null,
     *      "description": "test",
     *      "reference": "trx-12345",
     *      "is_matched": false,
     *      "created_at": "2026-08-31T07:41:33.000000Z",
     *      "updated_at": "2026-08-31T07:41:33.000000Z",
     *      "deleted_at": null
     *  }
     * }
     */
    public function show(int $id)
    {
        $mutation = BankMutation::find($id);

        if (!$mutation) {
            return response()->json([
                'success' => false,
                'message' => 'Data mutasi bank tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Data mutasi bank berhasil diambil.',
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
        $count = BankMutation::where('is_matched', false)->count();

        return response()->json([
            'success' => true,
            'message' => 'Jumlah data mutasi bank yang belum dicocokkan berhasil diambil.',
            'data'    => [
                'total_unmatched' => $count
            ]
        ]);
    }
}
