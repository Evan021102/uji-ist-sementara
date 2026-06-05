<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AksesPin extends Model
{
    protected $table = 'akses_pin';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = [];
}
