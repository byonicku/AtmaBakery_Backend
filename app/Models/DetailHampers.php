<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailHampers extends Model
{
    protected $table = 'detail_hampers';
    protected $primaryKey = 'id_detail_hampers';
    public $timestamps = false;

    protected $fillable = [
        'id_hampers',
        'id_produk',
        'jumlah',
        'id_bahan_baku'
    ];

    public function hampers()
    {
        return $this->belongsTo(Hampers::class, 'id_hampers', 'id_hampers')->withTrashed();
    }

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'id_produk', 'id_produk')->withTrashed();
    }

    public function bahan_baku()
    {
        return $this->belongsTo(BahanBaku::class, 'id_bahan_baku', 'id_bahan_baku')->withTrashed();
    }
}