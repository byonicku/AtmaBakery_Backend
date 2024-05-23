<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Hampers extends Model
{
    use SoftDeletes;
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

    public function cart()
    {
        return $this->hasMany(Cart::class, 'id_hampers', 'id_hampers');
    }

    public function transaksi()
    {
        return $this->hasMany(DetailTransaksi::class, 'id_hampers', 'id_hampers');
    }
}
