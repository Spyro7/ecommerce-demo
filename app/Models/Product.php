<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $guarded = false;

    protected $table = 'products';

    protected $casts = [
        'images' => 'array',
    ];
    public function category(){
        return $this->belongsTo(Category::class);
    }

    public function brand(){
        return $this->belongsTo(Brand::class);
    }

    public function orderItems(){
        return $this->hasMany(OrderItem::class);
    }
}
