<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pengeluaran extends Model
{
    use SoftDeletes;
    protected $table = 'pengeluaran';
    protected $primaryKey = 'id_pengeluaran';
    public $timestamps = false;

    protected $fillable = [
        'id_pengeluaran',
        'nama',
        'total',
        'tanggal_pengeluaran',
    ];
}