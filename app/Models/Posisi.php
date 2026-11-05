<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Posisi extends Model
{
    protected $table = 'posisi';
    
    // No timestamps needed since it only holds name
    public $timestamps = false;

    protected $fillable = [
        'nama'
    ];
}
