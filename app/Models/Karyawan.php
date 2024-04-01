<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Karyawan extends Model
{
    protected $table = 'karyawan';
    protected $primaryKey = 'id_karyawan';
    protected $timestamps = false;

    protected $fillable = [
        'nama',
        'no_telp',
        'email',
        'hire_date',
        'gaji',
    ];
}
