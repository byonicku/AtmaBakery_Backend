<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{
    protected $table = 'transaksi';
    protected $primaryKey = 'no_nota';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;
    protected $fillable = [
        'no_nota',
        'id_user',
        'id_alamat',
        'tanggal_pesan',
        'tanggal_lunas',
        'tanggal_ambil',
        'penggunaan_poin',
        'penambahaan_poin',
        'poin_setelah_penambahan',
        'total',
        'radius',
        'ongkir',
        'tip',
        'tipe_delivery',
        'bukti_pembayaran',
        'public_id',
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

    public function detail_transaksi()
    {
        return $this->hasMany(DetailTransaksi::class, 'no_nota', 'no_nota');
    }
}