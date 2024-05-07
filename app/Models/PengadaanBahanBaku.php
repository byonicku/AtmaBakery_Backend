<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengadaanBahanBaku extends Model
{
    protected $table = 'pengadaan_bahanbaku';

    protected $primaryKey = 'id_pengadaan';

    public $timestamps = false;

    protected $fillable = [
        'id_pengadaan',
        'id_bahan_baku',
        'stok',
        'tanggal_pembelian',
        'harga',
    ];

    public function bahan_baku()
    {
        return $this->belongsTo(BahanBaku::class, 'id_bahan_baku', 'id_bahan_baku')->withTrashed();
    }
}