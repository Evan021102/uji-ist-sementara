<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PesertaUji extends Model
{
    protected $table = 'peserta_uji';
    protected $primaryKey = 'id_peserta';
    public $timestamps = false;

    protected $fillable = [
        'nama',
        'posisi',
        'total_pelanggaran',
        'waktu_mulai'
    ];

    public function jawabanSesi2()
    {
        return $this->hasOne(JawabanSesi2::class, 'id_peserta', 'id_peserta');
    }

    public function jawabanSesi3()
    {
        return $this->hasOne(JawabanSesi3::class, 'id_peserta', 'id_peserta');
    }

    public function jawabanSesi4()
    {
        return $this->hasOne(JawabanSesi4::class, 'id_peserta', 'id_peserta');
    }

    public function jawabanSesi5()
    {
        return $this->hasOne(JawabanSesi5::class, 'id_peserta', 'id_peserta');
    }

    public function jawabanSesi6()
    {
        return $this->hasOne(JawabanSesi6::class, 'id_peserta', 'id_peserta');
    }
}
