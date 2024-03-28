<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{
    protected $table = 'transaksi';
    protected $primaryKey = 'no_nota';
    protected $keyType = 'string';

    protected $fillable = [
        'no_nota',
        'id_user',
        'id_alamat',
        'tanggal_pesan',
        'tanggal_lunas',
        'tanggal_ambil',
        'penggunaan_poin',
        'total',
        'radius',
        'tip',
        'tipe_delivery',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    public function alamat()
    {
        return $this->belongsTo(Alamat::class, 'id_alamat', 'id_alamat');
    }
}
