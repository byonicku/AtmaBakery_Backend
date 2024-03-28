<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Alamat extends Model
{
    use HasFactory;

    protected $table = 'alamat';
    protected $primaryKey = 'id_alamat';

    protected $fillable = [
        'id_alamat',
        'id_user',
        'nama_lengkap',
        'lokasi',
        'keterangan'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }
}
