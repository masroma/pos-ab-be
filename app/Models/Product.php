<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Product extends Model
{
    use HasFactory;
    protected $fillable = [
        'image', 'barcode', 'title','slug', 'description', 'buy_price', 'sell_price','discount_price', 'category_id', 'stock'
    ];
    public function category()
    {
         return $this->belongsTo(Category::class);
    }

    protected function image(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => url('/storage/products/' . $value),
        );
    }
}
