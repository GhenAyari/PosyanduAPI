<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PemeriksaanLansia extends Model
{
    use HasFactory;

    protected $table = 'pemeriksaan_lansia';

    protected $fillable = [
        'lansia_id', 'kader_id', 'tanggal_periksa', 'berat_badan',
        'tinggi_badan', 'lingkar_pinggang', 'tekanan_darah', 'tensi',
        'gula_darah', 'nadi', 'status_imt', 'dokumentasi_foto', 'status_form'
    ];

    protected $casts = [
        'dokumentasi_foto' => 'array',
        'tanggal_periksa' => 'date'
    ];

    public function kader()
    {
        // Mengarahkan 'kader_id' di tabel pemeriksaan ke tabel 'users'
        return $this->belongsTo(User::class, 'kader_id');
    }
}
