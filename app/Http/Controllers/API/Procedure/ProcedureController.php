<?php

namespace App\Http\Controllers\API\Procedure;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
            'no_nota' => $nota[0]->data_pemesanan,
            'nama' => $nota[4]->data_pemesanan,
            'email' => $nota[5]->data_pemesanan,
            'no_telp' => $nota[16]->data_pemesanan,
            'lokasi' => $nota[6]->data_pemesanan,
            'keterangan' => $nota[7]->data_pemesanan,
            'tanggal_pesan' => $nota[1]->data_pemesanan,
            'tanggal_lunas' => $nota[2]->data_pemesanan,
            'tanggal_ambil' => $nota[3]->data_pemesanan,
            'tipe_delivery' => $nota[8]->data_pemesanan,
            'penggunaan_poin' => (int) $nota[9]->data_pemesanan,
            'total' => (double) $nota[10]->data_pemesanan,
            'radius' => (int) $nota[11]->data_pemesanan,
            'ongkir' => (int) $nota[12]->data_pemesanan,
            'status' => $nota[15]->data_pemesanan,
            'penambahan_poin' => (int) $nota[13]->data_pemesanan,
            'poin_user_setelah_penambahan' => (int) $nota[14]->data_pemesanan,
            'produk' => []
        ];

        foreach ($barang as $item) {
            $result['produk'][] = [
                'nama_produk' => $item->nama,
                'ukuran' => $item->ukuran,
                'harga_saat_beli' => $item->harga_saat_beli,
                'jumlah' => $item->jumlah,
                'subtotal' => $item->total_harga,
            ];
        }

        return response()->json([
            'data' => $result,
        ], 200);
    }

    public function getNotaPemesananSelf(Request $request)
    {
        $user = Auth::user();

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

            if ($user->id_user != $nota[17]->data_pemesanan) {
                return response()->json([
                    'message' => 'Unauthorized Bukan Pemesan Anda',
                ], 401);
            }

            $barang = DB::select("CALL p3l.get_produk_for_nota(?);", [$request['no_nota']]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 500);
        }

        $result = [
            'no_nota' => $nota[0]->data_pemesanan,
            'nama' => $nota[4]->data_pemesanan,
            'email' => $nota[5]->data_pemesanan,
            'no_telp' => $nota[16]->data_pemesanan,
            'lokasi' => $nota[6]->data_pemesanan,
            'keterangan' => $nota[7]->data_pemesanan,
            'tanggal_pesan' => $nota[1]->data_pemesanan,
            'tanggal_lunas' => $nota[2]->data_pemesanan,
            'tanggal_ambil' => $nota[3]->data_pemesanan,
            'tipe_delivery' => $nota[8]->data_pemesanan,
            'penggunaan_poin' => (int) $nota[9]->data_pemesanan,
            'total' => (double) $nota[10]->data_pemesanan,
            'radius' => (int) $nota[11]->data_pemesanan,
            'ongkir' => (int) $nota[12]->data_pemesanan,
            'status' => $nota[15]->data_pemesanan,
            'penambahan_poin' => (int) $nota[13]->data_pemesanan,
            'poin_user_setelah_penambahan' => (int) $nota[14]->data_pemesanan,
            'produk' => []
        ];

        foreach ($barang as $item) {
            $result['produk'][] = [
                'nama_produk' => $item->nama,
                'ukuran' => $item->ukuran,
                'harga_saat_beli' => $item->harga_saat_beli,
                'jumlah' => $item->jumlah,
                'subtotal' => $item->total_harga,
            ];
        }

        return response()->json([
            'data' => $result,
        ], 200);
    }
}