<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PemeriksaanRemaja extends Model
{
    use HasFactory;

    protected $table = 'pemeriksaan_remaja';

    protected $fillable = [
        'remaja_id', 'kader_id', 'tanggal_periksa', 'umur_tahun',
        'berat_badan', 'tinggi_badan', 'tekanan_darah', 'status_imt',
        'dokumentasi_foto', 'status_form'
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
    public function remaja() {
        return $this->belongsTo(WargaRemaja::class, 'remaja_id');
    }
}
