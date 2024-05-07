<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BahanBaku extends Model
{
    use SoftDeletes;
    protected $table = 'bahan_baku';
    protected $primaryKey = 'id_bahan_baku';
    public $timestamps = false;

    protected $fillable = [
        'nama_bahan_baku',
        'stok',
        'satuan',
    ];

    public function resep()
    {
        return $this->hasMany(Resep::class, 'id_bahan_baku', 'id_bahan_baku');
    }

    public function pengadaan_bahan_baku()
    {
        return $this->hasMany(PengadaanBahanBaku::class, 'id_bahan_baku', 'id_bahan_baku');
    }

    public function histori_bahan_baku()
    {
        return $this->hasMany(HistoriBahanBaku::class, 'id_bahan_baku', 'id_bahan_baku');
    }
}