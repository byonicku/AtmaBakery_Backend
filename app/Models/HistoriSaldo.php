<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HistoriSaldo extends Model
{
    protected $table = 'histori_saldo';
    protected $primaryKey = 'id_histori_saldo';

    protected $fillable = [
        'tanggal',
        'id_user',
        'saldo',
        'nama_bank',
        'no_rek',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }
}
