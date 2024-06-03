<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama', 'no_telp', 'address','provinsi_id','kota_id','kode_pos','rt','rw'
    ];
}
