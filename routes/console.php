<?php

use App\Models\Cart;
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
