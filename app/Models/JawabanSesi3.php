<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JawabanSesi3 extends Model
{
    protected $table = 'jawaban_sesi3';
    protected $primaryKey = 'id_sesi2'; // Primary key is named id_sesi2 in all jawaban tables in this DB!
    public $timestamps = false;
    protected $guarded = [];

    public function peserta()
    {
        return $this->belongsTo(PesertaUji::class, 'id_peserta', 'id_peserta');
    }
}
