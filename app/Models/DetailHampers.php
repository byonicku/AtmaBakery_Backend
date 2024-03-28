<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailHampers extends Model
{
    protected $table = 'detail_hampers';
    protected $primaryKey = 'id_detail_hampers';

    protected $fillable = [
        'id_detail_hampers',
        'id_hampers',
        'id_produk',
        'jumlah',
        'id_bahan_baku'
    ];

    public function hampers()
    {
        return $this->belongsTo(Hampers::class, 'id_hampers', 'id_hampers');
    }

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'id_produk', 'id_produk');
    }

    public function bahan_baku()
    {
        return $this->belongsTo(BahanBaku::class, 'id_bahan_baku', 'id_bahan_baku');
    }
}
