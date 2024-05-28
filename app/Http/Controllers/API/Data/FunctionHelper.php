<?php

namespace App\Http\Controllers\API\Data;

use App\Models\Produk;
use App\Models\Notifikasi;
use App\Models\User;
use App\Models\Transaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FunctionHelper
{
    /**
     * Upload image to cloudinary
     *
     * @return string
     */
    public function uploadImage($image, string $imageName)
    {
        $response = cloudinary()->upload($image->getRealPath(), [
            'upload_preset' => 'atma-bakery',
            'filename_override' => $imageName,
            'public_id' => $imageName,
        ]);

        $uploadedFileUrl = $response->getSecurePath();

        return $uploadedFileUrl;
    }

    /**
     * Delete image from cloudinary
     *
     * @return string
     */
    public function deleteImage(string $imageName)
    {
        $publicId = 'atma-bakery/' . $imageName;
        $response = cloudinary()->destroy($publicId);

        return $response;
    }

    /**
     * Creating array of data to be updated
     *
     * @return array
     */
    public function updateDataMaker(array $attribute, Request $request)
    {
        $updateData = [];

        foreach ($attribute as $attr) {
            if ($request->has($attr)) {
                $updateData[$attr] = $request->$attr;
            }
        }

        return $updateData;
    }

    public function countStok(string $id_produk, string $po_date)
    {
        $produk = Produk::find($id_produk);

        $directTransaksiSum = Transaksi::whereHas('detail_transaksi', function ($query) use ($id_produk) {
            $query->where('id_produk', $id_produk)
                ->where('status', null);
        })->whereDate('tanggal_ambil', $po_date)
            ->join('detail_transaksi', 'transaksi.no_nota', '=', 'detail_transaksi.no_nota')
            ->sum('detail_transaksi.jumlah');

        $hampersTransaksiSum = Transaksi::whereHas('detail_transaksi', function ($query) use ($id_produk) {
            $query->whereHas('hampers.detail_hampers', function ($subQuery) use ($id_produk) {
                $subQuery->where('id_produk', $id_produk);
            })->where('status', null);
        })->whereDate('tanggal_ambil', $po_date)
            ->join('detail_transaksi as dt', 'transaksi.no_nota', '=', 'dt.no_nota')
            ->join('hampers', 'hampers.id_hampers', '=', 'dt.id_hampers')
            ->join('detail_hampers as dh', 'hampers.id_hampers', '=', 'dh.id_hampers')
            ->where('dh.id_produk', $id_produk)
            ->sum(DB::raw('dt.jumlah * dh.jumlah'));

        $totalJumlah = $directTransaksiSum + $hampersTransaksiSum;

        $limitOrStok = ($produk->status === 'PO') ? $produk->limit : $produk->stok;
        $remaining = $limitOrStok - (int) $totalJumlah;

        return $remaining;
    }

    public function bulkSend($title, $body, $token)
    {
        
        try {
            $url = 'https://fcm.googleapis.com/fcm/send';
            $dataArr = array('click_action' => 'FLUTTER_NOTIFICATION_CLICK', 'id' => '1', 'status' => "done");
            $notification = array('title' => $title, 'body' => $body, 'sound' => 'default', 'badge' => '1');
            $arrayToSend = array('to' => $token, 'notification' => $notification, 'data' => $dataArr, 'priority' => 'high');
            $fields = json_encode($arrayToSend);
            $headers = array(
                'Authorization: key=' . "AAAAOjTG70s:APA91bHLhNpb9dUOLTtvo_yaJItJ_REQBrIgddQlO2oYhq2yfMS--nfNt5MM9f4TnW_1f-oO80dZO5UAJgk37l6sesw64vINczFh0PfQn_iRMOC1Pid7IrE4XeeYd8wD00FOLxDM5jZg",
                'Content-Type: application/json'
            );

            $user = User::where('fcm_token', $token)->first();
            
            if (!$user) {
                return [
                    'status' => 'error',
                    'message' => 'User not found',
                    'notification' => $notification,
                    'code' => 404,
                ];
            }

            DB::beginTransaction();
        try {
            Notifikasi::create([
                'id_user' => $user->id_user,
                'title' => $title,
                'body' => $body,
            ]);
    
            DB::commit();
    
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'status' => 'error',
                'message' => 'Failed to create notification',
                'error' => $e->getMessage(),
                'notification' => $notification,
                'code' => 500,
            ];
        }

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
            $result = curl_exec($ch);
            curl_close($ch);

            return [
                'status' => 'success',
                'message' => 'Notification sent successfully',
                'notification' => $notification,
                'result' => json_decode($result),
            ];
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}
