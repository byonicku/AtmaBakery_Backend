<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Alamat extends Model
{
    protected $table = 'alamat';
    protected $primaryKey = 'id_alamat';
    public $timestamps = false;
    protected $fillable = [
        'id_user',
        'nama_lengkap',
        'no_telp',
        'lokasi',
        'keterangan'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }
}
