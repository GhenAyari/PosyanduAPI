<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Artikel extends Model
{
    use HasFactory;

    // 1. Beritahu Laravel nama tabel aslinya
    protected $table = 'artikel';

    // 2. Sesuaikan dengan kolom di migrasimu
    protected $fillable = [
        'posyandu_id',
        'penulis_id',
        'kategori',
        'judul',
        'slug',
        'isi_artikel',
        'path_foto',
        'status',
        'published_at',
    ];

    /**
     * Relasi ke penulis (User)
     */
    public function penulis()
    {
        return $this->belongsTo(User::class, 'penulis_id'); // Hubungkan dengan penulis_id
    }
}
