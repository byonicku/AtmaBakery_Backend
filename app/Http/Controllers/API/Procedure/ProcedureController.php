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
                'id_kategori' => $item->id_kategori,
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
                'id_kategori' => $item->id_kategori,
            ];
        }

        return response()->json([
            'data' => $result,
        ], 200);
    }

    public function bulksend() {
        $url = 'https://fcm.googleapis.com/fcm/send';
        $dataArr = array('click_action' => 'FLUTTER_NOTIFICATION_CLICK', 'id' => '1', 'status' => "done");
        $notification = array('title' => 'WOI BACA', 'body' => 'WOI BACA INI', 'sound' => 'default', 'badge' => '1');
        
        $targetToken = "ed8uife_Q_mOLrLAqmTNSz:APA91bFwFUfRpOLGIniRnQ1xgnZzrw6Dlgb18DcAgj0-XzwLH6_a0oBWoXHonCqMsbhkMyfG3L15erzYdBi6yVFfmIgexvLVIOlD4QHCjnGuze47DdJsdQJuKbg7UuklPlOqPCJwT6DY";
        
        $arrayToSend = array('to' => $targetToken, 'notification' => $notification, 'data' => $dataArr, 'priority' => 'high');
        $fields = json_encode($arrayToSend);
        $headers = array(
            'Authorization: key=' . "AAAAOjTG70s:APA91bHLhNpb9dUOLTtvo_yaJItJ_REQBrIgddQlO2oYhq2yfMS--nfNt5MM9f4TnW_1f-oO80dZO5UAJgk37l6sesw64vINczFh0PfQn_iRMOC1Pid7IrE4XeeYd8wD00FOLxDM5jZg",
            'Content-Type: application/json'
        );
    
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        $result = curl_exec($ch);
        curl_close($ch);
        
        return response()->json([
            'data' => $notification,
        ], 200);
    }
    
    
}