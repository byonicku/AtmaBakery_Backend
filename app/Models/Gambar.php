<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Gambar extends Model
{
    protected $table = 'gambar';
    protected $primaryKey = 'id_gambar';
    public $timestamps = false;
    protected $fillable = [
        'id_produk',
        'id_hampers',
        'url',
        'public_id',
    ];

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'id_produk', 'id_produk');
    }

    public function hampers()
    {
        return $this->belongsTo(Hampers::class, 'id_hampers', 'id_hampers');
    }
}
