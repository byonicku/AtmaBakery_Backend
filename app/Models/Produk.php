<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Produk extends Model
{
    use SoftDeletes;
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

    public function detailHampers()
    {
        return $this->hasMany(DetailHampers::class, 'id_produk', 'id_produk');
    }

    public function resep()
    {
        return $this->hasMany(Resep::class, 'id_produk', 'id_produk');
    }

    public function gambar()
    {
        return $this->hasMany(Gambar::class, 'id_produk', 'id_produk');
    }
}