<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WargaRemaja extends Model
{
    use HasFactory;

    protected $table = 'warga_remaja';
    protected $fillable = ['keluarga_id', 'nama_remaja', 'tanggal_lahir', 'jenis_kelamin'];
}
