<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JawabanSesi2 extends Model
{
    protected $table = 'jawaban_sesi2';
    protected $primaryKey = 'id_sesi2';
    public $timestamps = false;
    protected $guarded = [];

    public function peserta()
    {
        return $this->belongsTo(PesertaUji::class, 'id_peserta', 'id_peserta');
    }
}
