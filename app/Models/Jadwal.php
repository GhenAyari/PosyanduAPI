<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jadwal extends Model
{
    use HasFactory;

    protected $table = 'jadwal'; // Sesuai dengan nama tabel migration-mu
    protected $fillable = ['posyandu_id', 'keterangan_waktu', 'catatan'];

    public function posyandu()
    {
        return $this->belongsTo(Posyandu::class, 'posyandu_id');
    }
}
