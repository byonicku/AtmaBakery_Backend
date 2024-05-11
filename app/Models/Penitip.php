<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Penitip extends Model
{
    use SoftDeletes;
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

    public function produk()
    {
        return $this->hasMany(Produk::class, 'id_penitip', 'id_penitip');
    }
}
