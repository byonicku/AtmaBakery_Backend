<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{

    protected $table = 'role';
    protected $primaryKey = 'id_role';
    protected $keyType = 'string';
    protected $timestamps = false;
    protected $fillable = [
        'id_role',
        'nama_role',
    ];

    public function user()
    {
        return $this->hasMany(User::class, 'id_role', 'id_role');
    }
}
