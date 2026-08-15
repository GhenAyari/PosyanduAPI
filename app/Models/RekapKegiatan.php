<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RekapKegiatan extends Model
{
    use HasFactory;

    protected $guarded = []; // Mengizinkan semua kolom untuk diisi

    public function posyandu()
    {
        return $this->belongsTo(Posyandu::class);
    }
}
