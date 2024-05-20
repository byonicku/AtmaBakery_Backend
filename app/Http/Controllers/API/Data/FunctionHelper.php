<?php

namespace App\Http\Controllers\API\Data;

use App\Models\Produk;
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
            $query->where('id_produk', $id_produk);
        })->whereDate('tanggal_ambil', $po_date)
            ->join('detail_transaksi', 'transaksi.no_nota', '=', 'detail_transaksi.no_nota')
            ->sum('detail_transaksi.jumlah');

        $hampersTransaksiSum = Transaksi::whereHas('detail_transaksi', function ($query) use ($id_produk) {
            $query->whereHas('hampers.detail_hampers', function ($subQuery) use ($id_produk) {
                $subQuery->where('id_produk', $id_produk);
            });
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
}
