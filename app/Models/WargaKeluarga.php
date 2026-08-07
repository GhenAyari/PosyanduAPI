<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WargaKeluarga extends Model
{
    use HasFactory;

    protected $table = 'warga_keluarga'; // Pastikan nama tabelnya akurat
    protected $fillable = [
        'posyandu_id',
        'user_id',
        'nama_kepala_keluarga',
        'no_kk',
        'nik_kepala_keluarga',
        'no_hp'
    ];

    // Relasi ke tabel anak (Satu keluarga bisa banyak anak)
    public function anak()
    {
        return $this->hasMany(WargaAnak::class, 'keluarga_id');
    }

    // Relasi ke tabel user (Satu keluarga punya 1 akun login)
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
