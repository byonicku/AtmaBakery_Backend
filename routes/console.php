<?php

use App\Models\Cart;
use App\Models\DetailHampers;
use App\Models\DetailTransaksi;
use App\Models\Produk;
use App\Models\Transaksi;
use App\Models\User;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Models\Presensi;
use App\Models\Karyawan;

Artisan::command('add-presensi', function () {
    try {
        $karyawan = Karyawan::all();

        $presensi = Presensi::where('tanggal', '=', date('Y-m-d'))->get();

        if (count($presensi) > 0) {
            $this->info('Presensi hari ini sudah ditambahkan');
            return;
        }

        for ($i = 0; $i < count($karyawan); $i++) {
            $presensi = new Presensi();
            $presensi->id_karyawan = $karyawan[$i]->id_karyawan;
            $presensi->tanggal = date('Y-m-d');
            $presensi->status = 1;
            $presensi->save();
            $this->info('Presensi karyawan ' . $karyawan[$i]->nama . ' berhasil ditambahkan');
        }
    } catch (\Exception $e) {
        $this->error('Error: ' . $e->getMessage());
    }
})->purpose('Menambahkan presensi karyawan');

Artisan::command('remove-cart', function () {
    try {
        $date = date('Y-m-d', strtotime('+1 day'));
        $cart = Cart::where('po_date', '=', $date)->get();

        if (count($cart) == 0) {
            $this->info('Cart hari ini kosong');
            return;
        }

        for ($i = 0; $i < count($cart); $i++) {
            $cart[$i]->delete();
            $this->info('Cart ' . $cart[$i]->id_cart . ' berhasil dihapus');
        }

        $this->info('Cart berhasil dihapus');
    } catch (\Exception $e) {
        $this->error('Error: ' . $e->getMessage());
    }
})->purpose('Menghapus cart h-1 PO');

Artisan::command('remove-transaksi', function () {
    try {
        $date = date('Y-m-d', strtotime('+1 day'));
        $transaksi = Transaksi::
            where(function ($query) use ($date) {
                $query->where('tanggal_ambil', '=', $date)
                    ->orWhere('tanggal_ambil', '=', null);
            })
            ->where('status', '=', 'Menunggu Pembayaran')->get();

        if (count($transaksi) == 0) {
            $this->info('Transaksi hari ini kosong');
            return;
        }

        for ($i = 0; $i < count($transaksi); $i++) {
            $transaksi[$i]->status = 'Ditolak';
            $transaksi[$i]->tanggal_ambil = null;
            $transaksi[$i]->save();
            $detailTransaksi = DetailTransaksi::where('no_nota', $transaksi[$i]->no_nota)->get();

            foreach ($detailTransaksi as $detail) {
                if ($detail->id_produk) {
                    $produk = Produk::find($detail->id_produk);
                    if ($produk->status === 'READY') {
                        $produk->stok += $detail->jumlah;
                        $produk->save();
                    }
                } else if ($detail->id_hampers) {
                    $dt = DetailHampers::where('id_hampers', $detail->id_hampers)->get();
                    foreach ($dt as $item) {
                        if ($item->id_produk === null) {
                            continue;
                        }

                        $produk = Produk::find($item->id_produk);
                        if ($produk->status === 'READY') {
                            $produk->stok += $detail->jumlah * $item->jumlah;
                            $produk->save();
                        }
                    }
                }
            }

            $user = User::find($transaksi->id_user);

            if ($transaksi->penggunaan_poin > 0) {
                $user->poin += $transaksi->poin_sebelum_penambahan;
            }

            $user->save();

            $this->info('Transaksi ' . $transaksi[$i]->no_nota . ' berhasil ditolak');
        }

        $this->info('Transaksi berhasil dihapus');
    } catch (\Exception $e) {
        $this->error('Error: ' . $e->getMessage());
    }
});
