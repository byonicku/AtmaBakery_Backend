<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Produk extends Model
{
    protected $table = 'produk';
    protected $primaryKey = 'id_produk';
    protected $timestamps = false;
    protected $fillable = [
        'id_kategori',
        'nama_produk',
        'ukuaran',
        'harga',
        'limit',
        'id_penitip',
        'stok',
        'status'
    ];

    public function kategori()
    {
        return $this->belongsTo(Kategori::class, 'id_kategori', 'id_kategori');
    }
}
