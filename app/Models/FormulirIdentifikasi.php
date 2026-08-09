<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormulirIdentifikasi extends Model
{
    use HasFactory;

    protected $table = 'formulir_identifikasi';

    protected $fillable = [
        'posyandu_id',
        'kader_id',
        'bidang',
        'sub_bidang',
        'data_formulir',
        'dokumentasi_foto'
    ];

    // Otomatis convert JSON ke Array saat ditarik dari database
    protected $casts = [
        'data_formulir'    => 'array',
        'dokumentasi_foto' => 'array',
    ];
}
