<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Penitip extends Model
{
    protected $table = 'penitip';
    protected $primaryKey = 'id_penitip';
    protected $keyType = 'string';

    protected $fillable = [
        'id_penitip',
        'nama',
        'no_telp',
        'komisi'
    ];
}
