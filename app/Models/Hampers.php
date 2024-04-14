<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Hampers extends Model
{
    protected $table = 'hampers';
    protected $primaryKey = 'id_hampers';
    public $timestamps = false;

    protected $fillable = [
        'id_hampers',
        'nama_hampers',
        'harga',
    ];

    public function detail_hampers()
    {
        return $this->hasMany(DetailHampers::class, 'id_hampers', 'id_hampers');
    }

    public function gambar()
    {
        return $this->hasMany(Gambar::class, 'id_hampers', 'id_hampers');
    }
}
