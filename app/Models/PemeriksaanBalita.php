<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PemeriksaanBalita extends Model
{
    use HasFactory;

    protected $table = 'pemeriksaan_balita';

    protected $fillable = [
        'anak_id', 'kader_id', 'tanggal_periksa', 'umur_bulan',
        'berat_badan', 'tinggi_badan', 'lingkar_kepala', 'lingkar_lengan',
        'catatan_perkembangan', 'status_gizi', 'imunisasi', 'dokumentasi_foto', 'status_form'
    ];

    // Karena menggunakan tipe JSON di database, kita ubah jadi Array saat ditarik ke Laravel
    protected $casts = [
        'imunisasi' => 'array',
        'dokumentasi_foto' => 'array',
        'tanggal_periksa' => 'date'
    ];

    public function anak()
    {
        return $this->belongsTo(WargaAnak::class, 'anak_id');
    }

    public function kader()
    {
        return $this->belongsTo(User::class, 'kader_id');
    }
}
