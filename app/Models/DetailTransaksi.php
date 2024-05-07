<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailTransaksi extends Model
{
    protected $table = 'detail_transaksi';
    protected $primaryKey = 'id_detail_transaksi';
    public $timestamps = false;

    protected $fillable = [
        'id_detail_transaksi',
        'no_nota',
        'id_produk',
        'id_hampers',
        'harga_saat_beli',
        'jumlah',
    ];

    public function transaksi()
    {
        return $this->belongsTo(Transaksi::class, 'no_nota', 'no_nota');
    }

    public function hampers()
    {
        return $this->belongsTo(Hampers::class, 'id_hampers', 'id_hampers')->withTrashed();
    }

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'id_produk', 'id_produk')->withTrashed();
    }
}