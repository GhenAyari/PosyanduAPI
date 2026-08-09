<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PengaduanMasyarakat extends Model
{
    use HasFactory;

    protected $table = 'pengaduan_masyarakat';

    protected $fillable = [
        'posyandu_id',
        'bidang',
        'nama_pelapor',
        'jenis_kelamin',
        'nik',
        'no_hp',
        'alamat',
        'isi_keluhan',
        'lokasi_masalah',
        'lampiran',
        'status'
    ];

    protected $casts = [
        'lampiran' => 'array',
    ];
}
