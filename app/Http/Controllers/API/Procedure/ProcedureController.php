<?php

namespace App\Http\Controllers\API\Procedure;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ProcedureController extends Controller
{
    public function getNotaPemesanan(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'no_nota' => 'required|exists:transaksi,no_nota',
        ]);

        if ($validate->fails()) {
            return response()->json([
                'message' => $validate->errors(),
            ], 400);
        }

        try {
            $nota = DB::select("CALL p3l.get_nota_pemesanan(?);", [$request['no_nota']]);
            $barang = DB::select("CALL p3l.get_produk_for_nota(?);", [$request['no_nota']]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 500);
        }

        $result = [
            'id_nota' => $nota[0]->data_pemesanan,
            'nama' => $nota[4]->data_pemesanan,
            'email' => $nota[5]->data_pemesanan,
            'lokasi' => $nota[6]->data_pemesanan,
            'tanggal_pesan' => $nota[1]->data_pemesanan,
            'tanggal_lunas' => $nota[2]->data_pemesanan,
            'tanggal_ambil' => $nota[3]->data_pemesanan,
            'tipe_delivery' => $nota[7]->data_pemesanan,
            'penggunaan_poin' => $nota[8]->data_pemesanan,
            'total_harga' => $nota[9]->data_pemesanan,
            'radius' => $nota[10]->data_pemesanan,
            'ongkir' => $nota[11]->data_pemesanan,
            'pendapatan_poin' => $nota[12]->data_pemesanan,
            'barang' => []
        ];

        foreach ($barang as $item) {
            $result['barang'][] = [
                'nama' => $item->nama,
                'ukuran' => $item->ukuran,
                'total_harga' => $item->total_harga,
            ];
        }

        return response()->json([
            'data' => $result,
        ], 200);
    }
}
