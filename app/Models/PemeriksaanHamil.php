<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PemeriksaanHamil extends Model
{
    use HasFactory;

    protected $table = 'pemeriksaan_hamil';

    protected $fillable = [
        'ibu_id', 'kader_id', 'tanggal_periksa', 'usia_kehamilan_minggu',
        'berat_badan', 'tinggi_badan', 'tekanan_darah', 'lingkar_perut',
        'lingkar_lengan', 'status_kek', 'anemia', 'status_imt',
        'dokumentasi_foto', 'status_form'
    ];

    protected $casts = [
        'dokumentasi_foto' => 'array',
        'tanggal_periksa' => 'date'
    ];
}
