<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WargaAnak extends Model
{
    use HasFactory;

    protected $table = 'warga_anak';
    protected $fillable = [
        'keluarga_id',
        'nama_anak',
        'tanggal_lahir',
        'jenis_kelamin'
    ];

    public function keluarga()
    {
        return $this->belongsTo(WargaKeluarga::class, 'keluarga_id');
    }
}
