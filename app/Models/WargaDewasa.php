<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WargaDewasa extends Model
{
    use HasFactory;

    protected $table = 'warga_dewasa';
    protected $fillable = ['keluarga_id', 'nama_lengkap', 'tanggal_lahir', 'jenis_kelamin'];
}
