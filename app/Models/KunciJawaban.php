<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KunciJawaban extends Model
{
    protected $table = 'kunci_jawaban';
    protected $primaryKey = 'id_kunci';
    public $timestamps = false;
    protected $guarded = [];
}
