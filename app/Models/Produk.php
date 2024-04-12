<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Produk extends Model
{
    protected $table = 'produk';
    protected $primaryKey = 'id_produk';
    public $timestamps = false;
    protected $fillable = [
        'id_kategori',
        'nama_produk',
        'deskripsi',
        'ukuran',
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

    public function gambar()
    {
        return $this->hasMany(Gambar::class, 'id_produk', 'id_produk');
    }
}
