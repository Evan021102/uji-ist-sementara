<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JawabanSesi6 extends Model
{
    protected $table = 'jawaban_sesi6';
    protected $primaryKey = 'id_sesi6';
    public $timestamps = false;
    protected $guarded = [];

    public function peserta()
    {
        return $this->belongsTo(PesertaUji::class, 'id_peserta', 'id_peserta');
    }
}
