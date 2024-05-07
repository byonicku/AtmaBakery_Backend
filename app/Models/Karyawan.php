<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Karyawan extends Model
{
    use SoftDeletes;
    protected $table = 'karyawan';
    protected $primaryKey = 'id_karyawan';
    public $timestamps = false;

    protected $fillable = [
        'nama',
        'no_telp',
        'email',
        'hire_date',
        'gaji',
        'bonus'
    ];

    public function presensi()
    {
        return $this->hasMany(Presensi::class, 'id_karyawan', 'id_karyawan');
    }
}
