<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Posyandu extends Model
{
    use HasFactory;

    protected $table = 'posyandus';

    protected $fillable = [
        'nama',
        'alamat',
        'no_telepon',
        'latitude',
        'longitude'
    ];

    public function wargaKeluarga()
    {
        return $this->hasMany(WargaKeluarga::class, 'posyandu_id');
    }
}
