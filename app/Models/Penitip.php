<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Penitip extends Model
{
    protected $table = 'penitip';
    protected $primaryKey = 'id_penitip';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_penitip',
        'nama',
        'no_telp',
        'komisi'
    ];

    protected function casts(): array
    {
        return [
            'id_penitip' => 'string',
        ];
    }
}
