<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pengeluaran extends Model
{
    protected $table = 'pengeluaran';
    protected $primaryKey = 'id_pengeluaran';
    protected $timestamps = false;

    protected $fillable = [
        'id_pengeluaran',
        'nama',
        'satuan',
        'total',
        'tanggal_pengeuaran',
    ];
}
