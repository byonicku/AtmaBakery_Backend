<?php

namespace App\Http\Controllers\API\Data;

use App\Http\Controllers\Controller;
use App\Models\Transaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransaksiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    public function indexHistorySelf()
    {
        $data = Auth::user();

        $transaksi = Transaksi::with('detail_transaksi')->where('id_user', '=', $data->id_user)
            ->get();

        if (count($transaksi) == 0) {
            return response()->json([
                'message' => 'Data is empty',
            ], 404);
        }

        return response()->json([
            'message' => 'Data successfully retrieved',
            'data' => $transaksi,
        ], 200);
    }

    public function indexHistoryPaginateSelf()
    {
        $data = Auth::user();

        $transaksi = Transaksi::with('detail_transaksi')->where('id_user', '=', $data->id_user)
            ->paginate(10);

        if (count($transaksi) == 0) {
            return response()->json([
                'message' => 'Data is empty',
            ], 404);
        }

        return response()->json([
            'message' => 'Data successfully retrieved',
            'data' => $transaksi,
        ], 200);
    }

    public function indexHistoryPaginate(string $id_user)
    {
        $transaksi = Transaksi::with('detail_transaksi')->where('id_user', '=', $id_user)
            ->paginate(10);

        if (count($transaksi) == 0) {
            return response()->json([
                'message' => 'Data is empty',
            ], 404);
        }

        return response()->json([
            'message' => 'Data successfully retrieved',
            'data' => $transaksi,
        ], 200);
    }

    public function search(Request $request, string $id_user)
    {
        $data = $request->data;

        if ($data == null) {
            return response()->json([
                'message' => 'Data is empty',
            ], 404);
        }

        $transaksi = Transaksi::with('user', 'alamat')
            ->where('id_user', '=', $id_user)
            ->whereAny([
                'no_nota',
                'id_user',
                'id_alamat',
                'tanggal_pesan',
                'tanggal_lunas',
                'tanggal_ambil',
                'penggunaan_poin',
                'total',
                'radius',
                'tip',
                'tipe_delivery',
                'status',
            ], 'LIKE', '%' . $data . '%')
            ->get();

        if (count($transaksi) == 0) {
            return response()->json([
                'message' => 'Data not found',
            ], 404);
        }

        return response()->json([
            'message' => 'Data successfully retrieved',
            'data' => $transaksi,
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}