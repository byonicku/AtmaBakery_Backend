<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BahanBaku extends Model
{
    protected $table = 'bahan_baku';
    protected $primaryKey = 'id_bahan_baku';
    protected $timestamps = false;

    protected $fillable = [
        'nama_bahan_baku',
        'stok',
        'satuan',
    ];

    public function resep()
    {
        return $this->hasMany(Resep::class, 'id_bahan_baku', 'id_bahan_baku');
    }
}
