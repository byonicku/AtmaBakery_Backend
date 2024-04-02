<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HistoriBahanBaku extends Model
{
    protected $table = 'histori_bahanbaku';
    protected $primaryKey = 'id_histori_bahanbaku';
    public $timestamps = false;

    protected $fillable = [
        'id_histori_bahanbaku',
        'id_bahan_baku',
        'jumlah',
        'tanggal',
        'tanggal_pakai',
    ];

    public function bahan_baku()
    {
        return $this->belongsTo(BahanBaku::class, 'id_bahan_baku', 'id_bahan_baku');
    }
}
