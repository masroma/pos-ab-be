<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class General extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_company',
        'logo',
        'alamat',
        'whatsapp',
        'instagram',
        'facebook',
        'tiktok',
        'youtube',
    ];

    protected function logo(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => url('/storage/' . $value),
        );
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id');
    }
}
