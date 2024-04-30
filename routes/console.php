<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Models\Presensi;
use App\Models\Karyawan;

Artisan::command('add-presensi', function () {
    $karyawan = Karyawan::all();

    for ($i = 0; $i < count($karyawan); $i++) {
        $presensi = new Presensi();
        $presensi->id_karyawan = $karyawan[$i]->id_karyawan;
        $presensi->tanggal = date('Y-m-d');
        $presensi->status = 1;
        $presensi->save();
        $this->info('Presensi karyawan ' . $karyawan[$i]->nama . ' berhasil ditambahkan');
    }

})->purpose('Menambahkan presensi karyawan');